<?php
declare(strict_types=1);

namespace App\Controller\Doctor;

use App\Controller\AppController;
use Cake\Datasource\Exception\RecordNotFoundException;
use App\Lib\UserActivityLogger;
use App\Constants\SiteConstants;
use Cake\Log\Log;
use App\Service\DocumentContentService;
use App\Service\AiReportGenerationService;
use App\Service\ReportAssemblyService;
use App\Service\CaseStatusService;
use Cake\Core\Configure;

/**
 * Cases Controller (Doctor)
 *
 * Restrictions:
 * - Can view only cases assigned to them
 * - Can edit cases
 * - Can upload documents
 * - CANNOT create new cases
 * - CANNOT assign cases to others
 * - CANNOT delete cases
 *
 * @property \App\Model\Table\CasesTable $Cases
 * @property \App\Model\Table\CaseVersionsTable $CaseVersions
 * @property \App\Model\Table\CaseAuditsTable $CaseAudits
 * @property \App\Model\Table\UsersTable $Users
 */
class CasesController extends AppController
{
    /**
     * User Activity Logger instance
     */
    private $activityLogger;
    
    /**
     * Case Status Service instance
     */
    private $caseStatusService;

    /**
     * Initialize method
     */
    public function initialize(): void
    {
        parent::initialize();
        $this->activityLogger = new UserActivityLogger();
        $this->caseStatusService = new CaseStatusService();
        
        // Set doctor layout for all actions
        $this->viewBuilder()->setLayout('doctor');
    }

    /**
     * Index method - Shows only cases assigned to the current doctor
     *
     * @return \Psr\Http\Message\ResponseInterface|null|void Renders view
     */
    public function index()
    {
        $user = $this->getAuthUser();
        $currentHospital = $this->request->getSession()->read('Hospital.current');
        
        if (!$currentHospital || !isset($currentHospital->id)) {
            $this->Flash->error(__('No hospital context found. Please contact your administrator.'));
            return $this->redirect(array('prefix' => 'Doctor', 'controller' => 'Dashboard', 'action' => 'index'));
        }

        // Get filter parameters
        $status = $this->request->getQuery('status');
        $priority = $this->request->getQuery('priority');
        $search = $this->request->getQuery('search');

        // Build query - show cases assigned to current doctor OR where doctor was ever assigned
        $query = $this->Cases->find()
            ->contain(array(
                'Users', 
                'PatientUsers', 
                'CurrentUsers' => array('Roles'),
                'Hospitals',
                'Departments',
                'Sedations',
                'CasesExamsProcedures' => array(
                    'ExamsProcedures' => array(
                        'Exams' => array('Modalities'),
                        'Procedures'
                    )
                ),
                'CaseAssignments'
            ))
            ->where(array(
                'Cases.hospital_id' => $currentHospital->id,
                'OR' => array(
                    array('Cases.current_user_id' => $user->id),
                    function ($exp) use ($user) {
                        $caseAssignments = $this->fetchTable('CaseAssignments');
                        return $exp->exists(
                            $caseAssignments->find()
                                ->select(array('id'))
                                ->where(array(
                                    'CaseAssignments.case_id = Cases.id',
                                    'CaseAssignments.user_id' => $user->id
                                ))
                        );
                    }
                )
            ));

        // Apply filters - use role-based status for doctors
        if ($status && $status !== 'all') {
            $query->where(array('Cases.doctor_status' => $status));
        }
        
        if ($priority && $priority !== 'all') {
            $query->where(array('Cases.priority' => $priority));
        }

        if ($search) {
            $searchConditions = array(
                'OR' => array(
                    'Users.first_name LIKE' => '%' . $search . '%',
                    'Users.last_name LIKE' => '%' . $search . '%',
                )
            );
            
            // Only search by ID if the search term is numeric
            if (is_numeric($search)) {
                $searchConditions['OR']['Cases.id'] = (int)$search;
            }
            
            $query->where($searchConditions);
        }

        // Order by priority and creation date
        $query->orderBy(array(
            'CASE WHEN Cases.priority = "' . SiteConstants::PRIORITY_HIGH . '" THEN 1 '
            . 'WHEN Cases.priority = "' . SiteConstants::PRIORITY_MEDIUM . '" THEN 2 '
            . 'WHEN Cases.priority = "' . SiteConstants::PRIORITY_LOW . '" THEN 3 '
            . 'ELSE 4 END' => 'ASC',
            'Cases.created' => 'DESC'
        ));

        $cases = $this->paginate($query, array('limit' => 20));

        // Get counts for status badges - using role-based status for doctors
        // Doctors don't have 'draft' status - they start with 'assigned' when case is assigned to them
        $statusCounts = array();
        foreach (array(SiteConstants::CASE_STATUS_ASSIGNED, SiteConstants::CASE_STATUS_IN_PROGRESS, SiteConstants::CASE_STATUS_COMPLETED, SiteConstants::CASE_STATUS_CANCELLED) as $statusValue) {
            $count = $this->Cases->find()
                ->contain(array('CaseAssignments'))
                ->where(array(
                    'Cases.hospital_id' => $currentHospital->id,
                    'Cases.doctor_status' => $statusValue,
                    'OR' => array(
                        array('Cases.current_user_id' => $user->id),
                        function ($exp) use ($user) {
                            $caseAssignments = $this->fetchTable('CaseAssignments');
                            return $exp->exists(
                                $caseAssignments->find()
                                    ->select(array('id'))
                                    ->where(array(
                                        'CaseAssignments.case_id = Cases.id',
                                        'CaseAssignments.user_id' => $user->id
                                    ))
                            );
                        }
                    )
                ))
                ->count();
            $statusCounts[$statusValue] = $count;
        }

        $this->set(compact('cases', 'currentHospital', 'statusCounts'));
    }

