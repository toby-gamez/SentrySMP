{{--
    Partial: Product Form Fields
    Variables: $item (?Product — null when creating, model when editing),
               $categories (Collection<Category>), $imageSubDir (string, defaults to 'products')
    Renders category select, name, description, price, sale %, max-orders, and image picker.
    The image picker scopes its image list to the selected category's slug so images
    are organised per-category in the Images microservice.
--}}
@php $imageSubDir ??= 'products'; @endphp
<div class="form-group">
    <label>Category *</label>
    <select name="category_id" required>
        <option value="">— Select category —</option>
        @foreach($categories as $cat)
            <option value="{{ $cat->id }}" {{ old('category_id', $item?->category_id) == $cat->id ? 'selected' : '' }}>
                {{ $cat->name }}
            </option>
        @endforeach
    </select>
</div>

<div class="form-group">
    <label>Name *</label>
    <input type="text" name="name" value="{{ old('name', $item?->name) }}" required maxlength="100">
</div>

<div class="form-group">
    <label>Description</label>
    <textarea name="description" rows="3" maxlength="500">{{ old('description', $item?->description) }}</textarea>
</div>

<div class="form-group">
    <label>Price (€) *</label>
    <input type="number" name="price" value="{{ old('price', $item?->price) }}" step="0.01" min="0" required>
</div>

<div class="form-group">
    <label>Sale % <span style="color:#888;">(0 = no sale)</span></label>
    <input type="number" name="sale" value="{{ old('sale', $item?->sale ?? 0) }}" min="0" max="100">
</div>

<div class="form-group">
    <label>Max orders per user <span style="color:#888;">(blank = unlimited)</span></label>
    <input type="number" name="global_max_order" value="{{ old('global_max_order', $item?->global_max_order) }}" min="1">
</div>

{{-- Image Picker --}}
<div class="form-group">
    <label>Product Image</label>
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

    var CATEGORY_SLUGS = @json($categories->pluck('slug', 'id'));
    function getSubDir() {
        var sel = document.querySelector('select[name="category_id"]');
        var slug = sel ? CATEGORY_SLUGS[sel.value] : null;
        return slug || '{{ $imageSubDir }}';
    }
    var SUB_DIR = getSubDir();
    (function () {
        var sel = document.querySelector('select[name="category_id"]');
        if (sel) sel.addEventListener('change', function () {
            SUB_DIR = getSubDir();
            _imagesLoaded = false;
        });
    })();

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
