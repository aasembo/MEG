<?php
declare(strict_types=1);

namespace App\Controller\Technician;

use App\Controller\AppController;
use Cake\Http\Exception\NotFoundException;

/**
 * Reports Controller
 *
 * @property \App\Model\Table\ReportsTable $Reports
 */
class ReportsController extends AppController
{
    /**
     * Index method - List all reports for technician's cases
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $user = $this->request->getAttribute('identity');
        
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
     * View method - View a single report
     *
     * @param string|null $id Report id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $report = $this->Reports->get($id, contain: [
            'Cases' => ['PatientUsers'],
            'Hospitals'
        ]);

        $this->set(compact('report'));
    }

    /**
     * Add method - Create a new report for a case or edit existing one
     *
     * @param int|null $caseId Case ID to create report for
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add($caseId = null)
    {
        $user = $this->request->getAttribute('identity');
        
        // Load the case
        if (!$caseId) {
            $this->Flash->error(__('Invalid case specified.'));
            return $this->redirect(['controller' => 'Cases', 'action' => 'index']);
        }

        // Check if THIS technician already has a report for this case
        $existingReport = $this->Reports->find()
            ->where([
                'case_id' => $caseId,
                'user_id' => $user->getIdentifier()
            ])
            ->first();
            
        if ($existingReport) {
            // Redirect to edit this technician's existing report
            return $this->redirect(['action' => 'edit', $existingReport->id]);
        }

        // Load case with all related data for report generation
        $case = $this->fetchTable('Cases')->get($caseId, [
            'contain' => [
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
            ]
        ]);
        
        $report = $this->Reports->newEmptyEntity();
        
        // Generate formatted report content from database data using dynamic template
        $reportContent = $this->generateReportContent($case);
        
        if ($this->request->is('post')) {
            $data = $this->request->getData();
            
            // Debug: Log the received data
            \Cake\Log\Log::info('Technician Report Data Received: ' . json_encode($data));
            
            // Store the single editor content as report_data
            $reportDataStructure = [
                'content' => $data['report_content'] ?? ''
            ];
            
            $data['report_data'] = json_encode($reportDataStructure);
            $data['case_id'] = $caseId;
            $data['hospital_id'] = $case->hospital_id;
            $data['status'] = $data['status'] ?? 'pending';
            $data['user_id'] = $user->getIdentifier();
            
            // Debug: Log the final data being saved
            \Cake\Log\Log::info('Technician Report Final Data: ' . json_encode($data));
            
            $report = $this->Reports->patchEntity($report, $data);
            
            // Debug: Log any validation errors
            if ($report->getErrors()) {
                \Cake\Log\Log::error('Technician Report Validation Errors: ' . json_encode($report->getErrors()));
            }
            
            if ($this->Reports->save($report)) {
                \Cake\Log\Log::info('Technician Report Saved Successfully with ID: ' . $report->id);
                $this->Flash->success(__('The report has been saved.'));
                return $this->redirect(['action' => 'view', $report->id]);
            }
            $this->Flash->error(__('The report could not be saved. Please, try again.'));
        }

        $this->set(compact('report', 'case', 'reportContent'));
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
     * Edit method - Edit an existing report
     *
     * @param string|null $id Report id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $user = $this->request->getAttribute('identity');
        $userId = $user->getIdentifier();
        
        $report = $this->Reports->get($id, contain: [
            'Cases' => [
                'PatientUsers',
                'Hospitals',
                'Departments',
                'Sedations',
                'CasesExamsProcedures' => ['ExamsProcedures' => ['Exams', 'Procedures']],
                'Documents'
            ]
        ]);
        
        // Check if current user is the creator of this report
        if ($report->user_id != $userId) {
            $this->Flash->error(__('You can only edit reports that you created.'));
            return $this->redirect(['action' => 'index']);
        }
        
        if ($this->request->is(['patch', 'post', 'put'])) {
            $data = $this->request->getData();
            
            // Update report_data structure with single content
            $reportDataStructure = [
                'content' => $data['report_content'] ?? ''
            ];
            
            $data['report_data'] = json_encode($reportDataStructure);
            
            $report = $this->Reports->patchEntity($report, $data);
            
            if ($this->Reports->save($report)) {
                $this->Flash->success(__('The report has been updated.'));
                return $this->redirect(['action' => 'view', $report->id]);
            }
            $this->Flash->error(__('The report could not be updated. Please, try again.'));
        }

        // Set case data for the template
        $case = $report->case;
        
        // Parse existing report_data JSON
        $reportData = json_decode($report->report_data, true) ?? [];
        $existingContent = $reportData['content'] ?? '';
        
        // Only generate fresh dynamic content if existing content is blank
        if (empty(trim($existingContent))) {
            $reportContent = $this->generateReportContent($case);
        } else {
            $reportContent = $existingContent;
        }
        
        // Render the add template for edit (reuse the same form)
        $this->set(compact('report', 'case', 'reportContent'));
        $this->render('add');
    }

    /**
     * Preview method - Preview report before downloading
     *
     * @param string|null $id Report id.
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function preview($id = null)
    {
        $report = $this->Reports->get($id, contain: [
            'Cases' => ['PatientUsers'],
            'Hospitals'
        ]);
        
        $reportData = json_decode($report->report_data, true) ?? [];
        $reportContent = $reportData['content'] ?? '';
        
        $this->set(compact('report', 'reportContent'));
    }

    /**
     * Download method - Export report in requested format
     *
     * @param string|null $id Report id.
     * @param string $format Export format (pdf, docx, rtf, html, txt)
     * @return \Cake\Http\Response Downloads file
     */
    /**
     * Download report for a specific case (generates on-the-fly)
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

    public function download($id = null, $format = 'pdf')
    {
        $report = $this->Reports->get($id, contain: [
            'Cases' => ['PatientUsers'],
            'Hospitals'
        ]);
        
        $reportData = json_decode($report->report_data, true) ?? [];
        $reportContent = $reportData['content'] ?? '';
        
        // For our single-content structure, we pass the content directly
        // instead of the old multi-section structure
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
     * Delete method
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
