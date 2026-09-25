<?php
declare(strict_types=1);

final class ImageUploader
{
    public static function validate(?array $uploadedFile): ?string
    {
        if ($uploadedFile === null || ($uploadedFile['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return 'Please choose an image to upload.';
        }

        $maxBytes = (int) Config::get('upload.maxBytes');
        $maxMegabytes = round($maxBytes / 1048576, 1);

        switch ((int) $uploadedFile['error']) {
            case UPLOAD_ERR_OK:
                break;
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                return "The image is larger than {$maxMegabytes} MB.";
            default:
                return 'The upload failed. Please try again.';
        }

        if ((int) $uploadedFile['size'] > $maxBytes) {
            return "The image is larger than {$maxMegabytes} MB.";
        }

        $temporaryPath = (string) $uploadedFile['tmp_name'];
        if (!is_uploaded_file($temporaryPath)) {
            return 'Invalid upload.';
        }

        if (self::detectExtension($temporaryPath) === null || @getimagesize($temporaryPath) === false) {
            return 'Only JPG, PNG, GIF or WEBP images are allowed.';
        }

        return null;
    }

    public static function store(array $uploadedFile): string
    {
        $temporaryPath = (string) $uploadedFile['tmp_name'];
        $extension = self::detectExtension($temporaryPath) ?? 'jpg';
        $uploadDirectory = (string) Config::get('upload.directory');

        if (!is_dir($uploadDirectory) && !mkdir($uploadDirectory, 0755, true) && !is_dir($uploadDirectory)) {
            throw new RuntimeException('Upload directory could not be created.');
        }

        $storedFileName = 'photo_' . bin2hex(random_bytes(8)) . '.' . $extension;
        if (!move_uploaded_file($temporaryPath, $uploadDirectory . '/' . $storedFileName)) {
            throw new RuntimeException('The uploaded file could not be saved.');
        }

        return $storedFileName;
    }

    public static function delete(string $storedFileName): bool
    {
        $filePath = Config::get('upload.directory') . '/' . basename($storedFileName);
        return is_file($filePath) && unlink($filePath);
    }

    private static function detectExtension(string $filePath): ?string
    {
        $allowedMimeTypes = Config::get('upload.allowedMime', []);
        $detectedMime = (new finfo(FILEINFO_MIME_TYPE))->file($filePath);
        return $allowedMimeTypes[$detectedMime] ?? null;
    }
}
