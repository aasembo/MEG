<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AppController;
use Cake\Http\Exception\NotFoundException;

/**
 * Admin Reports Controller
 *
 * @property \App\Model\Table\ReportsTable $Reports
 */
class ReportsController extends AppController
{
    /**
     * Index method - List all reports (admin view)
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $reports = $this->Reports->find()
            ->contain([
                'Users' => ['Roles'], // Load Users with their Roles
                'Cases' => [
                    'PatientUsers',
                    'Hospitals',
                    'Departments',
                    'Sedations',
                    'CasesExamsProcedures' => [
                        'ExamsProcedures' => [
                            'Exams' => ['Modalities'],
                            'Procedures'
                        ]
                    ],
                    'Documents'
                ],
                'Hospitals'
            ])
            ->order(['Reports.case_id' => 'ASC', 'Reports.created' => 'ASC'])
            ->all();

        $this->set(compact('reports'));
    }

    /**
     * View method - View a single report (admin view)
     *
     * @param string|null $id Report id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $report = $this->Reports->get($id, contain: [
            'Cases' => ['PatientUsers'],
            'Hospitals',
            'Users' => ['Roles']
        ]);

        $this->set(compact('report'));
    }

    /**
     * Preview method - Preview report before downloading (admin view)
     *
     * @param string|null $id Report id.
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function preview($id = null)
    {
        $report = $this->Reports->get($id, contain: [
            'Cases' => ['PatientUsers'],
            'Hospitals',
            'Users' => ['Roles']
        ]);
        
        $reportData = json_decode($report->report_data, true) ?? [];
        $reportContent = $reportData['content'] ?? '';
        
        $this->set(compact('report', 'reportContent'));
    }

    /**
     * Download report for a specific case (generates on-the-fly) - Admin version
     *
     * @param int|null $caseId Case ID
     * @param string $format Format (pdf, html)
     * @return \Cake\Http\Response|null
     */
    public function downloadReport($caseId = null, $format = 'pdf')
    {
        if (!$caseId) {
            $this->Flash->error(__('Invalid case specified.'));
            return $this->redirect(['controller' => 'Cases', 'action' => 'index']);
        }

        try {
            // Load case with all related data for report generation
            $case = $this->fetchTable('Cases')->get($caseId, [
                'contain' => [
                    'PatientUsers' => ['Patients'],
                    'Hospitals',
                    'Departments', 
                    'Sedations',
                    'CasesExamsProcedures' => [
                        'ExamsProcedures' => [
                            'Exams' => ['Modalities'],
                            'Procedures'
                        ]
                    ],
                    'Documents'
                ]
            ]);

            // Generate report content on-the-fly
            $reportContent = $this->generateReportContent($case);
            
            // Create temporary report structure for export
            $reportData = ['content' => $reportContent];
            
            // Create a temporary report entity for the export service
            $tempReport = $this->Reports->newEmptyEntity();
            $tempReport->case_id = $caseId;
            $tempReport->hospital_id = $case->hospital_id; 
            $tempReport->title = 'Case Report #' . $case->id;
            $tempReport->case = $case;
            $tempReport->hospital = $case->hospital;
            
            // Load the export service
            $exportService = new \App\Service\ReportExportService();
            
            $result = $exportService->export($tempReport, $reportData, $format);
            
            return $this->response
                ->withType($result['mimeType'])
                ->withDownload($result['filename'])
                ->withStringBody($result['content']);
                
        } catch (\Exception $e) {
            $this->Flash->error(__('Error generating case report: {0}', $e->getMessage()));
            return $this->redirect(['controller' => 'Cases', 'action' => 'view', $caseId]);
        }
    }

    /**
     * Download existing report (admin view)
     *
     * @param string|null $id Report id.
     * @param string $format Export format (pdf, docx, rtf, html, txt)
     * @return \Cake\Http\Response Downloads file
     */
    public function download($id = null, $format = 'pdf')
    {
        $report = $this->Reports->get($id, contain: [
            'Cases' => ['PatientUsers'],
            'Hospitals'
        ]);
        
        $reportData = json_decode($report->report_data, true) ?? [];
        $reportContent = $reportData['content'] ?? '';
        
        // For our single-content structure, we pass the content directly
        $exportData = ['content' => $reportContent];
        
        // Load the export service
        $exportService = new \App\Service\ReportExportService();
        
        try {
            $result = $exportService->export($report, $exportData, $format);
            
            return $this->response
                ->withType($result['mimeType'])
                ->withDownload($result['filename'])
                ->withStringBody($result['content']);
                
        } catch (\Exception $e) {
            $this->Flash->error(__('Error generating report: {0}', $e->getMessage()));
            return $this->redirect(['action' => 'view', $id]);
        }
    }

    /**
     * Generate report content using database data and template
     * @param $case
     * @return string
     */
    private function generateReportContent($case): string
    {
        // Create a new view instance to render the element
        $view = new \Cake\View\View($this->request, $this->response);
        return $view->element('Reports/report_content', ['case' => $case]);
    }

    /**
     * Delete method (admin only)
     *
     * @param string|null $id Report id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $report = $this->Reports->get($id);
        
        if ($this->Reports->delete($report)) {
            $this->Flash->success(__('The report has been deleted.'));
        } else {
            $this->Flash->error(__('The report could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}