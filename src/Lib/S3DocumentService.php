<?php
declare(strict_types=1);

namespace App\Lib;

use Aws\S3\S3Client;
use Aws\Exception\AwsException;
use Cake\Log\Log;
use function Cake\Core\env;

class S3DocumentService {
    private $s3Client;
    private $bucket;
    private $s3Enabled;
    private $localUploadPath;
    
    public function __construct() {
        $awsKey = env('AWS_ACCESS_KEY_ID');
        $awsSecret = env('AWS_SECRET_ACCESS_KEY');
        $this->bucket = env('AWS_S3_BUCKET');
        
        // Convert string 'true'/'false' to boolean
        $s3EnabledEnv = env('AWS_S3_ENABLED');
        $this->s3Enabled = filter_var($s3EnabledEnv, FILTER_VALIDATE_BOOLEAN);
        
        if ($this->s3Enabled) {
            try {
                $this->s3Client = new S3Client([
                    'version' => 'latest',
                    'region' => env('AWS_S3_REGION'),
                    'credentials' => [
                        'key' => $awsKey,
                        'secret' => $awsSecret,
                    ]
                ]);
                Log::info('S3 storage enabled');
            } catch (\Exception $e) {
                Log::warning('S3 failed, using local storage: ' . $e->getMessage());
                $this->s3Enabled = false;
            }
        } else {
            Log::info('S3 not configured, using local storage');
        }
        
        $this->localUploadPath = WWW_ROOT . 'uploads' . DS;
        
        if (!$this->s3Enabled && !is_dir($this->localUploadPath)) {
            mkdir($this->localUploadPath, 0755, true);
        }
    }
    
    /**
     * Format Case ID with padding (minimum 6 characters)
     * Example: 123 becomes XXXXX123, 123456 stays 123456
     *
     * @param int $caseId The case ID to format
     * @return string Padded case ID with X prefix
     */
    private function formatCaseId(int $caseId): string {
        $caseIdStr = (string)$caseId;
        $minLength = 6;
        
        if (strlen($caseIdStr) < $minLength) {
            $padding = str_repeat('X', $minLength - strlen($caseIdStr));
            return $padding . $caseIdStr;
        }
        
        return $caseIdStr;
    }
    
    /**
     * Sanitize filename to keep original name but make it S3-safe
     *
     * @param string $filename Original filename
     * @return string Sanitized filename
     */
    private function sanitizeFilename(string $filename): string {
        // Replace spaces with underscores
        $filename = str_replace(' ', '_', $filename);
        // Remove any characters that aren't alphanumeric, dash, underscore, or dot
        $filename = preg_replace('/[^a-zA-Z0-9\-_\.]/', '', $filename);
        return $filename;
    }
    
    public function uploadDocument($fileData, int $caseId, int $patientId, string $documentType, ?int $casesExamsProcedureId = null): array {
        if ($this->s3Enabled) {
            return $this->uploadToS3($fileData, $caseId, $patientId, $documentType, $casesExamsProcedureId);
        } else {
            return $this->uploadToLocal($fileData, $caseId, $patientId, $documentType, $casesExamsProcedureId);
        }
    }
    
