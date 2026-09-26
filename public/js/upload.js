document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    var PREVIEW_MAX_SIZE = 1000;
    var EXPORT_MAX_SIZE = 2400;

    var form = document.getElementById('uploadForm');
    if (!form || !window.AlzFilters) { return; }

    var fileInput = document.getElementById('photoInput');
    var dropzone = document.getElementById('dropzone');
    var emptyState = document.getElementById('dropzoneEmpty');
    var previewCanvas = document.getElementById('previewCanvas');
    var filterStudio = document.getElementById('filterStudio');
    var filterNameInput = document.getElementById('filterName');
    var brightnessRange = document.getElementById('brightnessRange');
    var contrastRange = document.getElementById('contrastRange');
    var uploadButton = document.getElementById('uploadButton');

    var loadedImage = null;
    var originalFile = null;
    var renderScheduled = false;

    function currentAdjustments() {
        return { brightness: parseInt(brightnessRange.value, 10) || 0, contrast: parseInt(contrastRange.value, 10) || 0 };
    }

    function hasEdits() {
        var adjustments = currentAdjustments();
        return filterNameInput.value !== 'none' || adjustments.brightness !== 0 || adjustments.contrast !== 0;
    }

    function schedulePreview() {
        if (renderScheduled || !loadedImage) { return; }
        renderScheduled = true;
        requestAnimationFrame(function () {
            renderScheduled = false;
            AlzFilters.render(loadedImage, previewCanvas, filterNameInput.value, currentAdjustments(), PREVIEW_MAX_SIZE);
        });
    }

    function loadFile(file) {
        originalFile = file;
        var objectUrl = URL.createObjectURL(file);
        var image = new Image();

        image.onload = function () {
            loadedImage = image;
            emptyState.classList.add('d-none');
            previewCanvas.classList.remove('d-none');
            filterStudio.classList.remove('d-none');
            schedulePreview();
            document.querySelectorAll('.filter-chip').forEach(function (chip) {
                AlzFilters.renderThumbnail(image, chip.querySelector('canvas'), chip.getAttribute('data-filter'));
            });
        };
        image.onerror = function () {
            window.Alz.toast('This file could not be read as an image.', 'danger');
            URL.revokeObjectURL(objectUrl);
        };
        image.src = objectUrl;
    }

    fileInput.addEventListener('change', function () {
        var chosenFile = fileInput.files && fileInput.files[0];
        if (chosenFile && chosenFile !== originalFile && chosenFile.type.indexOf('image/') === 0) {
            resetEdits();
            loadFile(chosenFile);
        }
    });

    ['dragenter', 'dragover'].forEach(function (eventName) {
        dropzone.addEventListener(eventName, function (dragEvent) {
            dragEvent.preventDefault();
            dropzone.classList.add('is-dragging');
        });
    });
    ['dragleave', 'drop'].forEach(function (eventName) {
        dropzone.addEventListener(eventName, function () { dropzone.classList.remove('is-dragging'); });
    });
    dropzone.addEventListener('drop', function (dropEvent) {
        dropEvent.preventDefault();
        if (dropEvent.dataTransfer.files.length) {
            fileInput.files = dropEvent.dataTransfer.files;
            fileInput.dispatchEvent(new Event('change', { bubbles: true }));
        }
    });

    document.getElementById('filterStrip').addEventListener('click', function (clickEvent) {
        var chip = clickEvent.target.closest('.filter-chip');
        if (!chip) { return; }
        document.querySelectorAll('.filter-chip').forEach(function (otherChip) {
            otherChip.classList.toggle('active', otherChip === chip);
            otherChip.setAttribute('aria-checked', otherChip === chip ? 'true' : 'false');
        });
        filterNameInput.value = chip.getAttribute('data-filter');
        schedulePreview();
    });

    [brightnessRange, contrastRange].forEach(function (range) {
        range.addEventListener('input', function () {
            document.getElementById(range.id.replace('Range', 'Value')).textContent = range.value;
            schedulePreview();
        });
    });

    function resetEdits() {
        brightnessRange.value = 0;
        contrastRange.value = 0;
        document.getElementById('brightnessValue').textContent = '0';
        document.getElementById('contrastValue').textContent = '0';
        var originalChip = document.querySelector('.filter-chip[data-filter="none"]');
        if (originalChip) { originalChip.click(); }
    }
    document.getElementById('resetFilters').addEventListener('click', resetEdits);

    form.addEventListener('submit', function (submitEvent) {
        if (!loadedImage || !hasEdits()) {
            setBusy(true);
            return;
        }

        submitEvent.preventDefault();
        setBusy(true);

        setTimeout(function () {
            var exportCanvas = document.createElement('canvas');
            AlzFilters.render(loadedImage, exportCanvas, filterNameInput.value, currentAdjustments(), EXPORT_MAX_SIZE);

            var outputType = originalFile.type === 'image/png' ? 'image/png' : 'image/jpeg';
            exportCanvas.toBlob(function (blob) {
                var maxBytes = parseInt(fileInput.getAttribute('data-max-bytes'), 10);
                if (blob && blob.size > maxBytes && outputType === 'image/png') {
                    exportCanvas.toBlob(function (jpegBlob) { finishSubmit(jpegBlob, 'image/jpeg'); }, 'image/jpeg', 0.9);
                    return;
                }
                finishSubmit(blob, outputType);
            }, outputType, 0.92);
        }, 30);
    });

    function finishSubmit(blob, mimeType) {
        if (!blob) {
            setBusy(false);
            window.Alz.toast('The filter could not be applied. Please try again.', 'danger');
            return;
        }
        var baseName = originalFile.name.replace(/\.[^.]+$/, '');
        var filteredFile = new File([blob], baseName + (mimeType === 'image/png' ? '.png' : '.jpg'), { type: mimeType });
        var transfer = new DataTransfer();
        transfer.items.add(filteredFile);
        fileInput.files = transfer.files;
        form.submit();
    }

    function setBusy(isBusy) {
        uploadButton.disabled = isBusy;
        uploadButton.querySelector('.spinner-border').classList.toggle('d-none', !isBusy);
    }
});
