<?php
declare(strict_types=1);

namespace App\Controller\Scientist;

use App\Controller\AppController;
use Cake\Http\Exception\NotFoundException;
use Cake\Http\Response;
use App\Lib\S3DocumentService;

/**
 * MegReports Controller (Scientist)
 * Handles MEG PowerPoint report downloads for scientists
 * 
 * Note: Scientists can only download PPT reports, not view/edit slides
 *
 * @property \App\Model\Table\ReportSlidesTable $ReportSlides
 * @property \App\Model\Table\ReportsTable $Reports
 */
class MegReportsController extends AppController
{
    /**
     * Download PowerPoint presentation for a MEG report
     * Scientists have read-only access to download PPT reports
     *
     * @param int|null $reportId Report ID
     * @return \Cake\Http\Response Response with PowerPoint file
     * @throws \Cake\Http\Exception\NotFoundException When report not found or no access
     */
    public function downloadPpt($reportId = null)
    {
        $user = $this->request->getAttribute('identity');
        $userId = $user->getIdentifier();
        
        $Reports = $this->fetchTable('Reports');
        $report = $Reports->get($reportId, ['contain' => ['Cases' => ['PatientUsers']]]);
        
        // Scientists can access all reports (permissive access model)
        // If you need to restrict access, uncomment the code below:
        /*
        $hasAccess = $this->fetchTable('CaseAssignments')->exists([
            'case_id' => $report->case_id,
            'assigned_to' => $userId
        ]);
        
        if (!$hasAccess) {
            throw new NotFoundException(__('Report not found or you do not have access to it.'));
        }
        */
        
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
                    
                    // Position image below text (or centered if first slide)
                    if ($index === 0) {
                        // Cover slide - center image
                        $shape->setHeight(300);
                        $shape->setWidth(400);
                        $shape->setOffsetX(280);  // Center horizontally (960 - 400) / 2
                        $shape->setOffsetY(250);  // Position below content
                    } else {
                        // Regular slides - position below text
                        $shape->setHeight(400);  // Larger image for regular slides
                        $shape->setWidth(700);
                        $shape->setOffsetX(130);  // Center horizontally
                        $shape->setOffsetY(150);  // Below text area
                    }
                }
            }
        }
        
        // Generate filename
        $patientName = $report->case->patient_user->last_name ?? 'Unknown';
        $caseId = $report->case_id;
        $filename = "MEG_Report_Case_{$caseId}_{$patientName}.pptx";
        
        // Create temp file for PowerPoint
        $tempPptFile = TMP . 'meg_report_' . uniqid() . '.pptx';
        $writer = \PhpOffice\PhpPresentation\IOFactory::createWriter($presentation, 'PowerPoint2007');
        $writer->save($tempPptFile);
        
        // Clean up temp image files
        foreach ($tempFiles as $tempFile) {
            if (file_exists($tempFile)) {
                unlink($tempFile);
            }
        }
        
        // Send file to browser
        $response = $this->response->withFile(
            $tempPptFile,
            ['download' => true, 'name' => $filename]
        );
        
        // Clean up temp PowerPoint file after sending
        register_shutdown_function(function() use ($tempPptFile) {
            if (file_exists($tempPptFile)) {
                unlink($tempPptFile);
            }
        });
        
        return $response;
    }
}
