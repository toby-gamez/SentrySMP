<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DiscordService
{
    private string $botToken;
    private string $channelId;
    private string $guildId;

    public function __construct()
    {
        $this->botToken  = config('services.discord.bot_token', '');
        $this->channelId = config('services.discord.channel_id', '');
        $this->guildId   = config('services.discord.guild_id', '1159130895190605854');
    }

    public function getMemberCount(): ?int
    {
        return Cache::remember('discord_member_count', 300, function () {
            if (empty($this->botToken)) return null;

            try {
                $response = Http::withHeaders([
                    'Authorization' => "Bot {$this->botToken}",
                    'User-Agent'    => 'SentrySMP-Website/1.0',
                ])->get("https://discord.com/api/v10/guilds/{$this->guildId}?with_counts=true");

                if (!$response->successful()) return null;

                return $response->json('approximate_member_count');
            } catch (\Throwable $e) {
                Log::warning('Discord member count fetch failed', ['error' => $e->getMessage()]);
                return null;
            }
        });
    }

    public function getAnnouncements(int $limit = 20): array
    {
        // Always fetch the max from Discord and cache the full filtered list.
        // Slicing after filtering ensures callers always get $limit results even
        // when some messages are skipped (e.g. single-line filter).
        $all = Cache::remember('discord_announcements', 300, function () {
            if (empty($this->botToken) || empty($this->channelId)) return [];

            try {
                $response = Http::withHeaders([
                    'Authorization' => "Bot {$this->botToken}",
                    'User-Agent'    => 'SentrySMP-Website/1.0',
                ])->get("https://discord.com/api/v10/channels/{$this->channelId}/messages?limit=100");

                if (!$response->successful()) {
                    Log::warning('Discord announcements fetch failed', ['status' => $response->status()]);
                    return [];
                }

                $messages = $response->json();
                if (!is_array($messages)) return [];

                $results = [];
                foreach ($messages as $msg) {
                    // Skip bot messages
                    if (!empty($msg['author']['bot'])) continue;

                    $author  = $msg['author']['username'] ?? 'Unknown';
                    $content = $msg['content'] ?? '';

                    // Fallback to first embed
                    if (empty($content) && !empty($msg['embeds'][0])) {
                        $embed   = $msg['embeds'][0];
                        $content = trim(($embed['title'] ?? '') . "\n" . ($embed['description'] ?? ''));
                    }

                    // Fallback to attachment filenames
                    if (empty($content) && !empty($msg['attachments'])) {
                        $names   = array_column($msg['attachments'], 'filename');
                        $content = '📎 ' . implode(', ', $names);
                    }

                    if (empty($content)) continue;

                    // Skip single-line messages
                    $nonEmptyLines = array_filter(explode("\n", $content), fn($l) => trim($l) !== '');
                    if (count($nonEmptyLines) < 2) continue;

                    $avatarUrl = null;
                    $authorId  = $msg['author']['id'] ?? null;
                    $avatarHash = $msg['author']['avatar'] ?? null;
                    if ($authorId && $avatarHash) {
                        $avatarUrl = "https://cdn.discordapp.com/avatars/{$authorId}/{$avatarHash}.png?size=64";
                    }

                    $title   = $this->extractTitle($content, $content);
                    $content = $this->convertDiscordMarkdown($content);

                    $createdAt = null;
                    if (!empty($msg['timestamp'])) {
                        try { $createdAt = new \DateTime($msg['timestamp']); } catch (\Throwable) {}
                    }

                    $results[] = [
                        'title'      => $title,
                        'author'     => $author,
                        'avatar'     => $avatarUrl,
                        'content'    => $content,
                        'created_at' => $createdAt,
                    ];
                }

                return $results;
            } catch (\Throwable $e) {
                Log::error('Discord announcements fetch error', ['error' => $e->getMessage()]);
                return [];
            }
        });

        return $limit >= 100 ? $all : array_slice($all, 0, $limit);
    }

    private function extractTitle(string $content, string &$body): string
    {
        $lines = explode("\n", $content);

        if (count($lines) > 1) {
            $firstLine = trim($lines[0]);
            $isTitle = strlen($firstLine) > 3
                && strlen($firstLine) < 120
                && (
                    str_contains($firstLine, '**')
                    || str_contains($firstLine, '__')
                    || str_starts_with($firstLine, '#')
                    || preg_match('/^[A-Z][^.!?]*$/', $firstLine)
                );

            if ($isTitle) {
                $title = preg_replace('/^#+\s*/', '', $firstLine);
                $title = preg_replace('/(\*\*|__|~~|`)/', '', $title);
                $body  = trim(implode("\n", array_slice($lines, 1)));
                return trim($title);
            }
        }

        // Use first 6 words as title
        $words = preg_split('/\s+/', $content, -1, PREG_SPLIT_NO_EMPTY);
        $count = count($words);
        return implode(' ', array_slice($words, 0, min(6, $count))) . ($count > 6 ? '...' : '');
    }

    private function convertDiscordMarkdown(string $text): string
    {
        if (empty($text)) return '';

        $text = str_replace(['@everyone', '@here'], '', $text);
        $text = str_replace("\r\n", "\n", $text);

        // Resolve Discord <...> tokens before HTML-escaping (they use literal < >)
        $text = preg_replace('/<@!?\d+>/', '@user', $text);
        $text = preg_replace('/<@&\d+>/', '@role', $text);
        $text = preg_replace('/<#\d+>/', '#channel', $text);
        $text = preg_replace('/<a?:(\w+):\d+>/', ':$1:', $text);
        $text = preg_replace_callback('/<t:(\d+)(?::[tTdDfFR])?>/', function ($m) {
            $dt = \DateTime::createFromFormat('U', $m[1]);
            return $dt ? $dt->format('Y-m-d H:i:s') : $m[0];
        }, $text);

        // Extract code blocks and inline code before escaping so their content is preserved verbatim
        $codeBlocks = [];
        $text = preg_replace_callback('/```(?:\w*\n)?(.*?)```/s', function ($m) use (&$codeBlocks) {
            $ph = "\x00CODE" . count($codeBlocks) . "\x00";
            $codeBlocks[] = '<pre class="md-pre"><code>' . htmlspecialchars(trim($m[1]), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</code></pre>';
            return $ph;
        }, $text);
        $inlineCodes = [];
        $text = preg_replace_callback('/`([^`\n]+)`/', function ($m) use (&$inlineCodes) {
            $ph = "\x00INLINE" . count($inlineCodes) . "\x00";
            $inlineCodes[] = '<code class="md-code">' . htmlspecialchars($m[1], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</code>';
            return $ph;
        }, $text);

        // HTML-escape the plain text before injecting markup
        $text = htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        // Spoilers
        $text = preg_replace('/\|\|(.*?)\|\|/s', '<span class="spoiler">$1</span>', $text);

        // Bold italic → bold → italic (order matters)
        $text = preg_replace('/\*\*\*((?!\*).+?)\*\*\*/s', '<strong><em>$1</em></strong>', $text);
        $text = preg_replace('/\*\*((?!\*).+?)\*\*/s', '<strong>$1</strong>', $text);
        $text = preg_replace('/\*((?!\s).+?(?<!\s))\*/s', '<em>$1</em>', $text);

        // Strikethrough
        $text = preg_replace('/~~(.+?)~~/s', '<s>$1</s>', $text);

        // Underline
        $text = preg_replace('/__(.*?)__/s', '<u>$1</u>', $text);

        // Headings (line-level; # not escaped by htmlspecialchars)
        $text = preg_replace('/^### (.+)$/m', '<h5 class="md-h">$1</h5>', $text);
        $text = preg_replace('/^## (.+)$/m',  '<h4 class="md-h">$1</h4>', $text);
        $text = preg_replace('/^# (.+)$/m',   '<h3 class="md-h">$1</h3>', $text);

        // Blockquotes (> becomes &gt; after htmlspecialchars)
        $text = preg_replace('/^&gt; (.*)$/m', '<blockquote class="md-quote">$1</blockquote>', $text);

        // Restore code placeholders
        foreach ($codeBlocks as $i => $block) {
            $text = str_replace("\x00CODE{$i}\x00", $block, $text);
        }
        foreach ($inlineCodes as $i => $code) {
            $text = str_replace("\x00INLINE{$i}\x00", $code, $text);
        }

        // Collapse blank lines (3+ newlines → 1 newline, 2 newlines → 1 newline)
        $text = preg_replace('/\n{2,}/', "\n", $text);

        return trim($text);
    }
}
