<?php

namespace App\Services;

use App\Models\File;
use App\Models\StorageProvider;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;

class StorageService
{
    /**
     * Get the dynamic disk name for a provider.
     * Registers it if not already configured in runtime.
     */
    public function getDisk($source = null): string
    {
        $provider = null;

        if ($source instanceof File) {
            $provider = $source->storageProvider;
        } elseif ($source instanceof StorageProvider) {
            $provider = $source;
        }

        // Fallback to default active provider
        if (!$provider) {
            $provider = StorageProvider::where('is_default', true)
                ->where('is_active', true)
                ->first() ?: StorageProvider::where('is_active', true)->first();
        }

        if (!$provider) {
            // Fallback to .env defined s3 if no DB providers exist yet
            return 's3';
        }

        return $this->registerProvider($provider);
    }

    /**
     * Register a StorageProvider as a Laravel filesystem disk at runtime.
     */
    protected function registerProvider(StorageProvider $provider): string
    {
        $diskName = "s3_provider_{$provider->id}";

        // Skip if already registered in this request
        if (Config::has("filesystems.disks.{$diskName}")) {
            return $diskName;
        }

        Config::set("filesystems.disks.{$diskName}", [
            'driver' => $provider->driver,
            'key' => $provider->key,
            'secret' => $provider->secret,
            'region' => $provider->region ?? 'us-east-1',
            'bucket' => $provider->bucket,
            'url' => Config::get('app.url'), // Not strictly needed for pre-signed URLs
            'endpoint' => $provider->endpoint,
            'use_path_style_endpoint' => $provider->use_path_style_endpoint,
            'throw' => false,
        ]);

        return $diskName;
    }

    /**
     * Generate a pre-signed URL for direct browser upload.
     */
    public function getUploadUrl(string $path, $provider = null, int $expiresInMinutes = 30): string
    {
        $disk = $this->getDisk($provider);
        $s3 = Storage::disk($disk);
        
        $client = $s3->getClient();
        $command = $client->getCommand('PutObject', [
            'Bucket' => Config::get("filesystems.disks.{$disk}.bucket"),
            'Key' => $path,
        ]);

        $request = $client->createPresignedRequest($command, "+{$expiresInMinutes} minutes");
        
        return (string) $request->getUri();
    }

    /**
     * Generate a pre-signed URL for downloading a file.
     */
    public function getDownloadUrl(File $file, int $expiresInMinutes = 15): string
    {
        $disk = $this->getDisk($file);
        return Storage::disk($disk)->temporaryUrl($file->storage_path, now()->addMinutes($expiresInMinutes), [
            'ResponseContentDisposition' => 'attachment; filename="' . $file->display_name . '"',
        ]);
    }

    public function getPreviewUrl(File $file, int $expiresInMinutes = 15): string
    {
        $disk = $this->getDisk($file);
        return Storage::disk($disk)->temporaryUrl($file->storage_path, now()->addMinutes($expiresInMinutes), [
            'ResponseContentDisposition' => 'inline; filename="' . $file->display_name . '"',
        ]);
    }

    /**
     * Generate a pre-signed URL for a thumbnail.
     */
    public function getThumbnailUrl(File $file, int $expiresInMinutes = 15): ?string
    {
        if (!$file->thumbnail_path) {
            return null;
        }

        $disk = $this->getDisk($file);
        return Storage::disk($disk)->temporaryUrl($file->thumbnail_path, now()->addMinutes($expiresInMinutes), [
            'ResponseContentDisposition' => 'inline',
        ]);
    }

    /**
     * Generate a unique storage path for a new file.
     */
    public function generateStoragePath(int $userId, string $extension): string
    {
        $datePath = now()->format('Y/m/d');
        $uuid = (string) Str::uuid();
        
        return "uploads/user_{$userId}/{$datePath}/{$uuid}.{$extension}";
    }

    /**
     * Delete an object.
     */
    public function delete(File $file): bool
    {
        $disk = $this->getDisk($file);
        
        // Delete main file
        $deleted = Storage::disk($disk)->delete($file->storage_path);
        
        // Delete thumbnail if exists
        if ($file->thumbnail_path) {
            Storage::disk($disk)->delete($file->thumbnail_path);
        }
        
        return $deleted;
    }
}
