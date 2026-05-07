window.imageUploaderInterop = {
    registerDropZone: function (dropZoneElement, inputFileElement) {
        function onDrop(e) {
            var files = e.dataTransfer ? e.dataTransfer.files : null;
            if (!files || files.length === 0) return;

            var dt = new DataTransfer();
            dt.items.add(files[0]);
            inputFileElement.files = dt.files;
            inputFileElement.dispatchEvent(new Event('change', { bubbles: true }));
        }

        dropZoneElement.addEventListener('drop', onDrop);
        dropZoneElement._imageUploaderDropHandler = onDrop;
    },

    unregisterDropZone: function (dropZoneElement) {
        if (dropZoneElement && dropZoneElement._imageUploaderDropHandler) {
            dropZoneElement.removeEventListener('drop', dropZoneElement._imageUploaderDropHandler);
            delete dropZoneElement._imageUploaderDropHandler;
        }
    },

    clickElement: function (element) {
        element.click();
    }
};