    private function uploadToS3($fileData, int $caseId, int $patientId, string $documentType, ?int $casesExamsProcedureId = null): array {
        try {
            if (is_object($fileData) && method_exists($fileData, 'getStream')) {
                $originalName = $fileData->getClientFilename();
                $mimeType = $fileData->getClientMediaType();
                $size = $fileData->getSize();
                
                $stream = $fileData->getStream();
                $tmpFile = tempnam(sys_get_temp_dir(), 'upload_');
                file_put_contents($tmpFile, $stream->getContents());
                
                $fileArray = [
                    'tmp_name' => $tmpFile,
                    'name' => $originalName,
                    'size' => $size,
                    'type' => $mimeType
                ];
                $needsCleanup = true;
            } else {
                $fileArray = $fileData;
                $needsCleanup = false;
            }
            
            $validation = $this->validateFile($fileArray);
            if (!$validation['success']) {
                if ($needsCleanup && isset($tmpFile) && file_exists($tmpFile)) {
                    unlink($tmpFile);
                }
                return $validation;
            }
            
            // Keep original filename with sanitization
            $originalName = $fileArray['name'];
            $sanitizedName = $this->sanitizeFilename($originalName);
            
            // Format case ID with padding (min 6 chars)
            $paddedCaseId = $this->formatCaseId($caseId);
            
            // New path structure: Meg_{CaseID}/{DocumentType}/{OriginalFilename}
            $s3Key = "Meg_{$paddedCaseId}/{$documentType}/{$sanitizedName}";
            
            $result = $this->s3Client->putObject([
                'Bucket' => $this->bucket,
                'Key' => $s3Key,
                'SourceFile' => $fileArray['tmp_name'],
                'ContentType' => $fileArray['type'],
                'ServerSideEncryption' => 'AES256',
                'Metadata' => [
                    'case-id' => (string)$caseId,
                    'patient-id' => (string)$patientId,
                    'document-type' => $documentType,
                    'procedure-id' => $casesExamsProcedureId ? (string)$casesExamsProcedureId : 'none',
                    'original-name' => $fileArray['name']
                ]
            ]);
            
            if ($needsCleanup && isset($tmpFile) && file_exists($tmpFile)) {
                unlink($tmpFile);
            }
            
            Log::info('Document uploaded to S3', ['key' => $s3Key]);
            
            return [
                'success' => true,
                'file_path' => $s3Key,
                'file_size' => $fileArray['size'],
                'mime_type' => $fileArray['type'],
                'original_name' => $fileArray['name'],
                'storage_type' => 's3'
            ];
            
        } catch (AwsException $e) {
            if (isset($needsCleanup) && $needsCleanup && isset($tmpFile) && file_exists($tmpFile)) {
                unlink($tmpFile);
            }
            
            Log::error('S3 Upload failed: ' . $e->getMessage());
            
            return [
                'success' => false,
                'error' => 'Failed to upload to S3: ' . $e->getMessage()
            ];
        } catch (\Exception $e) {
            if (isset($needsCleanup) && $needsCleanup && isset($tmpFile) && file_exists($tmpFile)) {
                unlink($tmpFile);
            }
            
            Log::error('S3 exception: ' . $e->getMessage());
            
            return [
                'success' => false,
                'error' => 'Failed to upload: ' . $e->getMessage()
            ];
        }
    }
    
    private function uploadToLocal($fileData, int $caseId, int $patientId, string $documentType, ?int $casesExamsProcedureId = null): array {
        try {
            if (is_object($fileData) && method_exists($fileData, 'getStream')) {
                $originalName = $fileData->getClientFilename();
                $mimeType = $fileData->getClientMediaType();
                $size = $fileData->getSize();
                
                $stream = $fileData->getStream();
                $fileArray = [
                    'name' => $originalName,
                    'size' => $size,
                    'type' => $mimeType,
                    'stream' => $stream
                ];
            } else {
                $fileArray = $fileData;
            }
            
            $validation = $this->validateFile($fileArray);
            if (!$validation['success']) {
                return $validation;
            }
            
            // Keep original filename with sanitization
            $originalName = $fileArray['name'];
            $sanitizedName = $this->sanitizeFilename($originalName);
            
            // Format case ID with padding (min 6 chars)
            $paddedCaseId = $this->formatCaseId($caseId);
            
            // New path structure: Meg_{CaseID}/{DocumentType}/{OriginalFilename}
            $relativePath = "Meg_{$paddedCaseId}" . DS . $documentType . DS;
            $fullDir = $this->localUploadPath . $relativePath;
            
            if (!is_dir($fullDir)) {
                mkdir($fullDir, 0755, true);
            }
            
            $fullPath = $fullDir . $sanitizedName;
            
            if (isset($fileArray['stream'])) {
                file_put_contents($fullPath, $fileArray['stream']->getContents());
            } else {
                if (!move_uploaded_file($fileArray['tmp_name'], $fullPath)) {
                    copy($fileArray['tmp_name'], $fullPath);
                }
            }
            
            $storagePath = 'uploads/' . str_replace(DS, '/', $relativePath) . $sanitizedName;
            
            Log::info('Document uploaded locally', ['path' => $storagePath]);
            
            return [
                'success' => true,
                'file_path' => $storagePath,
                'file_size' => $fileArray['size'],
                'mime_type' => $fileArray['type'],
                'original_name' => $fileArray['name'],
                'storage_type' => 'local'
            ];
            
        } catch (\Exception $e) {
            Log::error('Local upload failed: ' . $e->getMessage());
            
            return [
                'success' => false,
                'error' => 'Failed to upload locally: ' . $e->getMessage()
            ];
        }
    }
    
