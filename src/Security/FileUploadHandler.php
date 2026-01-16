<?php

namespace SellNow\Security;

/**
 * FileUploadHandler: Safe file upload handling
 * Responsibility: Validate and securely save uploaded files
 */
class FileUploadHandler
{
    private string $uploadDir;
    private Validator $validator;

    public function __construct(string $uploadDir)
    {
        $this->uploadDir = rtrim($uploadDir, '/');
        $this->validator = new Validator();

        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }
    }

    /**
     * Handle image upload
     */
    public function handleImageUpload(array $file): ?string
    {
        if (!$this->validator->validateImage($file)) {
            return null;
        }

        return $this->saveFile($file, 'img_');
    }

    /**
     * Handle product file upload
     */
    public function handleProductFileUpload(array $file): ?string
    {
        if (!$this->validator->validateFile($file)) {
            return null;
        }

        return $this->saveFile($file, 'prod_');
    }

    /**
     * Save file with unique name
     */
    private function saveFile(array $file, string $prefix): ?string
    {
        $filename = $prefix . time() . '_' . $this->sanitizeFilename($file['name']);
        $filepath = $this->uploadDir . '/' . $filename;

        // Additional security: verify MIME type by content
        if (!$this->isValidMimeType($file['tmp_name'], $file['type'])) {
            return null;
        }

        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            chmod($filepath, 0644);
            return 'uploads/' . $filename;
        }

        return null;
    }

    /**
     * Sanitize filename for security
     */
    private function sanitizeFilename(string $filename): string
    {
        // Remove path components
        $filename = basename($filename);
        // Keep only alphanumeric, dash, underscore, and dot
        $filename = preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename);
        return $filename;
    }

    /**
     * Verify MIME type by file content
     */
    private function isValidMimeType(string $filepath, string $declaredType): bool
    {
        if (function_exists('mime_content_type')) {
            $actualType = mime_content_type($filepath);
            return $actualType === $declaredType;
        }

        // Fallback: simple magic number check
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $actualType = finfo_file($finfo, $filepath);
        finfo_close($finfo);

        return $actualType === $declaredType;
    }

    /**
     * Get validation errors
     */
    public function getErrors(): array
    {
        return $this->validator->getErrors();
    }
}
