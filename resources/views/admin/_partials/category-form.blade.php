{{--
    Partial: Category Form Fields
    Variables: $item (?Category — null when creating, model when editing),
               $imageSubDir (string, defaults to 'categories')
    Renders name, auto-generated slug, colour picker, and image picker.
    The slug input auto-fills from the name field unless the user edits it manually.
    The image picker fetches images from the Images microservice via XHR
    (admin.images.index?subDirectory=<imageSubDir>).
--}}
@php $imageSubDir ??= 'categories'; @endphp
<div class="form-group">
    <label>Name *</label>
    <input type="text" name="name" id="cat-name" value="{{ old('name', $item?->name) }}" required maxlength="100">
</div>

<div class="form-group">
    <label>Slug <span style="color:#888;">(auto-generated from name)</span></label>
    <input type="text" name="slug" id="cat-slug" value="{{ old('slug', $item?->slug) }}" maxlength="100" placeholder="e.g. battle-passes">
</div>

<script>
(function () {
    var nameInput = document.getElementById('cat-name');
    var slugInput = document.getElementById('cat-slug');
    var slugManual = slugInput.value.length > 0;

    function toSlug(str) {
        return str.toLowerCase().trim()
            .replace(/[^a-z0-9\s-]/g, '')
            .replace(/[\s_]+/g, '-')
            .replace(/-+/g, '-')
            .replace(/^-|-$/g, '');
    }

    slugInput.addEventListener('input', function () {
        slugManual = this.value.length > 0;
    });

    nameInput.addEventListener('input', function () {
        if (!slugManual) {
            slugInput.value = toSlug(this.value);
        }
    });
})();
</script>

<div class="form-group">
    <label>Colour *</label>
    <div style="display:flex;gap:10px;align-items:center;">
        <input type="color" name="color" value="{{ old('color', $item?->color ?? '#888888') }}" required
               style="width:60px;height:40px;padding:2px;border:1px solid #333;border-radius:6px;background:transparent;cursor:pointer;">
        <input type="text" id="color-hex" value="{{ old('color', $item?->color ?? '#888888') }}"
               maxlength="7" pattern="#[0-9a-fA-F]{6}" placeholder="#888888"
               style="width:100px;"
               oninput="document.querySelector('input[type=color]').value=this.value">
    </div>
    <script>
        document.querySelector('input[type=color]').addEventListener('input', function() {
            document.getElementById('color-hex').value = this.value;
        });
    </script>
</div>

{{-- Image Picker --}}
<div class="form-group">
    <label>Category Image</label>
    <input type="hidden" name="image" id="pf-image-url" value="{{ old('image', $item?->image) }}">

    <div id="pf-preview-wrap" style="margin-bottom:10px;display:{{ ($item?->image || old('image')) ? 'block' : 'none' }};">
        <img id="pf-preview" src="{{ old('image', $item?->image ?? '') }}"
             style="width:90px;height:90px;object-fit:cover;border-radius:8px;border:2px solid #333;display:block;margin-bottom:6px;">
        <span id="pf-preview-name" style="font-size:11px;color:#888;word-break:break-all;"></span>
    </div>

    <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:12px;">
        <button type="button" class="btn-admin btn-admin-secondary" onclick="pfOpenPicker()">
            <i class="bi bi-images"></i> Browse existing
        </button>
        <button type="button" class="btn-admin btn-admin-secondary" onclick="document.getElementById('pf-upload-input').click()">
            <i class="bi bi-upload"></i> Upload new
        </button>
        <input type="file" id="pf-upload-input" accept="image/*" style="display:none;" onchange="pfUploadFile(this)">
        <button type="button" class="btn-admin btn-admin-danger" onclick="pfClearImage()" id="pf-clear-btn"
                style="{{ ($item?->image || old('image')) ? '' : 'display:none;' }}">
            <i class="bi bi-x"></i> Clear
        </button>
    </div>

    <div id="pf-picker" style="display:none;border:1px solid #333;border-radius:10px;background:#111;padding:14px;max-height:360px;overflow-y:auto;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
            <strong style="color:#ccc;">Select an image</strong>
            <button type="button" class="btn-admin btn-admin-secondary" style="padding:4px 10px;font-size:12px;" onclick="pfClosePicker()">
                <i class="bi bi-x-lg"></i> Close
            </button>
        </div>
        <div id="pf-grid" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(90px,1fr));gap:8px;">
            <div style="color:#666;font-size:12px;grid-column:1/-1;">Loading images…</div>
        </div>
    </div>
    <div id="pf-upload-status" style="display:none;color:#888;font-size:12px;margin-top:6px;">Uploading…</div>
