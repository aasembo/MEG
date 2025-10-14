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
    
    public function __construct() {
        $this->bucket = env('AWS_S3_BUCKET', 'meg-documents');
        
        $this->s3Client = new S3Client([
            'version' => 'latest',
            'region' => env('AWS_S3_REGION', 'us-east-1'),
            'credentials' => [
                'key' => env('AWS_ACCESS_KEY_ID'),
                'secret' => env('AWS_SECRET_ACCESS_KEY'),
            ]
        ]);
    }
    public function uploadDocument(
        array $fileData, 
        int $caseId, 
        int $patientId, 
        string $documentType,
        ?int $casesExamsProcedureId = null
    ): array {
        try {
            // Validate file
            if (!isset($fileData['tmp_name']) || !is_uploaded_file($fileData['tmp_name'])) {
                return ['success' => false, 'error' => 'Invalid file upload'];
            }
            
            // Check file size (max 50MB)
            $maxSize = 50 * 1024 * 1024; // 50MB
            if ($fileData['size'] > $maxSize) {
                return ['success' => false, 'error' => 'File size exceeds 50MB limit'];
            }
            
            // Validate file type
            $allowedTypes = [
                'application/pdf',
                'image/jpeg',
                'image/png',
                'image/gif',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'text/plain'
            ];
            
            $fileType = mime_content_type($fileData['tmp_name']);
            if (!in_array($fileType, $allowedTypes)) {
                return ['success' => false, 'error' => 'File type not allowed'];
            }
            
            // Generate unique filename
            $extension = pathinfo($fileData['name'], PATHINFO_EXTENSION);
            $timestamp = date('Y-m-d_H-i-s');
            $randomString = bin2hex(random_bytes(8));
            $fileName = "{$documentType}_{$timestamp}_{$randomString}.{$extension}";
            
            // Create S3 key with folder structure
            $s3Key = "Patient_{$caseId}/{$fileName}";
            if ($casesExamsProcedureId) {
                $s3Key = "Patient_{$caseId}/procedure_{$casesExamsProcedureId}/{$fileName}";
            }
            
            // Upload to S3
            $result = $this->s3Client->putObject([
                'Bucket' => $this->bucket,
                'Key' => $s3Key,
                'SourceFile' => $fileData['tmp_name'],
                'ContentType' => $fileType,
                'ServerSideEncryption' => 'AES256',
                'Metadata' => [
                    'case-id' => (string)$caseId,
                    'patient-id' => (string)$patientId,
                    'document-type' => $documentType,
                    'original-filename' => $fileData['name']
                ]
            ]);
            
            return [
                'success' => true,
                'file_path' => $s3Key,
                'file_size' => $fileData['size'],
                'mime_type' => $fileType,
                'original_name' => $fileData['name']
            ];
            
        } catch (AwsException $e) {
            Log::error('S3 Upload Error: ' . $e->getMessage());
            return ['success' => false, 'error' => 'Failed to upload file to storage'];
        } catch (\Exception $e) {
            Log::error('Document Upload Error: ' . $e->getMessage());
            return ['success' => false, 'error' => 'An error occurred during upload'];
        }
    }
    public function getDownloadUrl(string $filePath, int $expirationMinutes = 60): ?string {
        try {
            $cmd = $this->s3Client->getCommand('GetObject', [
                'Bucket' => $this->bucket,
                'Key' => $filePath
            ]);
            
            $request = $this->s3Client->createPresignedRequest($cmd, "+{$expirationMinutes} minutes");
            
            return (string)$request->getUri();
        } catch (AwsException $e) {
            Log::error('S3 Download URL Error: ' . $e->getMessage());
            return null;
        }
    }
    
    public function deleteDocument(string $filePath): bool {
        try {
            $this->s3Client->deleteObject([
                'Bucket' => $this->bucket,
                'Key' => $filePath
            ]);
            
            return true;
        } catch (AwsException $e) {
            Log::error('S3 Delete Error: ' . $e->getMessage());
            return false;
        }
    }
    public function listDocuments(int $caseId, int $patientId, ?int $casesExamsProcedureId = null): array {
        try {
            $prefix = "Patient_{$caseId}/";
            if ($casesExamsProcedureId) {
                $prefix .= "procedure_{$casesExamsProcedureId}/";
            }
            
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
                        'last_modified' => $object['LastModified']
                    ];
                }
            }
            
            return $documents;
        } catch (AwsException $e) {
            Log::error('S3 List Error: ' . $e->getMessage());
            return [];
        }
    }
}