<?php

namespace App\Jobs;

use App\Models\File;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class GenerateFileThumbnail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(public File $file)
    {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Only process images
        if (!Str::startsWith($this->file->mime_type, 'image/')) {
            return;
        }

        try {
            $disk = $this->file->disk;
            if (!Storage::disk($disk)->exists($this->file->storage_path)) {
                return;
            }

            // Create temp file
            $tempPath = tempnam(sys_get_temp_dir(), 'thumb_');
            file_put_contents($tempPath, Storage::disk($disk)->get($this->file->storage_path));

            // Generate thumbnail
            $manager = new ImageManager(new Driver());
            $image = $manager->read($tempPath);
            $image->cover(200, 200);
            
            // Generate path for thumbnail
            $filename = pathinfo($this->file->storage_path, PATHINFO_FILENAME);
            $thumbName = "thumbnails/{$filename}_thumb.jpg";
            
            // Upload to same disk
            Storage::disk($disk)->put($thumbName, (string) $image->toJpeg());
            
            // Update file record
            $this->file->update(['thumbnail_path' => $thumbName]);
            
            // Cleanup
            if (file_exists($tempPath)) {
                unlink($tempPath);
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Thumbnail generation failed for file ' . $this->file->id . ': ' . $e->getMessage());
        }
    }
}
