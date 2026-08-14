<?php

namespace App\Traits;

use App\Services\ImageWebpService;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

trait Base64ToFile {
    /**
     * Convert base64 image payload into WebP format and save to storage.
     * 
     * @param string $request Base64 data string
     * @return string Relative storage path (e.g., 'photos/17867261930wFeC6WUBx.webp')
    */
    public function convert($request) : string {
        try {
            $photo = $request;
            $photo = preg_replace('#^data:image/\w+;base64,#i', '', $photo);
            $photo = str_replace(' ', '+', $photo);
            $decodedData = base64_decode($photo);

            if (!$decodedData) {
                throw new \Exception('Invalid base64 image data.');
            }

            $tempFile = sys_get_temp_dir() . '/base64_' . time() . '_' . Str::random(8) . '.tmp';
            file_put_contents($tempFile, $decodedData);

            $webpContent = ImageWebpService::convertToWebpBinary($tempFile, 82);
            @unlink($tempFile);

            $filename = time() . Str::random(10);
            $photoPath = 'photos/' . $filename . '.webp';

            if ($webpContent !== null) {
                Storage::disk('public')->put($photoPath, $webpContent);
            } else {
                Storage::disk('public')->put($photoPath, $decodedData);
            }

            return $photoPath;
        } catch (\Exception $e) {
            throw new \Exception('Failed to save Photo. '.$e->getMessage());
        }
    }
}