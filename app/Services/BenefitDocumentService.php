<?php

namespace App\Services;

use Illuminate\Support\Str;

class BenefitDocumentService
{
    public function __construct(private SupabaseStorageService $storage) {}

    public function processDocumentation(?array $documentation): ?array
    {
        if (!$documentation || !isset($documentation['base64Data'])) {
            return $documentation;
        }

        $base64Data = $documentation['base64Data'];

        if (!$this->isValidBase64($base64Data)) {
            return $documentation;
        }

        $fileName = $documentation['fileName'] ?? 'document.pdf';
        $extension = pathinfo($fileName, PATHINFO_EXTENSION) ?: 'pdf';

        $decodedData = $this->decodeBase64($base64Data);

        if (!$decodedData) {
            return $documentation;
        }

        $filename = Str::uuid() . '.' . $extension;
        $path = "benefit-requests";

        try {
            $uploadedPath = $this->uploadToSupabase($decodedData, $path, $filename);

            if (!$uploadedPath) {
                logger()->error('Failed to upload benefit document', [
                    'fileName' => $fileName,
                    'fileSize' => $documentation['fileSize'] ?? null,
                ]);
                return $documentation;
            }

            return [
                'fileName' => $fileName,
                'fileSize' => $documentation['fileSize'] ?? null,
                'url' => $this->storage->getPublicUrl($uploadedPath),
                'uploadedAt' => now()->toIso8601String(),
            ];
        } catch (\Exception $e) {
            logger()->error('Error processing benefit documentation', [
                'error' => $e->getMessage(),
                'fileName' => $fileName,
            ]);
            return $documentation;
        }
    }

    private function isValidBase64(string $data): bool
    {
        if (strpos($data, 'data:') === 0) {
            $data = substr($data, strpos($data, ',') + 1);
        }

        return base64_encode(base64_decode($data, true)) === $data;
    }

    private function decodeBase64(string $data): ?string
    {
        if (strpos($data, 'data:') === 0) {
            $data = substr($data, strpos($data, ',') + 1);
        }

        $decoded = base64_decode($data, true);

        return $decoded !== false ? $decoded : null;
    }

    private function uploadToSupabase(string $fileContent, string $path, string $filename): ?string
    {
        try {
            $tempFile = tmpfile();
            fwrite($tempFile, $fileContent);
            rewind($tempFile);

            $metadata = stream_get_meta_data($tempFile);
            $mimeType = $this->getMimeType($filename);

            $uploadedPath = $this->storage->uploadRaw(
                $fileContent,
                $path,
                $filename,
                $mimeType
            );

            return $uploadedPath;
        } catch (\Exception $e) {
            logger()->error('Supabase upload error', ['error' => $e->getMessage()]);
            return null;
        }
    }

    private function getMimeType(string $filename): string
    {
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        return match ($extension) {
            'pdf' => 'application/pdf',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xls' => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'png' => 'image/png',
            'jpg', 'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'txt' => 'text/plain',
            default => 'application/octet-stream',
        };
    }
}
