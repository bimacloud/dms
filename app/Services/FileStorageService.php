<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Http\UploadedFile;

class FileStorageService
{
    protected $disk;

    public function __construct()
    {
        $this->disk = Storage::disk('public');
    }

    /**
     * Store an uploaded file.
     *
     * @param UploadedFile $file
     * @param string $path
     * @param string $name
     * @return string The stored file path
     */
    public function store(UploadedFile $file, string $path, string $name): string
    {
        return $this->disk->putFileAs($path, $file, $name);
    }

    /**
     * Get the file contents.
     *
     * @param string $path
     * @return string|null
     */
    public function get(string $path): ?string
    {
        if ($this->exists($path)) {
            return $this->disk->get($path);
        }
        return null;
    }

    /**
     * Download the file as a streamed response.
     *
     * @param string $path
     * @param string|null $downloadName
     * @param array $headers
     * @return StreamedResponse|null
     */
    public function download(string $path, ?string $downloadName = null, array $headers = []): ?StreamedResponse
    {
        if ($this->exists($path)) {
            return $this->disk->download($path, $downloadName, $headers);
        }
        return null;
    }

    /**
     * Delete a file.
     *
     * @param string $path
     * @return bool
     */
    public function delete(string $path): bool
    {
        if ($this->exists($path)) {
            return $this->disk->delete($path);
        }
        return false;
    }

    /**
     * Check if a file exists.
     *
     * @param string $path
     * @return bool
     */
    public function exists(string $path): bool
    {
        return $this->disk->exists($path);
    }

    /**
     * Set the current disk dynamically if needed.
     *
     * @param string $diskName
     * @return self
     */
    public function setDisk(string $diskName): self
    {
        $this->disk = Storage::disk($diskName);
        return $this;
    }
}
