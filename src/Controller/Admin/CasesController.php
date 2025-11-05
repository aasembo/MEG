<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AppController;
use Cake\Datasource\Exception\RecordNotFoundException;
use App\Lib\UserActivityLogger;
use App\Constants\SiteConstants;
use Cake\Log\Log;

/**
 * Admin Cases Controller
 *
 * Hospital administrators can view all cases for their hospital
 * but cannot create, edit, or delete cases
 *
 * @property \App\Model\Table\CasesTable $Cases
 */
class CasesController extends AppController
{
    /**
     * User Activity Logger instance
     */
    private $activityLogger;

    /**
     * Initialize method
     */
    public function initialize(): void
    {
        parent::initialize();
        $this->activityLogger = new UserActivityLogger();
        
        // Set admin layout for all actions
        $this->viewBuilder()->setLayout('admin');
    }

    /**
     * Index method - View all cases for hospital
     *
     * @return \Psr\Http\Message\ResponseInterface|null|void Renders view
     */
    public function index()
    {
        $identity = $this->Authentication->getIdentity();
        $userId = $identity ? $identity->get('id') : null;
        
        $currentHospital = $this->request->getSession()->read('Hospital.current');
        
        if (!$currentHospital || !isset($currentHospital->id)) {
            $this->Flash->error(__('No hospital context found. Please contact your administrator.'));
            return $this->redirect(array('prefix' => 'Admin', 'controller' => 'Dashboard', 'action' => 'index'));
        }

        // Get filter parameters
        $status = $this->request->getQuery('status');
        $status = $status ? $status : 'all';
        
        $priority = $this->request->getQuery('priority');
        $priority = $priority ? $priority : 'all';
        
        $search = $this->request->getQuery('search');

        // Build query
        $query = $this->Cases->find()
            ->contain(array(
                'Users', 
                'PatientUsers', 
                'CurrentUsers', 
                'Hospitals',
                'Departments',
                'Sedations',
                'CasesExamsProcedures' => array(
                    'ExamsProcedures' => array(
                        'Exams' => array('Modalities'),
                        'Procedures'
                    )
                )
            ))
            ->where(array('Cases.hospital_id' => $currentHospital->id));

        // Apply filters
        if ($status && $status !== 'all') {
            $query->where(array('Cases.status' => $status));
        }
        
        if ($priority && $priority !== 'all') {
            $query->where(array('Cases.priority' => $priority));
        }

        if ($search) {
            $searchConditions = array(
                'OR' => array(
                    'Users.first_name LIKE' => '%' . $search . '%',
                    'Users.last_name LIKE' => '%' . $search . '%',
                    'PatientUsers.first_name LIKE' => '%' . $search . '%',
                    'PatientUsers.last_name LIKE' => '%' . $search . '%',
                )
            );
            
            // Only search by ID if the search term is numeric
            if (is_numeric($search)) {
                $searchConditions['OR']['Cases.id'] = (int)$search;
            }
            
            $query->where($searchConditions);
        }

        // Hospital admin can see ALL cases for their hospital (not just own cases)
        // No additional where clause needed - already filtered by hospital_id

        $cases = $this->paginate($query->orderBy(array('Cases.created' => 'DESC')));

        // Get filter options
        $statusOptions = array(
            'all' => 'All Status',
            SiteConstants::CASE_STATUS_DRAFT => 'Draft',
            SiteConstants::CASE_STATUS_ASSIGNED => 'Assigned',
            SiteConstants::CASE_STATUS_IN_PROGRESS => 'In Progress',
            'review' => 'Under Review',
            SiteConstants::CASE_STATUS_COMPLETED => 'Completed',
            SiteConstants::CASE_STATUS_CANCELLED => 'Cancelled'
        );

        $priorityOptions = array(
            'all' => 'All Priorities',
            SiteConstants::PRIORITY_LOW => 'Low',
            SiteConstants::PRIORITY_MEDIUM => 'Medium',
            SiteConstants::PRIORITY_HIGH => 'High',
            SiteConstants::PRIORITY_URGENT => 'Urgent'
        );

        // Log activity
        $this->activityLogger->log(
            SiteConstants::EVENT_CASE_LIST_VIEWED,
            array(
                'user_id' => $userId,
                'request' => $this->request,
                'event_data' => array('hospital_id' => $currentHospital->id, 'filters' => compact('status', 'priority', 'search'))
            )
        );

        // Additional template variables
        $hospitalName = isset($currentHospital->name) ? $currentHospital->name : 'Unknown Hospital';

        $this->set(compact(
            'cases', 
            'statusOptions', 
            'priorityOptions', 
            'status', 
            'priority', 
            'search',
            'currentHospital',
            'hospitalName'
        ));
    }

