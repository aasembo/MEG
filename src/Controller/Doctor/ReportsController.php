<?php
declare(strict_types=1);

namespace App\Controller\Doctor;

use App\Controller\AppController;
use Cake\Http\Exception\NotFoundException;

/**
 * Reports Controller (Doctor)
 *
 * @property \App\Model\Table\ReportsTable $Reports
 */
class ReportsController extends AppController
{
    /**
     * Index method - List reports for cases assigned to the doctor
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $user = $this->request->getAttribute('identity');
        $userId = $user->getIdentifier();
        
        // Only show reports for cases assigned to this doctor
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
            ->matching('Cases.CaseAssignments', function ($q) use ($userId) {
                return $q->where(['CaseAssignments.assigned_to' => $userId]);
            })
            ->order(['Reports.case_id' => 'ASC', 'Reports.created' => 'ASC'])
            ->all();

        $this->set(compact('reports'));
    }

    /**
     * View method - View a single report (only if case is assigned to doctor)
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
            ->first();
            
        if (!$report) {
            $this->Flash->error(__('Report not found or you do not have access to this case.'));
            return $this->redirect(['action' => 'index']);
        }

        $this->set(compact('report'));
    }

    /**
     * Add method - Create a new doctor report for a case, potentially using existing scientist report as base
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

        // Verify the case is assigned to this doctor
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

        // Check if THIS doctor already has a report for this case
        $existingDoctorReport = $this->Reports->find()
            ->where([
                'case_id' => $caseId,
                'user_id' => $userId,
                'doctor_approval IS NOT' => null
            ])
            ->first();
            
        if ($existingDoctorReport) {
            // Redirect to edit this doctor's existing report
            return $this->redirect(['action' => 'edit', $existingDoctorReport->id]);
        }

        // Check for existing scientist report to use as base
        $scientistReport = $this->Reports->find()
            ->where([
                'case_id' => $caseId,
                'scientist_review IS NOT' => null
            ])
            ->first();

        // Load case with all related data
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
        
        // Default content from scientist report if available
        $reportContent = '';
        if ($scientistReport) {
            $scientistData = json_decode($scientistReport->report_data, true) ?? [];
            $reportContent = $scientistData['content'] ?? '';
        }
        
        if ($this->request->is('post')) {
            $data = $this->request->getData();
            
            // Structure the report data with doctor approval
            $reportDataStructure = [
                'content' => $data['report_content'] ?? $reportContent
            ];
            
            $data['report_data'] = json_encode($reportDataStructure);
            $data['case_id'] = $caseId;
            $data['hospital_id'] = $case->hospital_id;
            $data['status'] = 'approved'; // Doctor reports are automatically approved
            $data['user_id'] = $userId;
            
            // Add doctor approval data
            $data['doctor_approval'] = json_encode([
                'approved_by' => $userId,
                'approved_at' => date('Y-m-d H:i:s'),
                'notes' => $data['doctor_notes'] ?? ''
            ]);
            
            $report = $this->Reports->patchEntity($report, $data);
            
            if ($this->Reports->save($report)) {
                $this->Flash->success(__('The doctor report has been created successfully.'));
                return $this->redirect(['action' => 'view', $report->id]);
            } else {
                $this->Flash->error(__('The report could not be saved. Please, try again.'));
            }
        }
        
        $this->set(compact('report', 'case', 'reportContent', 'scientistReport'));
    }

    /**
     * Edit method
     *
     * @param int|null $id Report id.
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
            
            // Update doctor approval data
            $data['doctor_approval'] = json_encode([
                'approved_by' => $userId,
                'approved_at' => date('Y-m-d H:i:s'),
                'notes' => $data['doctor_notes'] ?? ''
            ]);
            
            $report = $this->Reports->patchEntity($report, $data);
            
            if ($this->Reports->save($report)) {
                $this->Flash->success(__('The report has been updated.'));
                return $this->redirect(['action' => 'view', $report->id]);
            } else {
                $this->Flash->error(__('The report could not be saved. Please, try again.'));
            }
        }
        
        // Parse existing report data
        $reportData = json_decode($report->report_data, true) ?? [];
        $reportContent = $reportData['content'] ?? '';
        
        $this->set(compact('report', 'reportContent'));
    }

    /**
     * Preview method - Show report in preview format
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

        // Verify the case is assigned to this doctor
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
            
            // Create temporary report entity for export
            $tempReport = $this->Reports->newEntity([
                'case_id' => $caseId,
                'hospital_id' => $case->hospital_id,
                'case' => $case
            ]);
            
            // Load the export service
            $exportService = new \App\Service\ReportExportService();
            
            $result = $exportService->exportFromCase($tempReport, $case, $format);
            
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