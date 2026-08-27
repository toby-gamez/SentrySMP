@extends('layouts.app')

@section('title', 'Home - SentrySMP')

@push('head')
<style>
.latest-announcements { max-width: 1200px; margin: 40px auto; padding: 0 20px; }
.latest-announcements h2 { text-align: center; margin-bottom: 30px; }
.announcements-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 20px; }

/* Card layout matching news page */
.announcements-grid .announcement {
    display: flex;
    flex-direction: column;
    padding: 0;
    overflow: hidden;
}
.ann-header {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 12px 16px;
    background-color: rgba(0,0,0,0.25);
    border-bottom: 1px solid rgba(255,255,255,0.06);
}
body.light .ann-header {
    background-color: rgba(0,0,0,0.08);
    border-bottom: 1px solid rgba(0,0,0,0.08);
}
.ann-avatar {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    flex-shrink: 0;
    background-color: #dc3545;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 13px;
    font-weight: bold;
    overflow: hidden;
}
.ann-avatar img { width: 100%; height: 100%; object-fit: cover; }
.ann-meta { flex: 1; min-width: 0; }
.ann-title {
    margin: 0;
    font-size: 0.95rem;
    font-weight: 700;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.ann-byline { font-size: 0.72rem; opacity: 0.55; margin-top: 1px; }
.ann-body {
    padding: 12px 16px;
    font-size: 0.88rem;
    line-height: 1.6;
    white-space: pre-wrap;
    word-break: break-word;
}
.ann-body .md-h { margin: 0.4em 0 0.2em; font-size: 1em; font-weight: 700; white-space: normal; }
.ann-body .md-quote { border-left: 3px solid #dc3545; margin: 0.2em 0; padding: 2px 8px; opacity: 0.75; white-space: normal; }
.ann-body .md-pre { background: rgba(0,0,0,0.35); border-radius: 6px; padding: 8px 12px; overflow-x: auto; margin: 0.3em 0; white-space: pre; font-size: 0.85em; }
.ann-body .md-code { background: rgba(0,0,0,0.3); border-radius: 3px; padding: 1px 4px; font-size: 0.85em; font-family: monospace; }
body.light .ann-body .md-pre { background: rgba(0,0,0,0.08); }
body.light .ann-body .md-code { background: rgba(0,0,0,0.1); }
.ann-body .spoiler { background: currentColor; border-radius: 3px; cursor: pointer; color: transparent; transition: color 0.2s; }
.ann-body .spoiler:hover { color: inherit; }
</style>
@endpush

@section('content')
<div class="grid-div">
    <div class="thing-grid">
        @foreach($categories as $category)
        <a href="{{ route('shop.category', $category) }}">
            <div class="thing-grid-item" style="background-color:{{ $category->color }};border-bottom:5px solid {{ $category->secondary_color }};">
                <div class="thing-grid-text">
                    <p class="thing-grid-title">{{ $category->name }}</p>
                    <p class="thing-grid-subtitle">Click to view ›</p>
                </div>
                @if($category->image)
                <div class="thing-grid-image">
                    <img class="thing-grid-ghost" src="{{ $category->image }}" width="90px" height="100px" style="border-radius:0" alt="">
                    <img src="{{ $category->image }}" width="90px" height="100px" style="border-radius:0" alt="{{ $category->name }}">
                </div>
                @endif
            </div>
        </a>
        @endforeach
    </div>
</div>

<div class="latest-announcements">
    <h2>Latest Announcements</h2>

    @if(count($announcements) > 0)
    <div class="announcements-grid">
        @foreach($announcements as $a)
        <div class="announcement">
            <div class="ann-header">
                <div class="ann-avatar">
                    @if(!empty($a['avatar']))
                        <img src="{{ $a['avatar'] }}" alt="{{ e($a['author']) }}" loading="lazy">
                    @else
                        {{ strtoupper(substr($a['author'], 0, 1)) }}
                    @endif
                </div>
                <div class="ann-meta">
                    <div class="ann-title">{{ $a['title'] }}</div>
                    <div class="ann-byline">
                        {{ $a['author'] }}
                        @if($a['created_at'])
                        &nbsp;·&nbsp;{{ $a['created_at']->format('d M Y') }}
                        @endif
                    </div>
                </div>
            </div>
            <div class="ann-body">{!! $a['content'] !!}</div>
        </div>
        @endforeach
    </div>
    @endif

    <div style="margin-left:auto;margin-right:auto;width:min-content;margin-top:20px;">
        <button onclick="window.location.href='{{ route('news') }}'" style="height:40px;width:100px">Show more</button>
    </div>
</div>
@endsection
