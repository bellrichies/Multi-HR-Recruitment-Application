<?php

declare(strict_types=1);

namespace App\Support;

use App\Core\HttpException;

class FileUpload
{
    public function store(array $file, string $directory): string
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            throw new HttpException('File upload failed.', 400, ['file' => ['File upload failed.']]);
        }

        $maxSize = (int) env('UPLOAD_MAX_SIZE', 5242880);

        if ((int) $file['size'] > $maxSize) {
            throw new HttpException('File size exceeds maximum limit.', 400, [
                'file' => ['File size exceeds maximum limit of ' . number_format($maxSize / 1048576, 1) . 'MB.'],
            ]);
        }

        $extension = strtolower(pathinfo((string) $file['name'], PATHINFO_EXTENSION));
        $allowed = array_map('trim', explode(',', (string) env('UPLOAD_ALLOWED_TYPES', 'pdf,jpg,jpeg,png,doc,docx')));

        if (! in_array($extension, $allowed, true)) {
            throw new HttpException('File type not allowed.', 400, ['file' => ['File type not allowed.']]);
        }

        $allowedMimes = [
            'pdf' => 'application/pdf',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ];
        $mime = mime_content_type((string) $file['tmp_name']);

        if (isset($allowedMimes[$extension]) && $mime !== $allowedMimes[$extension]) {
            throw new HttpException('File MIME type not allowed.', 400, ['file' => ['File MIME type not allowed.']]);
        }

        $safeDirectory = trim($directory, '/\\');
        $targetDirectory = base_path('storage/uploads/' . $safeDirectory);

        if (! is_dir($targetDirectory)) {
            mkdir($targetDirectory, 0755, true);
        }

        $filename = bin2hex(random_bytes(16)) . '.' . $extension;
        $targetPath = $targetDirectory . DIRECTORY_SEPARATOR . $filename;

        if (! move_uploaded_file((string) $file['tmp_name'], $targetPath)) {
            throw new HttpException('File could not be stored.', 500, ['file' => ['File could not be stored.']]);
        }

        return 'storage/uploads/' . $safeDirectory . '/' . $filename;
    }
}
