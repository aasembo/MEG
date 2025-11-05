<?php
declare(strict_types=1);

namespace App\Controller\Scientist;

use App\Controller\AppController;
use Cake\Http\Exception\NotFoundException;

/**
 * Reports Controller (Scientist)
 *
 * @property \App\Model\Table\ReportsTable $Reports
 */
class ReportsController extends AppController
{
    /**
     * Index method - List reports for cases assigned to the scientist
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $user = $this->request->getAttribute('identity');
        $userId = $user->getIdentifier();
        
        // Only show reports for cases assigned to this scientist
        $reports = $this->Reports->find()
            ->contain(['Users' => ['Roles'], 'Cases' => ['PatientUsers'], 'Hospitals'])
            ->matching('Cases.CaseAssignments', function ($q) use ($userId) {
                return $q->where(['CaseAssignments.assigned_to' => $userId]);
            })
            ->order(['Reports.created' => 'DESC'])
            ->all();

        $this->set(compact('reports'));
    }

    /**
     * View method - View a single report (only if case is assigned to scientist)
     *
     * @param string|null $id Report id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found or not accessible.
     */
    public function view($id = null)
    {
        $user = $this->request->getAttribute('identity');
        $userId = $user->getIdentifier();
        
        // Get the report and verify case assignment
        $report = $this->Reports->find()
            ->contain(['Cases' => ['PatientUsers'], 'Hospitals'])
            ->matching('Cases.CaseAssignments', function ($q) use ($userId) {
                return $q->where(['CaseAssignments.assigned_to' => $userId]);
            })
            ->where(['Reports.id' => $id])
            ->firstOrFail();

        $this->set(compact('report'));
    }