    /**
     * View method - View individual case details
     *
     * @param string|null $id Case id.
     * @return \Psr\Http\Message\ResponseInterface|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $identity = $this->Authentication->getIdentity();
        $userId = $identity ? $identity->get('id') : null;
        
        $currentHospital = $this->request->getSession()->read('Hospital.current');
        
        if (!$currentHospital || !isset($currentHospital->id)) {
            $this->Flash->error(__('No hospital context found. Please contact your administrator.'));
            return $this->redirect(array('action' => 'index'));
        }

        try {
            $case = $this->Cases->get($id, array(
                'contain' => array(
                    'Users',
                    'Hospitals', 
                    'PatientUsers' => array('Patients'),
                    'CurrentUsers',
                    'CurrentVersions',
                    'Departments',
                    'Sedations',
                    'CaseVersions' => array('Users'),
                    'CaseAssignments' => array(
                        'Users' => array('Roles'),
                        'AssignedToUsers' => array('Roles')
                    ),
                    'CaseAudits' => array(
                        'ChangedByUsers' => array('Roles')
                    ),
                    'Documents' => array(
                        'Users',
                        'CasesExamsProcedures' => array(
                            'ExamsProcedures' => array('Exams', 'Procedures')
                        )
                    ),
                    'CasesExamsProcedures' => array(
                        'ExamsProcedures' => array(
                            'Exams' => array('Modalities', 'Departments'),
                            'Procedures'
                        ),
                        'Documents' => array('Users')
                    )
                )
            ));

            // Verify hospital access
            if ($case->hospital_id !== $currentHospital->id) {
                throw new RecordNotFoundException(__('Case not found.'));
            }

        } catch (RecordNotFoundException $e) {
            $this->Flash->error(__('Case not found.'));
            return $this->redirect(array('action' => 'index'));
        }

        // Log activity
        $this->activityLogger->log(
            SiteConstants::EVENT_CASE_VIEWED,
            array(
                'user_id' => $userId,
                'request' => $this->request,
                'event_data' => array('case_id' => $case->id, 'hospital_id' => $currentHospital->id)
            )
        );

        // Check if S3 is enabled for document preview handling
        $s3Service = new \App\Lib\S3DocumentService();
        $isS3Enabled = $s3Service->isS3Enabled();

        $this->set(compact('case', 'currentHospital', 'isS3Enabled'));
    }

    /**
     * View Document - View individual document details
     *
     * @param string|null $id Document id.
     * @return \Psr\Http\Message\ResponseInterface|null|void JSON response with document info
     */
    public function viewDocument($id = null)
    {
        $identity = $this->Authentication->getIdentity();
        $userId = $identity ? $identity->get('id') : null;
        
        $currentHospital = $this->request->getSession()->read('Hospital.current');
        
        if (!$currentHospital || !isset($currentHospital->id)) {
            $this->Flash->error(__('No hospital context found. Please contact your administrator.'));
            return $this->redirect(array('action' => 'index'));
        }

        try {
            $documentsTable = $this->fetchTable('Documents');
            $document = $documentsTable->get($id, array(
                'contain' => array('Cases')
            ));
            
            // Verify hospital access through case
            if ($document->case->hospital_id !== $currentHospital->id) {
                throw new RecordNotFoundException(__('Document not found.'));
            }

        } catch (RecordNotFoundException $e) {
            $this->Flash->error(__('Document not found.'));
            return $this->redirect(array('action' => 'index'));
        }

        // Get download URL from S3 (or local path)
        $s3Service = new \App\Lib\S3DocumentService();
        $fileUrl = $s3Service->getDownloadUrl($document->file_path);
        
        // Determine storage type
        $storageType = 'local';
        if ($s3Service->isS3Enabled() && strpos($document->file_path, 'uploads/') !== 0) {
            $storageType = 's3';
        }

        if ($fileUrl) {
            // Log activity
            $this->activityLogger->log(
                'document_viewed',
                array(
                    'user_id' => $userId,
                    'request' => $this->request,
                    'event_data' => array(
                        'case_id' => $document->case_id,
                        'document_id' => $document->id,
                        'document_type' => $document->document_type,
                        'hospital_id' => $currentHospital->id
                    )
                )
            );

            // Return JSON with document info for preview modal
            $this->response = $this->response->withType('application/json');
            $this->response = $this->response->withStringBody(json_encode(array(
                'success' => true,
                'url' => $fileUrl,
                'storage_type' => $storageType,
                'document' => array(
                    'id' => $document->id,
                    'filename' => $document->original_filename,
                    'type' => $document->file_type,
                    'size' => $document->file_size
                )
            )));
            
            return $this->response;
        } else {
            $this->response = $this->response->withType('application/json');
            $this->response = $this->response->withStringBody(json_encode(array(
                'success' => false,
                'error' => 'Failed to generate view link'
            )));
            return $this->response;
        }
    }