    private function validateFile(array $fileArray): array {
        if (empty($fileArray['name'])) {
            return ['success' => false, 'error' => 'Invalid file data'];
        }
        
        if (isset($fileArray['tmp_name']) && !file_exists($fileArray['tmp_name'])) {
            return ['success' => false, 'error' => 'Temporary file not found'];
        }
        
        $maxSize = 50 * 1024 * 1024;
        if ($fileArray['size'] > $maxSize) {
            return ['success' => false, 'error' => 'File exceeds 50MB limit'];
        }
        
        $allowedTypes = [
            'application/pdf',
            'image/jpeg',
            'image/jpg',
            'image/png',
            'image/gif',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'text/plain'
        ];
        
        if (!in_array($fileArray['type'], $allowedTypes)) {
            return ['success' => false, 'error' => 'Invalid file type'];
        }
        
        return ['success' => true];
    }
    
    public function getDownloadUrl(string $filePath, int $expirationMinutes = 60): string {
        if ($this->s3Enabled && strpos($filePath, 'uploads/') !== 0) {
            try {
                $cmd = $this->s3Client->getCommand('GetObject', [
                    'Bucket' => $this->bucket,
                    'Key' => $filePath
                ]);
                
                $request = $this->s3Client->createPresignedRequest($cmd, "+{$expirationMinutes} minutes");
                return (string)$request->getUri();
            } catch (\Exception $e) {
                Log::error('Failed to generate presigned URL: ' . $e->getMessage());
                return '';
            }
        } else {
            return '/' . str_replace(DS, '/', $filePath);
        }
    }
    
    public function deleteDocument(string $filePath): bool {
        try {
            if ($this->s3Enabled && strpos($filePath, 'uploads/') !== 0) {
                $this->s3Client->deleteObject([
                    'Bucket' => $this->bucket,
                    'Key' => $filePath
                ]);
                Log::info('Document deleted from S3', ['key' => $filePath]);
            } else {
                $fullPath = WWW_ROOT . str_replace('/', DS, $filePath);
                if (file_exists($fullPath)) {
                    unlink($fullPath);
                    Log::info('Document deleted locally', ['path' => $filePath]);
                }
            }
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to delete: ' . $e->getMessage());
            return false;
        }
    }
    
    public function listDocuments(int $caseId, int $patientId, ?int $casesExamsProcedureId = null): array {
        try {
            // Format case ID with padding (min 6 chars)
            $paddedCaseId = $this->formatCaseId($caseId);
            
            if ($this->s3Enabled) {
                // New path structure: Meg_{CaseID}/
                $prefix = "Meg_{$paddedCaseId}/";
                
                $result = $this->s3Client->listObjectsV2([
                    'Bucket' => $this->bucket,
                    'Prefix' => $prefix
                ]);
                
                $documents = [];
                if (isset($result['Contents'])) {
                    foreach ($result['Contents'] as $object) {
                        $documents[] = [
                            'key' => $object['Key'],
                            'size' => $object['Size'],
                            'last_modified' => $object['LastModified']->format('Y-m-d H:i:s')
                        ];
                    }
                }
                return $documents;
            } else {
                // New path structure: Meg_{CaseID}/
                $dirPath = $this->localUploadPath . "Meg_{$paddedCaseId}" . DS;
                
                $documents = [];
                if (is_dir($dirPath)) {
                    // Recursively scan all subdirectories (document types)
                    $iterator = new \RecursiveIteratorIterator(
                        new \RecursiveDirectoryIterator($dirPath, \RecursiveDirectoryIterator::SKIP_DOTS),
                        \RecursiveIteratorIterator::LEAVES_ONLY
                    );
                    
                    foreach ($iterator as $file) {
                        if ($file->isFile()) {
                            $relativePath = str_replace($this->localUploadPath, '', $file->getPathname());
                            $documents[] = [
                                'key' => 'uploads/' . str_replace(DS, '/', $relativePath),
                                'size' => $file->getSize(),
                                'last_modified' => date('Y-m-d H:i:s', $file->getMTime())
                            ];
                        }
                    }
                }
                return $documents;
            }
        } catch (\Exception $e) {
            Log::error('Failed to list documents: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Check if S3 storage is enabled
     *
     * @return bool
     */
    public function isS3Enabled(): bool {
        return $this->s3Enabled;
    }
    
    /**
     * Get S3 client instance
     *
     * @return S3Client|null
     */
    public function getS3Client(): ?S3Client {
        return $this->s3Client;
    }
}
