{{--
    Image Library
    Variables: $images (array of image objects from the SentrySMP.Images microservice,
               each with 'url', 'fileName', 'size' keys)
    Proxies upload and delete to the Images microservice via ImageController.
    The page also responds to XHR requests from the image picker embedded in
    product-form and category-form partials (?subDirectory=<slug>).
--}}
@extends('layouts.admin')
@section('title', 'Images')
@section('content')
{{-- ── Page Guide ─────────────────────────────────────────────────────── --}}
<div style="background:#0b1929;border:1px solid #1e3a5f;border-left:3px solid #3b82f6;border-radius:8px;padding:14px 18px;margin-bottom:20px;font-size:13px;">
    <div onclick="var n=this.nextElementSibling;n.hidden=!n.hidden;this.querySelector('.pg-ch').style.transform=n.hidden?'':'rotate(180deg)'"
         style="display:flex;align-items:center;gap:8px;cursor:pointer;user-select:none;">
        <i class="bi bi-book" style="color:#60a5fa;font-size:14px;"></i>
        <strong style="color:#93c5fd;">Image Library Guide</strong>
        <i class="bi bi-chevron-down pg-ch" style="color:#60a5fa;margin-left:auto;font-size:12px;transition:transform .2s;"></i>
    </div>
    <div hidden style="margin-top:12px;color:#94a3b8;line-height:1.75;">
        <ul style="margin:0;padding-left:18px;">
            <li><strong style="color:#c4d4e8;">Upload</strong> — choose an image file and click Upload. Accepted formats: JPEG, PNG, WebP, GIF. The file is stored in the SentrySMP.Images microservice.</li>
            <li><strong style="color:#c4d4e8;">Copy URL</strong> — copies the image's full URL to your clipboard. Paste it into a product or category image field.</li>
            <li><strong style="color:#c4d4e8;">Using images in forms</strong> — the <em>Browse existing</em> button inside any product or category form opens this library directly without leaving the page. Images are filtered to the current category's subfolder.</li>
            <li><strong style="color:#c4d4e8;">Deleting</strong> an image removes it permanently from the microservice. Update any products or categories that were using it before deleting.</li>
            <li>If the library shows empty or fails to load, check that the <strong style="color:#c4d4e8;">SentrySMP.Images</strong> microservice is running and that <code style="color:#888;">IMAGES_BASE_URL</code> is set correctly in <code style="color:#888;">.env</code>.</li>
        </ul>
    </div>
</div>

<div class="admin-card" style="margin-bottom:20px;">
    <div class="admin-card-header">
        <h2 class="admin-card-title"><i class="bi bi-upload"></i> Upload Image</h2>
    </div>
    <form method="POST" action="{{ route('admin.images.upload') }}" enctype="multipart/form-data" style="display:flex;gap:10px;align-items:flex-end;flex-wrap:wrap;padding:8px 0;">
        @csrf
        <div style="flex:1;min-width:200px;">
            <input type="file" name="image" accept="image/*" required style="background:#1a1a1a;border:1px solid #333;color:#ccc;padding:8px 12px;border-radius:8px;width:100%;box-sizing:border-box;">
        </div>
        <button type="submit" class="btn-admin btn-admin-primary"><i class="bi bi-cloud-upload"></i> Upload</button>
    </form>
    @error('image')
        <p style="color:#ff6b7a;margin-top:8px;">{{ $message }}</p>
    @enderror
</div>

<div class="admin-card">
    <div class="admin-card-header">
        <h2 class="admin-card-title"><i class="bi bi-images"></i> Image Library ({{ count($images) }})</h2>
        <a href="{{ route('admin.images.index') }}" class="btn-admin btn-admin-secondary"><i class="bi bi-arrow-clockwise"></i> Refresh</a>
    </div>

    @if(empty($images))
        <p style="color:#666;padding:12px 0;">No images found, or the image service is unreachable.</p>
    @else
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:14px;margin-top:12px;">
        @foreach($images as $image)
        @php $img = is_array($image) ? $image : (array)$image; @endphp
        <div style="background:#1a1a1a;border:1px solid #2a2a2a;border-radius:8px;overflow:hidden;display:flex;flex-direction:column;">
            <img src="{{ $img['url'] }}" alt="{{ $img['fileName'] }}"
                 style="width:100%;height:120px;object-fit:cover;display:block;background:#111;">
            <div style="padding:8px;flex:1;">
                <div style="font-size:11px;color:#888;word-break:break-all;margin-bottom:6px;" title="{{ $img['url'] }}">
                    {{ $img['fileName'] }}
                    <span style="color:#555;"> · {{ round(($img['size'] ?? 0) / 1024) }}KB</span>
                </div>
            </div>
            <div style="padding:0 8px 8px;display:flex;gap:6px;">
                <button onclick="copyUrl('{{ $img['url'] }}')" class="btn-admin btn-admin-secondary" style="font-size:11px;padding:4px 8px;flex:1;" title="Copy URL">
                    <i class="bi bi-clipboard"></i> Copy URL
                </button>
                <form method="POST" action="{{ route('admin.images.destroy', ['filename' => $img['fileName']]) }}" onsubmit="return confirm('Delete this image?')" style="flex-shrink:0;">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn-admin btn-admin-danger" style="font-size:11px;padding:4px 8px;">
                        <i class="bi bi-trash"></i>
                    </button>
                </form>
            </div>
        </div>
        @endforeach
    </div>
    @endif
</div>

<script>
function copyUrl(url) {
    navigator.clipboard.writeText(url).then(function() {
        var el = document.createElement('div');
        el.textContent = 'URL copied!';
        el.style.cssText = 'position:fixed;bottom:20px;right:20px;background:#23d05e;color:#000;padding:10px 18px;border-radius:8px;z-index:9999;font-weight:700;';
        document.body.appendChild(el);
        setTimeout(function(){ el.remove(); }, 2000);
    });
}
</script>
@endsection