    /**
     * Download Document - Download a document file
     *
     * @param string|null $id Document id.
     * @return \Psr\Http\Message\ResponseInterface|null|void Redirects to download URL
     */
    public function downloadDocument($id = null)
    {
        $identity = $this->Authentication->getIdentity();
        $userId = $identity ? $identity->get('id') : null;
        
        $currentHospital = $this->request->getSession()->read('Hospital.current');
        
        if (!$currentHospital || !isset($currentHospital->id)) {
            $this->Flash->error(__('No hospital context found. Please contact your administrator.'));
            return $this->redirect(array('action' => 'index'));
        }

        try {
            $documentsTable = $this->fetchTable('Documents');
            $document = $documentsTable->get($id, array(
                'contain' => array('Cases')
            ));
            
            // Verify hospital access through case
            if ($document->case->hospital_id !== $currentHospital->id) {
                throw new RecordNotFoundException(__('Document not found.'));
            }

        } catch (RecordNotFoundException $e) {
            $this->Flash->error(__('Document not found.'));
            return $this->redirect(array('action' => 'index'));
        }

        // Get download URL from S3 with proper Content-Disposition header
        $s3Service = new \App\Lib\S3DocumentService();
        
        try {
            // Check if S3 is enabled
            if ($s3Service->isS3Enabled() && strpos($document->file_path, 'uploads/') !== 0) {
                // Get from S3 and stream
                $s3Client = $s3Service->getS3Client();
                $result = $s3Client->getObject(array(
                    'Bucket' => $s3Service->getBucket(),
                    'Key' => $document->file_path
                ));
                
                $content = $result['Body']->getContents();
                $contentType = $result['ContentType'] ? $result['ContentType'] : $document->file_type;
            } else {
                // Get from local storage
                $fullPath = WWW_ROOT . str_replace('/', DS, $document->file_path);
                if (!file_exists($fullPath)) {
                    $this->Flash->error(__('File not found.'));
                    return $this->redirect(array('action' => 'view', $document->case_id));
                }
                $content = file_get_contents($fullPath);
                $contentType = $document->file_type;
            }

            // Log activity
            $this->activityLogger->log(
                'document_downloaded',
                array(
                    'user_id' => $userId,
                    'request' => $this->request,
                    'event_data' => array(
                        'case_id' => $document->case_id,
                        'document_id' => $document->id,
                        'document_type' => $document->document_type,
                        'hospital_id' => $currentHospital->id
                    )
                )
            );

            // Return document with download headers
            return $this->response
                ->withType($contentType)
                ->withStringBody($content)
                ->withHeader('Content-Disposition', 'attachment; filename="' . $document->original_filename . '"')
                ->withHeader('Content-Length', (string)strlen($content))
                ->withHeader('Cache-Control', 'no-cache, must-revalidate')
                ->withHeader('Pragma', 'no-cache');

        } catch (\Exception $e) {
            Log::error('Failed to download document: ' . $e->getMessage());
            $this->Flash->error(__('Failed to download document. Please try again.'));
            return $this->redirect(array('action' => 'view', $document->case_id));
        }
    }

