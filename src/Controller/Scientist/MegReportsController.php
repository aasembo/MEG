<?php
declare(strict_types=1);

namespace App\Controller\Scientist;

use App\Controller\AppController;
use App\Controller\Trait\PptDownloadTrait;
use Cake\Http\Exception\NotFoundException;
use Cake\Http\Response;
use App\Lib\S3DocumentService;

/**
 * MegReports Controller (Scientist)
 * Handles MEG PowerPoint report downloads for scientists
 * 
 * Note: Scientists can download PPT reports created by doctors
 *
 * @property \App\Model\Table\ReportSlidesTable $ReportSlides
 * @property \App\Model\Table\ReportsTable $Reports
 */
class MegReportsController extends AppController
{
    use PptDownloadTrait;
    
    /**
     * Download PowerPoint presentation for a MEG report
     * Scientists have access to all reports
     *
     * @param int|null $reportId Report ID
     * @return \Cake\Http\Response Response with PowerPoint file
     * @throws \Cake\Http\Exception\NotFoundException When report not found
     */
    public function downloadPpt($reportId = null)
    {
        // Scientists have access to all reports - use shared trait method
        return $this->downloadPptReadOnly($reportId);
    }
}