    /**
     * View method
     *
     * @param string|null $id Case id.
     * @return \Psr\Http\Message\ResponseInterface|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $user = $this->getAuthUser();
        $currentHospital = $this->request->getSession()->read('Hospital.current');
        
        if (!$currentHospital || !isset($currentHospital->id)) {
            $this->Flash->error(__('No hospital context found. Please contact your administrator.'));
            return $this->redirect(array('action' => 'index'));
        }

        // Load full user entity with roles for "You" pattern in templates
        $usersTable = $this->fetchTable('Users');
        $userWithRoles = $usersTable->get($user->id, array('contain' => array('Roles')));

        try {
            $case = $this->Cases->get($id, array(
                'contain' => array(
                    'Users' => array('Roles'),
                    'PatientUsers' => array('Roles'),
                    'CurrentUsers' => array('Roles'),
                    'Hospitals',
                    'Departments',
                    'Sedations',
                    'CaseVersions' => array('Users'),
                    'CasesExamsProcedures' => array(
                        'ExamsProcedures' => array(
                            'Exams' => array('Modalities', 'Departments'),
                            'Procedures'
                        ),
                        'Documents' => array('Users')
                    ),
                    'CaseAssignments',
                    'CaseAssignments' => array(
                        'AssignedToUsers' => array('Roles'),
                        'Users' => array('Roles'),
                        'sort' => array('CaseAssignments.timestamp' => 'DESC')
                    ),
                    'CaseAudits' => array(
                        'Users',
                        'sort' => array('CaseAudits.timestamp' => 'DESC'),
                        'limit' => 50
                    ),
                    'Documents' => array(
                        'Users',
                        'sort' => array('Documents.created' => 'DESC')
                    )
                )
            ));

            // Check if doctor has access to this case
            $hasAssignment = false;
            
            // Check if user is current assignee
            if ($case->current_user_id === $user->id) {
                $hasAssignment = true;
            } else {
                // Check if user was ever assigned to this case
                foreach ($case->case_assignments as $assignment) {
                    if ($assignment->user_id === $user->id) {
                        $hasAssignment = true;
                        break;
                    }
                }
            }
            
            if (!$hasAssignment) {
                throw new RecordNotFoundException(__('Case not found or you do not have access to this case.'));
            }

        } catch (RecordNotFoundException $e) {
            $this->Flash->error(__('Case not found or you do not have access to this case.'));
            return $this->redirect(array('action' => 'index'));
        }

        // Check if S3 is enabled
        $isS3Enabled = Configure::read('S3.enabled', false);

        // Get case version history
        $caseVersionsTable = $this->fetchTable('CaseVersions');
        $caseVersions = $caseVersionsTable->find()
            ->contain(array('Users'))
            ->where(array('case_id' => $case->id))
            ->orderBy(array('version_number' => 'DESC'))
            ->all();

        $this->set(compact('case', 'caseVersions', 'currentHospital', 'isS3Enabled'));
        // Pass user with roles for role badge helper
        $this->set('user', $userWithRoles);
    }

    /**
     * Edit method - DISABLED for doctors
     * Doctors can view cases but cannot edit them
     *
     * @param string|null $id Case id.
     * @return \Psr\Http\Message\ResponseInterface|null Redirects to view action.
     */
    public function edit($id = null)
    {
        // Doctors cannot edit cases - redirect to view
        $this->Flash->error(__('Doctors do not have permission to edit cases. You can only view case details.'));
        return $this->redirect(array('action' => 'view', $id));
    }