</div>

<script>
(function () {
    var _imagesLoaded = false;

    function pfSetImage(url, name) {
        document.getElementById('pf-image-url').value = url;
        var preview = document.getElementById('pf-preview');
        var wrap    = document.getElementById('pf-preview-wrap');
        var nameEl  = document.getElementById('pf-preview-name');
        preview.src = url;
        wrap.style.display = url ? 'block' : 'none';
        nameEl.textContent = name || '';
        document.getElementById('pf-clear-btn').style.display = url ? '' : 'none';
        pfClosePicker();
    }

    window.pfClearImage = function () { pfSetImage('', ''); };

    window.pfOpenPicker = function () {
        var picker = document.getElementById('pf-picker');
        picker.style.display = picker.style.display === 'none' ? 'block' : 'none';
        if (!_imagesLoaded) pfLoadImages();
    };

    window.pfClosePicker = function () {
        document.getElementById('pf-picker').style.display = 'none';
    };

    var SUB_DIR = '{{ $imageSubDir }}';

    function pfLoadImages() {
        _imagesLoaded = true;
        fetch('{{ route('admin.images.index') }}?subDirectory=' + encodeURIComponent(SUB_DIR), {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            var grid = document.getElementById('pf-grid');
            if (!data || data.length === 0) {
                grid.innerHTML = '<div style="color:#666;font-size:12px;grid-column:1/-1;">No images yet.</div>';
                return;
            }
            grid.innerHTML = '';
            data.forEach(function(img) {
                var div = document.createElement('div');
                div.style.cssText = 'cursor:pointer;border-radius:6px;overflow:hidden;border:2px solid transparent;transition:.15s;';
                div.title = img.fileName;
                div.innerHTML = '<img src="' + img.url + '" style="width:100%;height:70px;object-fit:cover;display:block;" loading="lazy">' +
                    '<div style="padding:3px 4px;font-size:9px;color:#666;">' + Math.round((img.size||0)/1024) + 'KB</div>';
                div.onclick = function() { pfSetImage(img.url, img.fileName); };
                div.onmouseenter = function() { this.style.borderColor = '#23d05e'; };
                div.onmouseleave = function() { this.style.borderColor = 'transparent'; };
                grid.appendChild(div);
            });
        })
        .catch(function(e) {
            document.getElementById('pf-grid').innerHTML = '<div style="color:#ff6b7a;font-size:12px;grid-column:1/-1;">Failed: ' + e.message + '</div>';
        });
    }

    window.pfUploadFile = function (input) {
        if (!input.files || !input.files[0]) return;
        var status = document.getElementById('pf-upload-status');
        status.style.display = 'block';
        status.style.color   = '#888';
        status.textContent   = 'Uploading…';

        var formData = new FormData();
        formData.append('image', input.files[0]);
        formData.append('_token', '{{ csrf_token() }}');
        formData.append('subDirectory', SUB_DIR);

        fetch('{{ route('admin.images.upload') }}', {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
            body: formData,
        })
        .then(function(r) { return r.json().then(function(d) { return { status: r.status, data: d }; }); })
        .then(function(res) {
            var data = res.data;
            if (data.ok && data.data) {
                var img = Array.isArray(data.data) ? data.data[0] : data.data;
                status.style.color = '#23d05e';
                status.textContent = 'Uploaded!';
                _imagesLoaded = false;
                pfSetImage(img.url || img.Url, img.fileName || img.FileName);
                setTimeout(function(){ status.style.display = 'none'; }, 2500);
            } else {
                var msg = data.error || data.message || ('HTTP ' + res.status);
                status.style.color = '#ff6b7a';
                status.textContent = 'Upload failed: ' + msg;
            }
        })
        .catch(function(e) {
            status.style.color = '#ff6b7a';
            status.textContent = 'Upload error: ' + e.message;
        });

        input.value = '';
    };
})();
</script>
