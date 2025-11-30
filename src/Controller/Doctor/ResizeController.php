<?php
declare(strict_types=1);

namespace App\Controller\Doctor;

use App\Controller\AppController;
use Cake\Http\Response;

/**
 * Resize Controller
 * Handles image resizing for doctors
 *
 * @property \App\Model\Table\UsersTable $Users
 */
class ResizeController extends AppController
{
    /**
     * Before filter callback
     *
     * @param \Cake\Event\EventInterface $event Event
     * @return \Cake\Http\Response|null|void
     */
    public function beforeFilter(\Cake\Event\EventInterface $event)
    {
        parent::beforeFilter($event);
        
        // Skip CSRF check for process action (file upload with multipart/form-data)
        if ($this->request->getParam('action') === 'process') {
            $this->request = $this->request->withAttribute('csrfToken', null);
        }
    }

    /**
     * Index method - Image resize interface
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $this->set('title', 'Resize Images');
    }

    /**
     * Process method - Resize and download images
     *
     * @return \Cake\Http\Response|null
     */
    public function process()
    {
        $this->request->allowMethod(['post']);
        
        $width = (int)($this->request->getData('width') ?? 760);
        $height = (int)($this->request->getData('height') ?? 450);
        $images = $this->request->getData('images');
        
        if (empty($images)) {
            $this->Flash->error('Please upload at least one image.');
            return $this->redirect(['action' => 'index']);
        }
        
        // Create temporary directory for resized images
        $tempDir = TMP . 'resized_' . uniqid();
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0755, true);
        }
        
        $resizedFiles = [];
        
        foreach ($images as $image) {
            if ($image->getError() !== UPLOAD_ERR_OK) {
                continue;
            }
            
            $filename = $image->getClientFilename();
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            
            // Only process image files
            if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) {
                continue;
            }
            
            $tmpPath = $image->getStream()->getMetadata('uri');
            
            // Check image dimensions
            list($currentWidth, $currentHeight) = getimagesize($tmpPath);
            
            // If image is already smaller than target, copy it without resizing
            if ($currentWidth <= $width && $currentHeight <= $height) {
                $resizedPath = $tempDir . DS . $filename;
                copy($tmpPath, $resizedPath);
                $resizedFiles[] = [
                    'path' => $resizedPath,
                    'name' => $filename
                ];
            } else {
                // Resize image
                $resizedPath = $tempDir . DS . 'resized_' . $filename;
                if ($this->resizeImage($tmpPath, $resizedPath, $width, $height, $ext)) {
                    $resizedFiles[] = [
                        'path' => $resizedPath,
                        'name' => 'resized_' . $filename
                    ];
                }
            }
        }
        
        if (empty($resizedFiles)) {
            $this->Flash->error('No images were resized. Please check file formats.');
            return $this->redirect(['action' => 'index']);
        }
        
        // If single image, download directly
        if (count($resizedFiles) === 1) {
            $file = $resizedFiles[0];
            $response = $this->response->withFile(
                $file['path'],
                ['download' => true, 'name' => $file['name']]
            );
            
            // Cleanup
            register_shutdown_function(function() use ($tempDir) {
                $this->deleteDirectory($tempDir);
            });
            
            return $response;
        }
        
        // Multiple images - create ZIP
        $zipPath = $tempDir . DS . 'resized_images.zip';
        $zip = new \ZipArchive();
        
        if ($zip->open($zipPath, \ZipArchive::CREATE) === true) {
            foreach ($resizedFiles as $file) {
                $zip->addFile($file['path'], $file['name']);
            }
            $zip->close();
            
            $response = $this->response->withFile(
                $zipPath,
                ['download' => true, 'name' => 'resized_images_' . date('Y-m-d_His') . '.zip']
            );
            
            // Cleanup after download
            register_shutdown_function(function() use ($tempDir) {
                $this->deleteDirectory($tempDir);
            });
            
            return $response;
        }
        
        $this->Flash->error('Failed to create download archive.');
        return $this->redirect(['action' => 'index']);
    }
    
    /**
     * Resize image to specified dimensions
     *
     * @param string $sourcePath Source image path
     * @param string $destPath Destination path
     * @param int $targetWidth Target width
     * @param int $targetHeight Target height
     * @param string $ext File extension
     * @return bool Success
     */
    private function resizeImage(string $sourcePath, string $destPath, int $targetWidth, int $targetHeight, string $ext): bool
    {
        // Create image resource based on type
        switch ($ext) {
            case 'jpg':
            case 'jpeg':
                $source = @imagecreatefromjpeg($sourcePath);
                break;
            case 'png':
                $source = @imagecreatefrompng($sourcePath);
                break;
            case 'gif':
                $source = @imagecreatefromgif($sourcePath);
                break;
            default:
                return false;
        }
        
        if (!$source) {
            return false;
        }
        
        // Get original dimensions
        $originalWidth = imagesx($source);
        $originalHeight = imagesy($source);
        
        // Calculate aspect ratio
        $ratio = min($targetWidth / $originalWidth, $targetHeight / $originalHeight);
        $newWidth = (int)($originalWidth * $ratio);
        $newHeight = (int)($originalHeight * $ratio);
        
        // Create new image with high quality
        $resized = imagecreatetruecolor($newWidth, $newHeight);
        
        // Preserve transparency for PNG and GIF
        if ($ext === 'png' || $ext === 'gif') {
            imagealphablending($resized, false);
            imagesavealpha($resized, true);
            $transparent = imagecolorallocatealpha($resized, 255, 255, 255, 127);
            imagefilledrectangle($resized, 0, 0, $newWidth, $newHeight, $transparent);
        } else {
            // Enable antialiasing for better quality
            imagealphablending($resized, true);
        }
        
        // High-quality resize with bicubic interpolation
        imagecopyresampled($resized, $source, 0, 0, 0, 0, $newWidth, $newHeight, $originalWidth, $originalHeight);
        
        // Apply sharpening to restore clarity after resize
        $sharpenMatrix = [
            [-1, -1, -1],
            [-1, 16, -1],
            [-1, -1, -1]
        ];
        $divisor = array_sum(array_map('array_sum', $sharpenMatrix));
        imageconvolution($resized, $sharpenMatrix, $divisor, 0);
        
        // Save based on type with maximum quality
        $result = false;
        switch ($ext) {
            case 'jpg':
            case 'jpeg':
                // JPEG quality 100 = maximum quality, no compression
                $result = imagejpeg($resized, $destPath, 100);
                break;
            case 'png':
                // PNG compression 0 = no compression, maximum quality
                $result = imagepng($resized, $destPath, 0);
                break;
            case 'gif':
                $result = imagegif($resized, $destPath);
                break;
        }
        
        // Free memory
        imagedestroy($source);
        imagedestroy($resized);
        
        return $result;
    }
    
    /**
     * Delete directory recursively
     *
     * @param string $dir Directory path
     * @return void
     */
    private function deleteDirectory(string $dir): void
    {
        if (!file_exists($dir)) {
            return;
        }
        
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . DS . $file;
            is_dir($path) ? $this->deleteDirectory($path) : unlink($path);
        }
        rmdir($dir);
    }
}