    /**
     * Upload Document method - Doctors CAN upload documents
     *
     * @param string|null $id Case id.
     * @return \Psr\Http\Message\ResponseInterface|null|void Redirects on successful upload, renders view otherwise.
     */
    public function uploadDocument($id = null)
    {
        $user = $this->getAuthUser();
        $currentHospital = $this->request->getSession()->read('Hospital.current');
        
        if (!$currentHospital || !isset($currentHospital->id)) {
            $this->Flash->error(__('No hospital context found. Please contact your administrator.'));
            return $this->redirect(array('action' => 'index'));
        }

        try {
            $case = $this->Cases->get($id, array(
                'contain' => array('CaseAssignments')
            ));

            // Check if doctor has access to this case
            $hasAssignment = false;
            if ($case->current_user_id === $user->id) {
                $hasAssignment = true;
            } else {
                foreach ($case->case_assignments as $assignment) {
                    if ($assignment->user_id === $user->id) {
                        $hasAssignment = true;
                        break;
                    }
                }
            }
            
            if (!$hasAssignment) {
                throw new RecordNotFoundException(__('Case not found or you do not have access to this case.'));
            }

        } catch (RecordNotFoundException $e) {
            $this->Flash->error(__('Case not found or you do not have access to this case.'));
            return $this->redirect(array('action' => 'index'));
        }

        if ($this->request->is(array('post', 'put'))) {
            $data = $this->request->getData();
            
            // Validate uploaded file
            if (!isset($data['file']) || $data['file']->getError() !== UPLOAD_ERR_OK) {
                $this->Flash->error(__('Please select a valid file to upload.'));
                return $this->redirect(array('action' => 'view', $case->id));
            }

            $file = $data['file'];
            $fileName = $file->getClientFilename();
            $fileSize = $file->getSize();
            $fileType = $file->getClientMediaType();

            // Check file size (max 50MB)
            $maxSize = 50 * 1024 * 1024; // 50MB in bytes
            if ($fileSize > $maxSize) {
                $this->Flash->error(__('File size exceeds the maximum allowed size of 50MB.'));
                return $this->redirect(array('action' => 'view', $case->id));
            }

            // Allowed file types
            $allowedTypes = array(
                'application/pdf',
                'image/jpeg',
                'image/jpg',
                'image/png',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
            );

            if (!in_array($fileType, $allowedTypes)) {
                $this->Flash->error(__('Invalid file type. Allowed types: PDF, JPG, PNG, DOC, DOCX, XLS, XLSX.'));
                return $this->redirect(array('action' => 'view', $case->id));
            }

            // Generate unique filename
            $ext = pathinfo($fileName, PATHINFO_EXTENSION);
            $uniqueFileName = uniqid('doc_') . '_' . time() . '.' . $ext;

            // Determine storage path
            $isS3Enabled = Configure::read('S3.enabled', false);
            $filePath = '';

            if ($isS3Enabled) {
                // Upload to S3
                try {
                    $s3Client = \Aws\S3\S3Client::factory(array(
                        'version' => 'latest',
                        'region' => Configure::read('S3.region'),
                        'credentials' => array(
                            'key' => Configure::read('S3.key'),
                            'secret' => Configure::read('S3.secret')
                        )
                    ));

                    $bucket = Configure::read('S3.bucket');
                    $s3Path = 'documents/' . $currentHospital->id . '/' . $case->id . '/' . $uniqueFileName;

                    $result = $s3Client->putObject(array(
                        'Bucket' => $bucket,
                        'Key' => $s3Path,
                        'Body' => $file->getStream(),
                        'ContentType' => $fileType,
                        'ACL' => 'private'
                    ));

                    $filePath = $s3Path;
                    
                } catch (\Exception $e) {
                    Log::error('S3 upload error: ' . $e->getMessage());
                    $this->Flash->error(__('Failed to upload file to cloud storage.'));
                    return $this->redirect(array('action' => 'view', $case->id));
                }
            } else {
                // Upload to local filesystem
                $uploadPath = WWW_ROOT . 'files' . DS . 'documents' . DS . $currentHospital->id . DS . $case->id . DS;
                
                if (!file_exists($uploadPath)) {
                    mkdir($uploadPath, 0755, true);
                }

                $file->moveTo($uploadPath . $uniqueFileName);
                $filePath = 'documents/' . $currentHospital->id . '/' . $case->id . '/' . $uniqueFileName;
            }

            // Save document record
            $documentsTable = $this->fetchTable('Documents');
            $document = $documentsTable->newEntity(array(
                'case_id' => $case->id,
                'uploaded_by' => $user->id,
                'file_name' => $fileName,
                'file_path' => $filePath,
                'file_size' => $fileSize,
                'file_type' => $fileType,
                'description' => isset($data['description']) ? $data['description'] : '',
                'document_type' => isset($data['document_type']) ? $data['document_type'] : 'other',
                'is_s3' => $isS3Enabled ? 1 : 0
            ));

            if ($documentsTable->save($document)) {
                // Log activity
                $this->activityLogger->log(
                    SiteConstants::EVENT_DOCUMENT_UPLOADED,
                    array(
                        'user_id' => $user->id,
                        'request' => $this->request,
                        'event_data' => array(
                            'case_id' => $case->id,
                            'document_id' => $document->id,
                            'hospital_id' => $currentHospital->id
                        )
                    )
                );

                $this->Flash->success(__('Document has been uploaded successfully.'));
            } else {
                $this->Flash->error(__('Failed to save document record.'));
            }

            return $this->redirect(array('action' => 'view', $case->id));
        }

        $this->set(compact('case', 'currentHospital'));
    }

