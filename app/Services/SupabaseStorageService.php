<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class SupabaseStorageService
{
    private string $url;
    private string $bucket;
    private string $token;

    public function __construct()
    {
        $this->url = rtrim(config('services.supabase.url'), '/');
        $this->bucket = config('services.supabase.bucket');
        $this->token = config('services.supabase.anon_key');
    }

    public function upload(UploadedFile $file, string $path): ?string
    {
        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $fullPath = trim($path, '/') . '/' . $filename;

        try {
            $response = Http::withToken($this->token)
                ->withHeader('Content-Type', $file->getClientMimeType())
                ->withBody($file->getContent(), $file->getClientMimeType())
                ->post("{$this->url}/storage/v1/object/{$this->bucket}/{$fullPath}");

            if ($response->successful()) {
                return $fullPath;
            }

            return null;
        } catch (\Exception $e) {
            logger()->error('Supabase upload error', ['error' => $e->getMessage()]);
            return null;
        }
    }

    public function uploadRaw(string $content, string $path, string $filename, string $mimeType = 'application/octet-stream'): ?string
    {
        $fullPath = trim($path, '/') . '/' . $filename;

        try {
            $response = Http::withToken($this->token)
                ->withHeader('Content-Type', $mimeType)
                ->withBody($content, $mimeType)
                ->post("{$this->url}/storage/v1/object/{$this->bucket}/{$fullPath}");

            if ($response->successful()) {
                return $fullPath;
            }

            return null;
        } catch (\Exception $e) {
            logger()->error('Supabase upload error', ['error' => $e->getMessage()]);
            return null;
        }
    }

    public function delete(string $path): bool
    {
        try {
            $response = Http::withToken($this->token)
                ->delete("{$this->url}/storage/v1/object/{$this->bucket}/{$path}");

            return $response->successful();
        } catch (\Exception $e) {
            logger()->error('Supabase delete error', ['error' => $e->getMessage()]);
            return false;
        }
    }

    public function getPublicUrl(string $path): string
    {
        return "{$this->url}/storage/v1/object/public/{$this->bucket}/{$path}";
    }
}
