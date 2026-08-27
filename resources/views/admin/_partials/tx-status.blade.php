{{--
    Partial: Transaction / Command Status Badge
    Variables: $status (?string) — semicolon-separated list of status tokens
    Renders a coloured badge for each token in the status string.
    Known tokens: SUCCEEDED, FAILED, CANCELLED, RCON_FAILED, TRIED_AGAIN.
    Unknown tokens render with a default blue style.
--}}
@php
$parts = array_filter(array_map('trim', explode(';', $status ?? '')));
$styles = [
    'SUCCEEDED'   => 'background:#1a3320;color:#4ade80;border-color:#22c55e;',
    'FAILED'      => 'background:#3a1a1a;color:#f87171;border-color:#ef4444;',
    'CANCELLED'   => 'background:#2a2a2a;color:#9ca3af;border-color:#6b7280;',
    'RCON_FAILED' => 'background:#3a2a10;color:#fb923c;border-color:#f97316;',
    'TRIED_AGAIN' => 'background:#1a2a3a;color:#60a5fa;border-color:#3b82f6;',
];
$defaultStyle = 'background:#1e2f3a;color:#6ea8fe;border-color:#3b6ea6;';
@endphp
@foreach($parts as $part)
<span style="display:inline-block;font-size:11px;font-weight:700;padding:2px 8px;border-radius:4px;border:1px solid;margin:1px;white-space:nowrap;{{ $styles[$part] ?? $defaultStyle }}">{{ $part }}</span>
@endforeach
@if(empty($parts))
<span style="color:#555;">—</span>
@endif
