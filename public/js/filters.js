var AlzFilters = (function () {
    'use strict';

    function clamp(value) {
        return value < 0 ? 0 : (value > 255 ? 255 : value);
    }

    function luminance(red, green, blue) {
        return 0.299 * red + 0.587 * green + 0.114 * blue;
    }

    var pointFilters = {
        grayscale: function (red, green, blue) {
            var gray = luminance(red, green, blue);
            return [gray, gray, gray];
        },
        sepia: function (red, green, blue) {
            return [
                0.393 * red + 0.769 * green + 0.189 * blue,
                0.349 * red + 0.686 * green + 0.168 * blue,
                0.272 * red + 0.534 * green + 0.131 * blue
            ];
        },
        invert: function (red, green, blue) {
            return [255 - red, 255 - green, 255 - blue];
        },
        warm: function (red, green, blue) {
            return [red * 1.08 + 14, green * 1.02 + 4, blue * 0.86];
        },
        cool: function (red, green, blue) {
            return [red * 0.88, green * 1.01 + 2, blue * 1.1 + 14];
        },
        noir: function (red, green, blue) {
            var gray = (luminance(red, green, blue) - 128) * 1.55 + 128;
            return [gray, gray, gray];
        },
        vintage: function (red, green, blue) {
            var sepia = pointFilters.sepia(red, green, blue);
            var mix = 0.65;
            return [
                (red * (1 - mix) + sepia[0] * mix) * 0.86 + 26,
                (green * (1 - mix) + sepia[1] * mix) * 0.86 + 20,
                (blue * (1 - mix) + sepia[2] * mix) * 0.80 + 18
            ];
        }
    };

    var kernels = {
        sharpen: [[0, -1, 0, -1, 5, -1, 0, -1, 0], 1, 0],
        blur: [[1, 2, 1, 2, 4, 2, 1, 2, 1], 16, 0],
        emboss: [[-2, -1, 0, -1, 1, 1, 0, 1, 2], 1, 0],
        'edge-detect': [[-1, -1, -1, -1, 8, -1, -1, -1, -1], 1, 0]
    };

    function convolve(source, kernel, divisor, offset) {
        var width = source.width;
        var height = source.height;
        var input = source.data;
        var output = new ImageData(width, height);
        var out = output.data;

        for (var y = 0; y < height; y++) {
            for (var x = 0; x < width; x++) {
                var sumRed = 0, sumGreen = 0, sumBlue = 0;

                for (var kernelY = -1; kernelY <= 1; kernelY++) {
                    var sampleY = Math.min(height - 1, Math.max(0, y + kernelY));
                    for (var kernelX = -1; kernelX <= 1; kernelX++) {
                        var sampleX = Math.min(width - 1, Math.max(0, x + kernelX));
                        var sampleIndex = (sampleY * width + sampleX) * 4;
                        var weight = kernel[(kernelY + 1) * 3 + (kernelX + 1)];
                        sumRed += input[sampleIndex] * weight;
                        sumGreen += input[sampleIndex + 1] * weight;
                        sumBlue += input[sampleIndex + 2] * weight;
                    }
                }

                var targetIndex = (y * width + x) * 4;
                out[targetIndex] = clamp(sumRed / divisor + offset);
                out[targetIndex + 1] = clamp(sumGreen / divisor + offset);
                out[targetIndex + 2] = clamp(sumBlue / divisor + offset);
                out[targetIndex + 3] = input[targetIndex + 3];
            }
        }
        return output;
    }

    function vignette(imageData, strength) {
        var width = imageData.width, height = imageData.height, pixels = imageData.data;
        var centerX = width / 2, centerY = height / 2;
        var maxDistanceSquared = centerX * centerX + centerY * centerY;

        for (var y = 0; y < height; y++) {
            for (var x = 0; x < width; x++) {
                var deltaX = x - centerX, deltaY = y - centerY;
                var factor = 1 - strength * ((deltaX * deltaX + deltaY * deltaY) / maxDistanceSquared);
                var index = (y * width + x) * 4;
                pixels[index] *= factor;
                pixels[index + 1] *= factor;
                pixels[index + 2] *= factor;
            }
        }
        return imageData;
    }

    function pixelate(imageData, blockSize) {
        var width = imageData.width, height = imageData.height, pixels = imageData.data;

        for (var blockY = 0; blockY < height; blockY += blockSize) {
            for (var blockX = 0; blockX < width; blockX += blockSize) {
                var endX = Math.min(blockX + blockSize, width), endY = Math.min(blockY + blockSize, height);
                var totalRed = 0, totalGreen = 0, totalBlue = 0, pixelCount = 0, x, y, index;

                for (y = blockY; y < endY; y++) {
                    for (x = blockX; x < endX; x++) {
                        index = (y * width + x) * 4;
                        totalRed += pixels[index]; totalGreen += pixels[index + 1]; totalBlue += pixels[index + 2];
                        pixelCount++;
                    }
                }
                for (y = blockY; y < endY; y++) {
                    for (x = blockX; x < endX; x++) {
                        index = (y * width + x) * 4;
                        pixels[index] = totalRed / pixelCount;
                        pixels[index + 1] = totalGreen / pixelCount;
                        pixels[index + 2] = totalBlue / pixelCount;
                    }
                }
            }
        }
        return imageData;
    }

    function adjust(imageData, brightness, contrast) {
        if (!brightness && !contrast) { return imageData; }

        var brightnessOffset = brightness * 1.28;
        var contrastLevel = contrast * 2.55;
        var contrastFactor = (259 * (contrastLevel + 255)) / (255 * (259 - contrastLevel));
        var pixels = imageData.data;

        for (var index = 0; index < pixels.length; index += 4) {
            pixels[index] = clamp(contrastFactor * (pixels[index] - 128) + 128 + brightnessOffset);
            pixels[index + 1] = clamp(contrastFactor * (pixels[index + 1] - 128) + 128 + brightnessOffset);
            pixels[index + 2] = clamp(contrastFactor * (pixels[index + 2] - 128) + 128 + brightnessOffset);
        }
        return imageData;
    }

    function apply(imageData, filterName, adjustments) {
        var result = imageData;
        var pointFilter = pointFilters[filterName];

        if (pointFilter) {
            var pixels = result.data;
            for (var index = 0; index < pixels.length; index += 4) {
                var mapped = pointFilter(pixels[index], pixels[index + 1], pixels[index + 2]);
                pixels[index] = clamp(mapped[0]);
                pixels[index + 1] = clamp(mapped[1]);
                pixels[index + 2] = clamp(mapped[2]);
            }
            if (filterName === 'vintage') { vignette(result, 0.45); }
        } else if (kernels[filterName]) {
            var kernelSpec = kernels[filterName];
            result = convolve(result, kernelSpec[0], kernelSpec[1], kernelSpec[2]);
        } else if (filterName === 'vignette') {
            vignette(result, 0.75);
        } else if (filterName === 'pixelate') {
            pixelate(result, Math.max(4, Math.round(Math.max(result.width, result.height) / 70)));
        }

        adjustments = adjustments || {};
        return adjust(result, adjustments.brightness || 0, adjustments.contrast || 0);
    }

    function render(sourceImage, targetCanvas, filterName, adjustments, maxSize) {
        var sourceWidth = sourceImage.naturalWidth || sourceImage.width;
        var sourceHeight = sourceImage.naturalHeight || sourceImage.height;
        var scale = Math.min(1, maxSize / Math.max(sourceWidth, sourceHeight));

        targetCanvas.width = Math.max(1, Math.round(sourceWidth * scale));
        targetCanvas.height = Math.max(1, Math.round(sourceHeight * scale));

        var context = targetCanvas.getContext('2d', { willReadFrequently: true });
        context.drawImage(sourceImage, 0, 0, targetCanvas.width, targetCanvas.height);

        if (filterName !== 'none' || (adjustments && (adjustments.brightness || adjustments.contrast))) {
            var imageData = context.getImageData(0, 0, targetCanvas.width, targetCanvas.height);
            context.putImageData(apply(imageData, filterName, adjustments), 0, 0);
        }
        return targetCanvas;
    }

    function renderThumbnail(sourceImage, thumbCanvas, filterName) {
        var sourceWidth = sourceImage.naturalWidth || sourceImage.width;
        var sourceHeight = sourceImage.naturalHeight || sourceImage.height;
        var cropSize = Math.min(sourceWidth, sourceHeight);
        var context = thumbCanvas.getContext('2d', { willReadFrequently: true });

        context.drawImage(sourceImage, (sourceWidth - cropSize) / 2, (sourceHeight - cropSize) / 2, cropSize, cropSize,
            0, 0, thumbCanvas.width, thumbCanvas.height);
        if (filterName !== 'none') {
            var imageData = context.getImageData(0, 0, thumbCanvas.width, thumbCanvas.height);
            context.putImageData(apply(imageData, filterName, null), 0, 0);
        }
    }

    return {
        names: ['none'].concat(Object.keys(pointFilters), Object.keys(kernels), ['vignette', 'pixelate']),
        apply: apply,
        render: render,
        renderThumbnail: renderThumbnail
    };
})();
