@extends('layouts.app')

@section('title', 'News — SentrySMP')

@push('head')
<style>
.news-list {
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
}

.announcement {
    display: flex;
    flex-direction: column;
    gap: 0;
    padding: 0;
    overflow: hidden;
}

.announcement-header {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 14px 20px;
    background-color: rgba(0,0,0,0.25);
    border-bottom: 1px solid rgba(255,255,255,0.06);
}

body.light .announcement-header {
    background-color: rgba(0,0,0,0.08);
    border-bottom: 1px solid rgba(0,0,0,0.08);
}

.announcement-avatar {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    flex-shrink: 0;
    background-color: #dc3545;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    font-weight: bold;
    overflow: hidden;
}

.announcement-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.announcement-meta {
    flex: 1;
    min-width: 0;
}

.announcement-title {
    margin: 0;
    font-size: 1rem;
    font-weight: 700;
    line-height: 1.3;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.announcement-byline {
    font-size: 0.75rem;
    opacity: 0.55;
    margin-top: 2px;
}

.announcement-body {
    padding: 16px 20px;
    font-size: 0.92rem;
    line-height: 1.65;
    white-space: pre-wrap;
    word-break: break-word;
}

/* Markdown elements inside announcement body */
.announcement-body .md-h {
    margin: 0.5em 0 0.25em;
    font-size: 1em;
    font-weight: 700;
    border-bottom: 1px solid rgba(220,53,69,0.4);
    padding-bottom: 2px;
    white-space: normal;
}
.announcement-body h3.md-h { font-size: 1.1em; }

.announcement-body .md-quote {
    border-left: 3px solid #dc3545;
    margin: 0.25em 0;
    padding: 2px 10px;
    opacity: 0.75;
    white-space: normal;
}

.announcement-body .md-pre {
    background: rgba(0,0,0,0.35);
    border-radius: 6px;
    padding: 10px 14px;
    overflow-x: auto;
    margin: 0.4em 0;
    white-space: pre;
    font-size: 0.88em;
}
body.light .announcement-body .md-pre {
    background: rgba(0,0,0,0.08);
}

.announcement-body .md-code {
    background: rgba(0,0,0,0.3);
    border-radius: 3px;
    padding: 1px 5px;
    font-size: 0.88em;
    font-family: monospace;
}
body.light .announcement-body .md-code {
    background: rgba(0,0,0,0.1);
}

.announcement-body .spoiler {
    background: currentColor;
    border-radius: 3px;
    cursor: pointer;
    color: transparent;
    transition: color 0.2s;
}
.announcement-body .spoiler:hover {
    color: inherit;
}

.news-empty {
    text-align: center;
    padding: 3rem 1rem;
    opacity: 0.5;
}
</style>
@endpush

@section('content')
<div class="main-wrapper">
    <p class="main">News &amp; Updates</p>
</div>

<div id="latestAnnouncements">
    @if(count($announcements) > 0)
    <div class="news-list">
        @foreach($announcements as $a)
        <div class="announcement">
            <div class="announcement-header">
                <div class="announcement-avatar">
                    @if(!empty($a['avatar']))
                        <img src="{{ $a['avatar'] }}" alt="{{ e($a['author']) }}" loading="lazy">
                    @else
                        {{ strtoupper(substr($a['author'], 0, 1)) }}
                    @endif
                </div>
                <div class="announcement-meta">
                    <div class="announcement-title">{{ $a['title'] }}</div>
                    <div class="announcement-byline">
                        {{ $a['author'] }}
                        @if($a['created_at'])
                        &nbsp;·&nbsp;{{ $a['created_at']->format('d M Y, H:i') }}
                        @endif
                    </div>
                </div>
            </div>
            <div class="announcement-body">{!! $a['content'] !!}</div>
        </div>
        @endforeach
    </div>
    @else
    <div class="news-empty">
        <p>No announcements available at the moment.</p>
    </div>
    @endif
</div>
@endsection