    /**
     * Proxy Document - Proxy document content for preview
     * Allows Office Online Viewer to access S3 documents
     *
     * @param string|null $id Document id.
     * @return \Psr\Http\Message\ResponseInterface|null|void Response with document content
     */
    public function proxyDocument($id = null)
    {
        $identity = $this->Authentication->getIdentity();
        $userId = $identity ? $identity->get('id') : null;
        
        $currentHospital = $this->request->getSession()->read('Hospital.current');
        
        if (!$currentHospital || !isset($currentHospital->id)) {
            return $this->response
                ->withStatus(403)
                ->withStringBody('Access denied');
        }

        try {
            $documentsTable = $this->fetchTable('Documents');
            $document = $documentsTable->get($id, array(
                'contain' => array('Cases')
            ));
            
            // Verify hospital access through case
            if ($document->case->hospital_id !== $currentHospital->id) {
                throw new RecordNotFoundException(__('Document not found.'));
            }

        } catch (RecordNotFoundException $e) {
            return $this->response
                ->withStatus(404)
                ->withStringBody('Document not found');
        }

        // Get document content from S3 or local storage
        $s3Service = new \App\Lib\S3DocumentService();
        
        try {
            // Check if S3 is enabled
            if ($s3Service->isS3Enabled() && strpos($document->file_path, 'uploads/') !== 0) {
                // Get from S3
                $s3Client = $s3Service->getS3Client();
                $result = $s3Client->getObject(array(
                    'Bucket' => env('AWS_S3_BUCKET'),
                    'Key' => $document->file_path
                ));
                
                $content = $result['Body']->getContents();
            } else {
                // Get from local storage
                $fullPath = WWW_ROOT . str_replace('/', DS, $document->file_path);
                if (!file_exists($fullPath)) {
                    return $this->response
                        ->withStatus(404)
                        ->withStringBody('File not found');
                }
                $content = file_get_contents($fullPath);
            }

            // Log activity
            $this->activityLogger->log(
                'document_proxy_accessed',
                array(
                    'user_id' => $userId,
                    'request' => $this->request,
                    'event_data' => array(
                        'case_id' => $document->case_id,
                        'document_id' => $document->id,
                        'document_type' => $document->document_type,
                        'hospital_id' => $currentHospital->id
                    )
                )
            );

            // Return document with proper headers
            return $this->response
                ->withType($document->file_type)
                ->withStringBody($content)
                ->withHeader('Content-Disposition', 'inline; filename="' . $document->original_filename . '"')
                ->withHeader('Content-Length', (string)strlen($content))
                ->withHeader('Cache-Control', 'no-cache, must-revalidate')
                ->withHeader('Pragma', 'no-cache');

        } catch (\Exception $e) {
            Log::error('Failed to proxy document: ' . $e->getMessage());
            return $this->response
                ->withStatus(500)
                ->withStringBody('Failed to load document');
        }
    }


}
