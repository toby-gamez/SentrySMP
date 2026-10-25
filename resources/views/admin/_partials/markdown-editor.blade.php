{{--
    Partial: Markdown Editor
    Variables: $name (string), $value (string|null), $id (string|null),
               $rows (int, default 8), $maxlength (int|null)
    Renders a textarea with a Markdown formatting toolbar and character counter.
--}}
@php
    $editorId       = $id ?? 'md-editor-' . $name;
    $editorName     = $name;
    $editorValue    = $value ?? '';
    $editorRows     = $rows ?? 8;
    $editorMax      = $maxlength ?? null;
@endphp
<div class="md-editor" data-md-editor>
    <div class="md-toolbar" role="toolbar" aria-label="Markdown formatting">
        <button type="button" class="md-btn" data-md-action="bold" title="Bold (Ctrl+B)" aria-label="Bold">
            <i class="bi bi-type-bold"></i>
        </button>
        <button type="button" class="md-btn" data-md-action="italic" title="Italic (Ctrl+I)" aria-label="Italic">
            <i class="bi bi-type-italic"></i>
        </button>
        <button type="button" class="md-btn" data-md-action="strike" title="Strikethrough" aria-label="Strikethrough">
            <i class="bi bi-type-strikethrough"></i>
        </button>
        <span class="md-toolbar-sep"></span>
        <button type="button" class="md-btn" data-md-action="heading" title="Heading" aria-label="Heading">
            <i class="bi bi-type-h2"></i>
        </button>
        <button type="button" class="md-btn" data-md-action="link" title="Link" aria-label="Link">
            <i class="bi bi-link-45deg"></i>
        </button>
        <span class="md-toolbar-sep"></span>
        <button type="button" class="md-btn" data-md-action="ul" title="Bulleted list" aria-label="Bulleted list">
            <i class="bi bi-list-ul"></i>
        </button>
        <button type="button" class="md-btn" data-md-action="ol" title="Numbered list" aria-label="Numbered list">
            <i class="bi bi-list-ol"></i>
        </button>
        <button type="button" class="md-btn" data-md-action="quote" title="Quote" aria-label="Quote">
            <i class="bi bi-quote"></i>
        </button>
        <span class="md-toolbar-sep"></span>
        <button type="button" class="md-btn" data-md-action="code" title="Inline code" aria-label="Inline code">
            <i class="bi bi-code"></i>
        </button>
        <button type="button" class="md-btn" data-md-action="codeblock" title="Code block" aria-label="Code block">
            <i class="bi bi-code-square"></i>
        </button>
        <button type="button" class="md-btn" data-md-action="hr" title="Horizontal rule" aria-label="Horizontal rule">
            <i class="bi bi-dash-lg"></i>
        </button>
    </div>

    <textarea id="{{ $editorId }}" name="{{ $editorName }}" rows="{{ $editorRows }}"
              data-md-input @if($editorMax) maxlength="{{ $editorMax }}" @endif>{{ $editorValue }}</textarea>

    <div class="md-editor-footer">
        <span class="md-hint">Markdown: <code>**bold**</code>, <code>*italic*</code>, <code>## heading</code>, <code>- list</code>, <code>[link](url)</code></span>
        @if($editorMax)
            <span class="md-counter" data-md-counter>0 / {{ $editorMax }}</span>
        @endif
    </div>
</div>

<script>
(function () {
    document.querySelectorAll('[data-md-editor]').forEach(function (editor) {
        if (editor.dataset.mdReady) return;
        editor.dataset.mdReady = '1';

        var textarea = editor.querySelector('[data-md-input]');
        var counter  = editor.querySelector('[data-md-counter]');
        if (!textarea) return;

        function updateCounter() {
            if (!counter) return;
            var max = parseInt(textarea.getAttribute('maxlength'), 10);
            counter.textContent = textarea.value.length + (max > 0 ? ' / ' + max : '');
        }

        function finish() {
            updateCounter();
            textarea.dispatchEvent(new Event('input', { bubbles: true }));
        }

        function wrap(before, after, placeholder) {
            var start = textarea.selectionStart;
            var end   = textarea.selectionEnd;
            var selected = textarea.value.slice(start, end);
            var inner = selected || placeholder;
            var insert = before + inner + after;

            textarea.focus();
            textarea.setRangeText(insert, start, end, 'end');

            if (selected) {
                var cursor = start + insert.length;
                textarea.setSelectionRange(cursor, cursor);
            } else {
                textarea.setSelectionRange(start + before.length, start + before.length + inner.length);
            }

            finish();
        }

        function prefixLines(prefix) {
            var start = textarea.selectionStart;
            var end   = textarea.selectionEnd;
            var value = textarea.value;
            var lineStart = value.lastIndexOf('\n', start - 1) + 1;
            var lineEnd   = value.indexOf('\n', end);
            if (lineEnd === -1) lineEnd = value.length;

            var block = value.slice(lineStart, lineEnd);
            var lines = block.split('\n');
            var updated = lines.map(function (line) {
                if (!line.length) return lines.length === 1 ? prefix : line;
                return prefix + line;
            }).join('\n');

            textarea.focus();
            textarea.setRangeText(updated, lineStart, lineEnd, 'end');

            if (start !== end) {
                textarea.setSelectionRange(lineStart, lineStart + updated.length);
            } else {
                var cursor = lineStart + updated.length;
                textarea.setSelectionRange(cursor, cursor);
            }

            finish();
        }

        function insertText(text) {
            var start = textarea.selectionStart;
            var end   = textarea.selectionEnd;

            textarea.focus();
            textarea.setRangeText(text, start, end, 'end');
            finish();
        }

        function insertLink() {
            var start = textarea.selectionStart;
            var end   = textarea.selectionEnd;
            var selected = textarea.value.slice(start, end);
            var label = selected || 'link text';
            var url = 'https://';
            var insert = '[' + label + '](' + url + ')';

            textarea.focus();
            textarea.setRangeText(insert, start, end, 'end');

            var urlStart = start + 1 + label.length + 2;
            textarea.setSelectionRange(urlStart, urlStart + url.length);
            finish();
        }

        function applyAction(action) {
            switch (action) {
                case 'bold':      wrap('**', '**', 'bold text'); break;
                case 'italic':    wrap('*', '*', 'italic text'); break;
                case 'strike':    wrap('~~', '~~', 'strikethrough'); break;
                case 'heading':   prefixLines('## '); break;
                case 'link':      insertLink(); break;
                case 'ul':        prefixLines('- '); break;
                case 'ol':        prefixLines('1. '); break;
                case 'quote':     prefixLines('> '); break;
                case 'code':      wrap('`', '`', 'code'); break;
                case 'codeblock': wrap("```\n", "\n```", 'code'); break;
                case 'hr':        insertText("\n---\n"); break;
            }
        }

        editor.querySelectorAll('[data-md-action]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                applyAction(btn.getAttribute('data-md-action'));
            });
        });

        textarea.addEventListener('keydown', function (event) {
            if (!(event.ctrlKey || event.metaKey) || event.altKey) return;
            var key = event.key.toLowerCase();
            if (key === 'b') { event.preventDefault(); applyAction('bold'); }
            if (key === 'i') { event.preventDefault(); applyAction('italic'); }
        });

        textarea.addEventListener('input', updateCounter);
        updateCounter();
    });
})();
</script>
