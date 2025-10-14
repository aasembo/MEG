<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

class Document extends Entity {
    protected array $_accessible = [
        'case_id' => true,
        'user_id' => true,
        'cases_exams_procedure_id' => true,
        'document_type' => true,
        'file_path' => true,
        'file_type' => true,
        'file_size' => true,
        'original_filename' => true,
        'description' => true,
        'uploaded_at' => true,
        'created' => true,
        'modified' => true,
        'case' => true,
        'user' => true,
        'cases_exams_procedure' => true,
    ];

    /**
     * Get the display name for the document type
     */
    public function getDocumentTypeLabel(): string {
        $typeLabels = [
            'report' => 'Medical Report',
            'image' => 'Medical Image',
            'consent' => 'Consent Form',
            'lab_result' => 'Lab Result',
            'prescription' => 'Prescription',
            'referral' => 'Referral Letter',
            'other' => 'Other Document'
        ];

        return $typeLabels[$this->document_type] ?? 'Unknown';
    }

    /**
     * Get file size in human readable format
     */
    public function getHumanFileSize(): string {
        if (!$this->file_size) {
            return 'Unknown';
        }

        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Get file extension from file path
     */
    public function getFileExtension(): string {
        return strtoupper(pathinfo($this->file_path, PATHINFO_EXTENSION));
    }

    /**
     * Get icon class based on file type
     */
    public function getFileIcon(): string {
        $extension = strtolower($this->getFileExtension());
        
        $iconMap = [
            'pdf' => 'fas fa-file-pdf text-danger',
            'doc' => 'fas fa-file-word text-primary',
            'docx' => 'fas fa-file-word text-primary',
            'jpg' => 'fas fa-file-image text-success',
            'jpeg' => 'fas fa-file-image text-success',
            'png' => 'fas fa-file-image text-success',
            'gif' => 'fas fa-file-image text-success',
            'txt' => 'fas fa-file-alt text-secondary',
        ];

        return $iconMap[$extension] ?? 'fas fa-file text-muted';
    }

    /**
     * Check if the document is an image
     */
    public function isImage(): bool {
        $imageExtensions = ['jpg', 'jpeg', 'png', 'gif'];
        return in_array(strtolower($this->getFileExtension()), $imageExtensions);
    }
}