    /**
     * Download Document method
     *
     * @param string|null $id Document id.
     * @return \Psr\Http\Message\ResponseInterface|null Sends file download
     */
    public function downloadDocument($id = null)
    {
        $user = $this->getAuthUser();
        
        try {
            $documentsTable = $this->fetchTable('Documents');
            $document = $documentsTable->get($id, array(
                'contain' => array('Cases' => array('CaseAssignments'))
            ));

            // Check if doctor has access to this case
            $case = $document->case;
            $hasAssignment = false;
            
            if ($case->current_user_id === $user->id) {
                $hasAssignment = true;
            } else {
                foreach ($case->case_assignments as $assignment) {
                    if ($assignment->user_id === $user->id) {
                        $hasAssignment = true;
                        break;
                    }
                }
            }
            
            if (!$hasAssignment) {
                throw new RecordNotFoundException(__('Document not found or access denied.'));
            }

        } catch (RecordNotFoundException $e) {
            $this->Flash->error(__('Document not found or access denied.'));
            return $this->redirect(array('action' => 'index'));
        }

        // Check if S3 or local storage
        if ($document->is_s3) {
            // Download from S3
            try {
                $s3Client = \Aws\S3\S3Client::factory(array(
                    'version' => 'latest',
                    'region' => Configure::read('S3.region'),
                    'credentials' => array(
                        'key' => Configure::read('S3.key'),
                        'secret' => Configure::read('S3.secret')
                    )
                ));

                $bucket = Configure::read('S3.bucket');
                $cmd = $s3Client->getCommand('GetObject', array(
                    'Bucket' => $bucket,
                    'Key' => $document->file_path,
                    'ResponseContentDisposition' => 'attachment; filename="' . $document->file_name . '"'
                ));

                $request = $s3Client->createPresignedRequest($cmd, '+5 minutes');
                $presignedUrl = (string) $request->getUri();

                return $this->redirect($presignedUrl);
                
            } catch (\Exception $e) {
                Log::error('S3 download error: ' . $e->getMessage());
                $this->Flash->error(__('Failed to download file from cloud storage.'));
                return $this->redirect(array('action' => 'view', $document->case_id));
            }
        } else {
            // Download from local storage
            $filePath = WWW_ROOT . 'files' . DS . $document->file_path;
            
            if (!file_exists($filePath)) {
                $this->Flash->error(__('File not found.'));
                return $this->redirect(array('action' => 'view', $document->case_id));
            }

            $this->response = $this->response->withFile(
                $filePath,
                array('download' => true, 'name' => $document->file_name)
            );
            
            return $this->response;
        }
    }

