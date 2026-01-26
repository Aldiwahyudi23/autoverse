<?php

namespace App\Services\Inspection;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Str;

class ImageCompressionService
{
    /**
     * Compress image for web display
     */
    public function compressForWeb(string $imagePath, int $quality = 60): string
    {
        try {
            // Get full path
            $fullPath = Storage::disk('public')->path($imagePath);
            
            if (!file_exists($fullPath)) {
                return Storage::url($imagePath);
            }
            
            // Check if already optimized
            $optimizedPath = $this->getOptimizedPath($imagePath, $quality);
            $optimizedFullPath = Storage::disk('public')->path($optimizedPath);
            
            if (file_exists($optimizedFullPath)) {
                return Storage::url($optimizedPath);
            }
            
            // Create directory if not exists
            $optimizedDir = dirname($optimizedFullPath);
            if (!file_exists($optimizedDir)) {
                mkdir($optimizedDir, 0755, true);
            }
            
            // Get image dimensions
            list($width, $height) = getimagesize($fullPath);
            
            // Calculate new dimensions (max 1200px on the longest side)
            $maxSize = 1200;
            if ($width > $height && $width > $maxSize) {
                $newWidth = $maxSize;
                $newHeight = intval($height * ($maxSize / $width));
            } elseif ($height > $maxSize) {
                $newHeight = $maxSize;
                $newWidth = intval($width * ($maxSize / $height));
            } else {
                $newWidth = $width;
                $newHeight = $height;
            }
            
            // Resize and compress
            $image = Image::make($fullPath);
            
            // Only resize if necessary
            if ($newWidth !== $width || $newHeight !== $height) {
                $image->resize($newWidth, $newHeight, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
            }
            
            // Save optimized version
            $image->save($optimizedFullPath, $quality);
            
            // Free memory
            $image->destroy();
            
            return Storage::url($optimizedPath);
            
        } catch (\Exception $e) {
            // Fallback to original
            Log::error('Image compression failed: ' . $e->getMessage());
            return Storage::url($imagePath);
        }
    }
    
    /**
     * Compress image for mobile (lower quality)
     */
    public function compressForMobile(string $imagePath, int $quality = 40): string
    {
        try {
            $fullPath = Storage::disk('public')->path($imagePath);
            
            if (!file_exists($fullPath)) {
                return Storage::url($imagePath);
            }
            
            $optimizedPath = $this->getOptimizedPath($imagePath, $quality, 'mobile');
            $optimizedFullPath = Storage::disk('public')->path($optimizedPath);
            
            if (file_exists($optimizedFullPath)) {
                return Storage::url($optimizedPath);
            }
            
            // Create directory
            $optimizedDir = dirname($optimizedFullPath);
            if (!file_exists($optimizedDir)) {
                mkdir($optimizedDir, 0755, true);
            }
            
            list($width, $height) = getimagesize($fullPath);
            
            // Mobile: max 800px
            $maxSize = 800;
            if ($width > $height && $width > $maxSize) {
                $newWidth = $maxSize;
                $newHeight = intval($height * ($maxSize / $width));
            } elseif ($height > $maxSize) {
                $newHeight = $maxSize;
                $newWidth = intval($width * ($maxSize / $height));
            } else {
                $newWidth = $width;
                $newHeight = $height;
            }
            
            $image = Image::make($fullPath);
            
            if ($newWidth !== $width || $newHeight !== $height) {
                $image->resize($newWidth, $newHeight, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
            }
            
            $image->save($optimizedFullPath, $quality);
            $image->destroy();
            
            return Storage::url($optimizedPath);
            
        } catch (\Exception $e) {
            Log::error('Mobile image compression failed: ' . $e->getMessage());
            return Storage::url($imagePath);
        }
    }
    
    /**
     * Create thumbnail for gallery view
     */
    public function createThumbnail(string $imagePath, int $width = 200, int $height = 200): string
    {
        try {
            $fullPath = Storage::disk('public')->path($imagePath);
            
            if (!file_exists($fullPath)) {
                return Storage::url($imagePath);
            }
            
            $thumbnailPath = $this->getThumbnailPath($imagePath, $width, $height);
            $thumbnailFullPath = Storage::disk('public')->path($thumbnailPath);
            
            if (file_exists($thumbnailFullPath)) {
                return Storage::url($thumbnailPath);
            }
            
            // Create directory
            $thumbnailDir = dirname($thumbnailFullPath);
            if (!file_exists($thumbnailDir)) {
                mkdir($thumbnailDir, 0755, true);
            }
            
            $image = Image::make($fullPath);
            
            // Create thumbnail (crop to fit)
            $image->fit($width, $height, function ($constraint) {
                $constraint->upsize();
            });
            
            $image->save($thumbnailFullPath, 70);
            $image->destroy();
            
            return Storage::url($thumbnailPath);
            
        } catch (\Exception $e) {
            Log::error('Thumbnail creation failed: ' . $e->getMessage());
            return Storage::url($imagePath);
        }
    }
    
    /**
     * Save base64 image with compression
     */
    public function saveBase64Image(string $base64Data, string $directory, array $options = []): string
    {
        try {
            // Extract image data
            if (preg_match('/^data:image\/(\w+);base64,/', $base64Data, $matches)) {
                $extension = $matches[1];
                $imageData = substr($base64Data, strpos($base64Data, ',') + 1);
                $imageData = base64_decode($imageData);
                
                // Generate unique filename
                $filename = Str::uuid() . '.' . $extension;
                $path = $directory . '/' . $filename;
                
                // Save original
                Storage::disk('public')->put($path, $imageData);
                
                // Get compression options
                $compress = $options['compress'] ?? true;
                $quality = $options['quality'] ?? 75;
                $maxWidth = $options['max_width'] ?? 1920;
                $maxHeight = $options['max_height'] ?? 1080;
                
                if ($compress) {
                    $this->compressUploadedImage($path, $quality, $maxWidth, $maxHeight);
                }
                
                // Create thumbnail if requested
                if ($options['create_thumbnail'] ?? false) {
                    $this->createThumbnail($path);
                }
                
                return $path;
            }
            
            throw new \Exception('Invalid base64 image data');
            
        } catch (\Exception $e) {
            Log::error('Base64 image save failed: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Compress uploaded image
     */
    public function compressUploadedImage(string $imagePath, int $quality = 75, int $maxWidth = 1920, int $maxHeight = 1080): void
    {
        try {
            $fullPath = Storage::disk('public')->path($imagePath);
            
            if (!file_exists($fullPath)) {
                return;
            }
            
            $image = Image::make($fullPath);
            
            // Get current dimensions
            $width = $image->width();
            $height = $image->height();
            
            // Resize if larger than max dimensions
            if ($width > $maxWidth || $height > $maxHeight) {
                $image->resize($maxWidth, $maxHeight, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
            }
            
            // Optimize based on image type
            $extension = pathinfo($imagePath, PATHINFO_EXTENSION);
            
            if (in_array(strtolower($extension), ['jpg', 'jpeg'])) {
                // Progressive JPEG for better loading
                $image->interlace(true);
            }
            
            // Save with compression
            $image->save($fullPath, $quality);
            $image->destroy();
            
        } catch (\Exception $e) {
            Log::error('Uploaded image compression failed: ' . $e->getMessage());
        }
    }
    
    /**
     * Get file size in human readable format
     */
    public function getFileSize(string $imagePath): string
    {
        try {
            $fullPath = Storage::disk('public')->path($imagePath);
            
            if (!file_exists($fullPath)) {
                return '0 KB';
            }
            
            $bytes = filesize($fullPath);
            
            if ($bytes >= 1073741824) {
                return number_format($bytes / 1073741824, 2) . ' GB';
            } elseif ($bytes >= 1048576) {
                return number_format($bytes / 1048576, 2) . ' MB';
            } elseif ($bytes >= 1024) {
                return number_format($bytes / 1024, 2) . ' KB';
            } else {
                return $bytes . ' bytes';
            }
        } catch (\Exception $e) {
            return 'Unknown';
        }
    }
    
    /**
     * Get image dimensions
     */
    public function getImageDimensions(string $imagePath): array
    {
        try {
            $fullPath = Storage::disk('public')->path($imagePath);
            
            if (!file_exists($fullPath)) {
                return ['width' => 0, 'height' => 0];
            }
            
            list($width, $height) = getimagesize($fullPath);
            
            return [
                'width' => $width,
                'height' => $height,
                'orientation' => $width > $height ? 'landscape' : ($width < $height ? 'portrait' : 'square')
            ];
        } catch (\Exception $e) {
            return ['width' => 0, 'height' => 0, 'orientation' => 'unknown'];
        }
    }
    
    /**
     * Format image data for frontend response
     */
    public function formatImageForResponse($image): array
    {
        $imagePath = ltrim($image->image_path, '/');
        $cleanPath = str_replace('storage/', '', $imagePath);
        
        return [
            'id' => $image->id,
            'image_path' => $cleanPath,
            'path' => $cleanPath,
            'preview' => Storage::url($cleanPath),
            'thumbnail' => $this->createThumbnail($cleanPath, 200, 200),
            'optimized_url' => $this->compressForWeb($cleanPath, 60),
            'mobile_url' => $this->compressForMobile($cleanPath, 40),
            'file_name' => $image->file_name,
            'file_size' => $this->getFileSize($cleanPath),
            'file_size_bytes' => Storage::disk('public')->size($cleanPath),
            'dimensions' => $this->getImageDimensions($cleanPath),
            'created_at' => $image->created_at->toIso8601String(),
            'updated_at' => $image->updated_at->toIso8601String(),
        ];
    }
    
    /**
     * Batch compress images
     */
    public function batchCompress(array $imagePaths, string $type = 'web'): array
    {
        $results = [];
        
        foreach ($imagePaths as $imagePath) {
            try {
                if ($type === 'mobile') {
                    $url = $this->compressForMobile($imagePath);
                } else {
                    $url = $this->compressForWeb($imagePath);
                }
                
                $results[$imagePath] = [
                    'success' => true,
                    'url' => $url,
                    'size_before' => $this->getFileSize($imagePath),
                ];
            } catch (\Exception $e) {
                $results[$imagePath] = [
                    'success' => false,
                    'error' => $e->getMessage(),
                    'url' => Storage::url($imagePath),
                ];
            }
        }
        
        return $results;
    }
    
    /**
     * Clean up old optimized images
     */
    public function cleanupOldOptimizedImages(int $days = 30): int
    {
        try {
            $optimizedDir = 'optimized';
            $count = 0;
            
            $files = Storage::disk('public')->allFiles($optimizedDir);
            
            foreach ($files as $file) {
                $lastModified = Storage::disk('public')->lastModified($file);
                $ageInDays = (time() - $lastModified) / (60 * 60 * 24);
                
                if ($ageInDays > $days) {
                    Storage::disk('public')->delete($file);
                    $count++;
                }
            }
            
            return $count;
        } catch (\Exception $e) {
            Log::error('Cleanup optimized images failed: ' . $e->getMessage());
            return 0;
        }
    }
    
    // =========================================================================
    // PRIVATE HELPER METHODS
    // =========================================================================
    
    private function getOptimizedPath(string $originalPath, int $quality, string $type = 'web'): string
    {
        $pathinfo = pathinfo($originalPath);
        $filename = $pathinfo['filename'];
        $extension = $pathinfo['extension'];
        
        // Create unique hash based on path and quality
        $hash = md5($originalPath . $quality . $type);
        
        return 'optimized/' . $type . '/' . substr($hash, 0, 2) . '/' . $filename . '_' . $quality . 'q.' . $extension;
    }
    
    private function getThumbnailPath(string $originalPath, int $width, int $height): string
    {
        $pathinfo = pathinfo($originalPath);
        $filename = $pathinfo['filename'];
        $extension = $pathinfo['extension'];
        
        $hash = md5($originalPath . $width . $height);
        
        return 'thumbnails/' . substr($hash, 0, 2) . '/' . $filename . '_' . $width . 'x' . $height . '.' . $extension;
    }
}