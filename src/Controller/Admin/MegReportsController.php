<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AppController;
use Cake\Http\Exception\NotFoundException;
use Cake\Http\Response;
use App\Lib\S3DocumentService;

/**
 * MegReports Controller (Admin)
 * Handles MEG PowerPoint report downloads for administrators
 * 
 * Note: Admins can only download PPT reports for viewing purposes
 *
 * @property \App\Model\Table\ReportSlidesTable $ReportSlides
 * @property \App\Model\Table\ReportsTable $Reports
 */
class MegReportsController extends AppController
{
    /**
     * Download PowerPoint presentation for a MEG report
     * Admins have read-only access to download PPT reports
     *
     * @param int|null $reportId Report ID
     * @return \Cake\Http\Response Response with PowerPoint file
     * @throws \Cake\Http\Exception\NotFoundException When report not found
     */
    public function downloadPpt($reportId = null)
    {
        $Reports = $this->fetchTable('Reports');
        $report = $Reports->get($reportId, ['contain' => ['Cases' => ['PatientUsers']]]);
        
        // Admins have access to all reports
        
        // Get all slides for this report
        $ReportSlides = $this->fetchTable('ReportSlides');
        $slides = $ReportSlides->find()
            ->where(['report_id' => $reportId])
            ->order(['slide_order' => 'ASC'])
            ->all();
        
        // Generate URLs for slide images
        $s3Service = new S3DocumentService();
        foreach ($slides as $slide) {
            if ($slide->file_path) {
                $slide->image_url = $s3Service->getDownloadUrl($slide->file_path);
            }
        }
        
        // Create PowerPoint presentation
        $presentation = new \PhpOffice\PhpPresentation\PhpPresentation();
        $presentation->removeSlideByIndex(0); // Remove default slide
        
        // Track temp files for cleanup after PowerPoint is generated
        $tempFiles = [];
        
        foreach ($slides as $index => $slide) {
            $pptSlide = $presentation->createSlide();
            
            // Set slide background to white
            $background = $pptSlide->getBackground();
            if ($background) {
                $background->setColor(new \PhpOffice\PhpPresentation\Style\Color('FFFFFFFF'));
            }
            
            // Add description/content if present
            if (!empty($slide->description)) {
                // First slide (cover) gets special formatting with heading
                if ($index === 0) {
                    // Split description into heading and content
                    $lines = explode("\n", $slide->description);
                    $heading = array_shift($lines); // First line is heading
                    $content = implode("\n", array_slice($lines, 2)); // Skip empty lines after heading
                    
                    // Add heading
                    $headingShape = $pptSlide->createRichTextShape();
                    $headingShape->setHeight(80);
                    $headingShape->setWidth(900);
                    $headingShape->setOffsetX(30);
                    $headingShape->setOffsetY(50);
                    $headingShape->getActiveParagraph()->getAlignment()
                        ->setHorizontal(\PhpOffice\PhpPresentation\Style\Alignment::HORIZONTAL_CENTER);
                    
                    $headingRun = $headingShape->createTextRun($heading);
                    $headingRun->getFont()
                        ->setSize(24)
                        ->setBold(true)
                        ->setColor(new \PhpOffice\PhpPresentation\Style\Color('FF000000'));
                    
                    // Add content below heading
                    $textShape = $pptSlide->createRichTextShape();
                    $textShape->setHeight(420);
                    $textShape->setWidth(900);
                    $textShape->setOffsetX(30);
                    $textShape->setOffsetY(150);
                    $textShape->getActiveParagraph()->getAlignment()
                        ->setHorizontal(\PhpOffice\PhpPresentation\Style\Alignment::HORIZONTAL_CENTER);
                    
                    $textRun = $textShape->createTextRun($content);
                    $textRun->getFont()
                        ->setSize(16)
                        ->setColor(new \PhpOffice\PhpPresentation\Style\Color('FF000000'));
                } else {
                    // Regular slides - text at top
                    $textShape = $pptSlide->createRichTextShape();
                    $textShape->setHeight(100);  // Reduced height for text
                    $textShape->setWidth(900);
                    $textShape->setOffsetX(30);
                    $textShape->setOffsetY(30);
                    
                    $textRun = $textShape->createTextRun($slide->description);
                    $textRun->getFont()
                        ->setSize(24)
                        ->setColor(new \PhpOffice\PhpPresentation\Style\Color('FF000000'));
                }
            }
            
            // Add image if present
            if (!empty($slide->image_url)) {
                // Use generated URL (presigned for S3 or local path)
                if (strpos($slide->image_url, 'http') === 0) {
                    // S3 URL - download to temp file
                    $tempImage = TMP . 'ppt_img_' . uniqid() . '.jpg';
                    $imageContent = file_get_contents($slide->image_url);
                    file_put_contents($tempImage, $imageContent);
                    $imagePath = $tempImage;
                    $tempFiles[] = $tempImage; // Track for cleanup
                } else {
                    // Local path
                    $imagePath = WWW_ROOT . ltrim($slide->image_url, '/');
                }
                
                if (file_exists($imagePath)) {
                    $shape = $pptSlide->createDrawingShape();
                    $shape->setPath($imagePath);
                    
                    // Get original image dimensions
                    list($imageWidth, $imageHeight) = getimagesize($imagePath);
                    
                    // Use original image size (no scaling)
                    $shape->setWidth($imageWidth);
                    $shape->setHeight($imageHeight);
                    
                    // Center image horizontally
                    $slideWidth = 960;
                    $centerX = (int)(($slideWidth - $imageWidth) / 2);
                    
                    // Position image below text
                    $offsetY = ($index === 0) ? 150 : 140;  // Below text
                    $shape->setOffsetY($offsetY);
                    $shape->setOffsetX($centerX);
                }
            }
        }
        
        // Generate filename with case ID padded to 6 characters
        $caseId = str_pad((string)$report->case_id, 6, 'X', STR_PAD_LEFT);
        $filename = 'MEG_Report_CASE_' . $caseId . '.pptx';
        
        // Generate PowerPoint file
        $writer = new \PhpOffice\PhpPresentation\Writer\PowerPoint2007($presentation);
        
        // Save to temporary file
        $tmpFile = TMP . 'ppt_' . uniqid() . '.pptx';
        $writer->save($tmpFile);
        
        // Clean up temp image files after PowerPoint is generated
        foreach ($tempFiles as $tempFile) {
            if (file_exists($tempFile)) {
                unlink($tempFile);
            }
        }
        
        // Send file to browser
        $response = $this->response->withFile($tmpFile, [
            'download' => true,
            'name' => $filename
        ]);
        
        // Clean up temp PowerPoint file after sending
        register_shutdown_function(function() use ($tmpFile) {
            if (file_exists($tmpFile)) {
                unlink($tmpFile);
            }
        });
        
        return $response;
    }
}