    /**
     * Download Report method
     *
     * @param string|null $id Case id.
     * @return \Psr\Http\Message\ResponseInterface|null Sends PDF download
     */
    public function downloadReport($id = null)
    {
        $user = $this->getAuthUser();
        $currentHospital = $this->request->getSession()->read('Hospital.current');
        
        try {
            $case = $this->Cases->get($id, array(
                'contain' => array(
                    'Users',
                    'PatientUsers',
                    'Hospitals',
                    'Departments',
                    'Sedations',
                    'CaseVersions' => array('Users'),
                    'CasesExamsProcedures' => array(
                        'ExamsProcedures' => array(
                            'Exams' => array('Modalities', 'Departments'),
                            'Procedures'
                        ),
                        'Documents'
                    ),
                    'Documents' => array('Users'),
                    'CaseAssignments' => array(
                        'AssignedToUsers',
                        'Users'
                    )
                )
            ));

            // Check if doctor has access to this case
            $hasAssignment = false;
            if ($case->current_user_id === $user->id) {
                $hasAssignment = true;
            } else {
                foreach ($case->case_assignments as $assignment) {
                    if ($assignment->user_id === $user->id) {
                        $hasAssignment = true;
                        break;
                    }
                }
            }
            
            if (!$hasAssignment) {
                throw new RecordNotFoundException(__('Case not found or access denied.'));
            }

        } catch (RecordNotFoundException $e) {
            $this->Flash->error(__('Case not found or access denied.'));
            return $this->redirect(array('action' => 'index'));
        }

        // Generate PDF report
        $reportAssemblyService = new ReportAssemblyService();
        $pdfContent = $reportAssemblyService->generatePdfReport($case, $currentHospital);

        if (!$pdfContent) {
            $this->Flash->error(__('Failed to generate report.'));
            return $this->redirect(array('action' => 'view', $id));
        }

        // Send PDF as download
        $this->response = $this->response
            ->withType('application/pdf')
            ->withStringBody($pdfContent)
            ->withDownload('case_report_' . $case->id . '.pdf');

        return $this->response;
    }

    /**
     * Add method - DISABLED for doctors
     * Doctors cannot create new cases
     *
     * @return \Psr\Http\Message\ResponseInterface|null Redirects with error
     */
    public function add()
    {
        $this->Flash->error(__('Doctors do not have permission to create new cases.'));
        return $this->redirect(array('action' => 'index'));
    }

    /**
     * Delete method - DISABLED for doctors
     * Doctors cannot delete cases
     *
     * @param string|null $id Case id.
     * @return \Psr\Http\Message\ResponseInterface|null Redirects with error
     */
    public function delete($id = null)
    {
        $this->Flash->error(__('Doctors do not have permission to delete cases.'));
        return $this->redirect(array('action' => 'index'));
    }

    /**
     * Assign method - DISABLED for doctors
     * Doctors cannot assign cases to others
     *
     * @param string|null $id Case id.
     * @return \Psr\Http\Message\ResponseInterface|null Redirects with error
     */
    public function assign($id = null)
    {
        $this->Flash->error(__('Doctors do not have permission to assign cases to others.'));
        return $this->redirect(array('action' => 'view', $id));
    }

    /**
     * Get authenticated user
     *
     * @return \App\Model\Entity\User Current authenticated user
     */
    private function getAuthUser()
    {
        $identity = $this->request->getAttribute('identity');
        return $identity->getOriginalData();
    }
}