    /**
     * Add method - Create a new scientist report for a case, potentially using existing technician report as base
     *
     * @param int|null $caseId Case ID to create report for
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add($caseId = null)
    {
        $user = $this->request->getAttribute('identity');
        $userId = $user->getIdentifier();
        
        // Get case ID from parameter or query string
        $caseId = $caseId ?? $this->request->getQuery('case_id');
        
        // Load the case
        if (!$caseId) {
            $this->Flash->error(__('Invalid case specified.'));
            return $this->redirect(['controller' => 'Cases', 'action' => 'index']);
        }

        // Verify the case is assigned to this scientist
        $caseAssignment = $this->fetchTable('CaseAssignments')->find()
            ->where([
                'case_id' => $caseId,
                'assigned_to' => $userId
            ])
            ->first();
            
        if (!$caseAssignment) {
            $this->Flash->error(__('You do not have access to this case.'));
            return $this->redirect(['controller' => 'Cases', 'action' => 'index']);
        }

        // Check if THIS scientist already has a report for this case
        $existingScientistReport = $this->Reports->find()
            ->where([
                'case_id' => $caseId,
                'user_id' => $userId,
                'scientist_review IS NOT' => null
            ])
            ->first();
            
        if ($existingScientistReport) {
            // Redirect to edit this scientist's existing report
            return $this->redirect(['action' => 'edit', $existingScientistReport->id]);
        }

        // Look for existing technician report to use as base
        $technicianReport = $this->Reports->find()
            ->where([
                'case_id' => $caseId,
                'scientist_review IS' => null
            ])
            ->first();

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
        
        // Generate report content - use technician report if available, otherwise generate new
        $reportContent = '';
        if ($technicianReport) {
            // Extract content from existing technician report
            $technicianReportData = json_decode($technicianReport->report_data, true) ?? [];
            $reportContent = $technicianReportData['content'] ?? '';
            
            // If no content, fallback to generated content
            if (empty($reportContent)) {
                $reportContent = $this->generateReportContent($case);
            }
        } else {
            // Generate fresh content from case data
            $reportContent = $this->generateReportContent($case);
        }
        
        if ($this->request->is('post')) {
            $data = $this->request->getData();
            
            // Store the single editor content as report_data
            $reportDataStructure = [
                'content' => $data['report_content'] ?? ''
            ];
            
            // Mark this as a scientist report by setting scientist_review
            $scientistReview = [
                'reviewed_by' => $user->getIdentifier(),
                'reviewed_at' => date('Y-m-d H:i:s'),
                'scientist_notes' => $data['scientist_notes'] ?? '',
                'confidence_score' => $data['confidence_score'] ?? null
            ];
            
            $data['report_data'] = json_encode($reportDataStructure);
            $data['scientist_review'] = json_encode($scientistReview);
            $data['case_id'] = $caseId;
            $data['hospital_id'] = $case->hospital_id;
            $data['status'] = $data['status'] ?? 'pending';
            $data['user_id'] = $userId;
            
            $report = $this->Reports->patchEntity($report, $data);
            
            if ($this->Reports->save($report)) {
                $this->Flash->success(__('The scientist report has been saved.'));
                return $this->redirect(['action' => 'view', $report->id]);
            }
            $this->Flash->error(__('The report could not be saved. Please, try again.'));
        }

        $this->set(compact('report', 'case', 'reportContent', 'technicianReport'));
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
     * Edit method - Edit existing report
     *
     * @param string|null $id Report id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     */
    public function edit($id = null)
    {
        $user = $this->request->getAttribute('identity');
        $userId = $user->getIdentifier();
        
        // Get the report with case assignment verification
        $report = $this->Reports->find()
            ->contain(['Cases' => [
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
            ]])
            ->matching('Cases.CaseAssignments', function ($q) use ($userId) {
                return $q->where(['CaseAssignments.assigned_to' => $userId]);
            })
            ->where(['Reports.id' => $id])
            ->first();
            
        if (!$report) {
            $this->Flash->error(__('Report not found or you do not have access to this case.'));
            return $this->redirect(['action' => 'index']);
        }
        
        // Check if current user is the creator of this report
        if ($report->user_id != $userId) {
            $this->Flash->error(__('You can only edit reports that you created.'));
            return $this->redirect(['action' => 'index']);
        }
        
        if ($this->request->is(['patch', 'post', 'put'])) {
            $data = $this->request->getData();
            
            // Store the single editor content as report_data
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
        $user = $this->request->getAttribute('identity');
        $userId = $user->getIdentifier();
        
        // Get the report with case assignment verification
        $report = $this->Reports->find()
            ->contain(['Cases' => ['PatientUsers'], 'Hospitals'])
            ->matching('Cases.CaseAssignments', function ($q) use ($userId) {
                return $q->where(['CaseAssignments.assigned_to' => $userId]);
            })
            ->where(['Reports.id' => $id])
            ->first();
            
        if (!$report) {
            $this->Flash->error(__('Report not found or you do not have access to this case.'));
            return $this->redirect(['action' => 'index']);
        }
        
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
    public function download($id = null, $format = 'pdf')
    {
        $user = $this->request->getAttribute('identity');
        $userId = $user->getIdentifier();
        
        // Get the report with case assignment verification
        $report = $this->Reports->find()
            ->contain(['Cases' => ['PatientUsers'], 'Hospitals'])
            ->matching('Cases.CaseAssignments', function ($q) use ($userId) {
                return $q->where(['CaseAssignments.assigned_to' => $userId]);
            })
            ->where(['Reports.id' => $id])
            ->first();
            
        if (!$report) {
            $this->Flash->error(__('Report not found or you do not have access to this case.'));
            return $this->redirect(['action' => 'index']);
        }
        
        // Load the export service
        $exportService = new \App\Service\ReportExportService();
        
        // Parse report_data
        $reportData = json_decode($report->report_data, true) ?? [];
        
        try {
            $result = $exportService->export($report, $reportData, $format);
            
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
     * Export report from case directly (without creating report record)
     *
     * @param int $caseId Case ID
     * @param string $format Export format
     * @return \Cake\Http\Response Downloads file
     */
    public function exportFromCase($caseId = null, $format = 'pdf')
    {
        $user = $this->request->getAttribute('identity');
        $userId = $user->getIdentifier();
        
        if (!$caseId) {
            $this->Flash->error(__('Invalid case specified.'));
            return $this->redirect(['controller' => 'Cases', 'action' => 'index']);
        }

        // Verify the case is assigned to this scientist
        $caseAssignment = $this->fetchTable('CaseAssignments')->find()
            ->where([
                'case_id' => $caseId,
                'assigned_to' => $userId
            ])
            ->first();
            
        if (!$caseAssignment) {
            $this->Flash->error(__('You do not have access to this case.'));
            return $this->redirect(['controller' => 'Cases', 'action' => 'index']);
        }

        try {
            // Load case
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
            $this->Flash->error(__('Error generating report: {0}', $e->getMessage()));
            return $this->redirect(['controller' => 'Cases', 'action' => 'view', $caseId]);
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
        $user = $this->request->getAttribute('identity');
        $userId = $user->getIdentifier();
        
        // Get the report with case assignment verification
        $report = $this->Reports->find()
            ->matching('Cases.CaseAssignments', function ($q) use ($userId) {
                return $q->where(['CaseAssignments.assigned_to' => $userId]);
            })
            ->where(['Reports.id' => $id])
            ->first();
            
        if (!$report) {
            $this->Flash->error(__('Report not found or you do not have access to this case.'));
            return $this->redirect(['action' => 'index']);
        }
        
        if ($this->Reports->delete($report)) {
            $this->Flash->success(__('The report has been deleted.'));
        } else {
            $this->Flash->error(__('The report could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}