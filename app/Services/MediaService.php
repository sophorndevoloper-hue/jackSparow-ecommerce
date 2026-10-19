<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class MediaService
{
    /**
     * Store an uploaded file on the given disk.
     */
    public function upload(UploadedFile $file, string $directory = 'uploads', string $disk = 'public'): string
    {
        return $file->store($directory, $disk);
    }

    /**
     * Delete a file from storage if it exists.
     */
    public function delete(?string $path, string $disk = 'public'): bool
    {
        if ($path && Storage::disk($disk)->exists($path)) {
            return Storage::disk($disk)->delete($path);
        }

        return false;
    }

    /**
     * Replace an existing file with a newly uploaded one, cleaning up the previous file.
     */
    public function replace(?string $oldPath, ?UploadedFile $newFile, string $directory = 'uploads', string $disk = 'public'): ?string
    {
        if ($newFile) {
            $this->delete($oldPath, $disk);

            return $this->upload($newFile, $directory, $disk);
        }

        return $oldPath;
    }
}
