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

    /**
     * Download Case Report as PDF
     *
     * @param int|null $id Case id
     * @return \Cake\Http\Response|null
     */
    public function downloadReport($id = null)
    {
        $identity = $this->Authentication->getIdentity();
        $userId = $identity ? $identity->get('id') : null;
        
        $currentHospital = $this->request->getSession()->read('Hospital.current');

        if (!$currentHospital || !isset($currentHospital->id)) {
            $this->Flash->error(__('No hospital context found.'));
            return $this->redirect(array('action' => 'index'));
        }

        try {
            $case = $this->Cases->get($id, array(
                'contain' => array(
                    'PatientUsers' => array('Patients'),
                    'Users',
                    'Hospitals',
                    'Departments',
                    'Sedations',
                    'CurrentUsers',
                    'CurrentVersions' => array('Users'),
                    'CasesExamsProcedures' => array(
                        'ExamsProcedures' => array('Exams', 'Procedures'),
                        'Documents' // Include documents linked to procedures
                    ),
                    'CaseVersions' => array('Users'),
                    'CaseAssignments' => array('Users', 'AssignedToUsers'),
                    'Documents' // Include general case documents
                )
            ));

            // Verify hospital access
            if ($case->hospital_id !== $currentHospital->id) {
                throw new \Cake\Datasource\Exception\RecordNotFoundException(__('Case not found.'));
            }

        } catch (\Cake\Datasource\Exception\RecordNotFoundException $e) {
            $this->Flash->error(__('Case not found.'));
            return $this->redirect(array('action' => 'index'));
        }

        // Generate PDF report
        try {
            // Increase PCRE backtrack limit for large HTML content
            ini_set('pcre.backtrack_limit', '5000000');
            
            $mpdf = new \Mpdf\Mpdf(array(
                'mode' => 'utf-8',
                'format' => 'A4',
                'margin_left' => 10,
                'margin_right' => 10,
                'margin_top' => 15,
                'margin_bottom' => 15,
                'margin_header' => 10,
                'margin_footer' => 10
            ));

            // Set document properties
            $mpdf->SetTitle('Case Report #' . $case->id);
            $mpdf->SetAuthor($currentHospital->name);
            $mpdf->SetCreator('MEG System');

            // Prepare data for view using AI services if enabled
            $viewVars = $this->_prepareReportWithAI($case, $currentHospital, $userId);

            // Create a new View instance to render the template WITHOUT layout
            $viewBuilder = $this->viewBuilder();
            $viewBuilder->setTemplate('pdf/report');
            $viewBuilder->disableAutoLayout(); // Disable layout for PDF
            
            // Build the view with the current request
            $view = $viewBuilder->build($this->request);
            
            // Set view variables
            $view->set($viewVars);
            
            // Render to HTML (without layout)
            $html = $view->render();

            // Write HTML to PDF (handle large content by splitting into chunks if needed)
            $htmlSize = strlen($html);
            $maxChunkSize = 500000; // 500KB chunks
            
            if ($htmlSize > $maxChunkSize) {
                Log::debug("Large HTML content ({$htmlSize} bytes), splitting into chunks");
                
                // Split HTML by sections to avoid breaking tags
                // First write the header/styles
                preg_match('/(.*?<body[^>]*>)/is', $html, $headerMatch);
                if ($headerMatch) {
                    $mpdf->WriteHTML($headerMatch[1]);
                    $html = substr($html, strlen($headerMatch[1]));
                }
                
                // Split remaining content by section divs
                $sections = preg_split('/(<div class="section">)/i', $html, -1, PREG_SPLIT_DELIM_CAPTURE);
                
                $buffer = '';
                foreach ($sections as $section) {
                    $buffer .= $section;
                    
                    // Write chunk when it reaches a reasonable size
                    if (strlen($buffer) > $maxChunkSize) {
                        $mpdf->WriteHTML($buffer);
                        $buffer = '';
                    }
                }
                
                // Write any remaining content
                if (!empty($buffer)) {
                    $mpdf->WriteHTML($buffer);
                }
            } else {
                // Small enough to write in one go
                $mpdf->WriteHTML($html);
            }

            // Log activity
            $this->activityLogger->log(
                'report_downloaded',
                array(
                    'user_id' => $userId,
                    'request' => $this->request,
                    'event_data' => array(
                        'case_id' => $case->id,
                        'hospital_id' => $currentHospital->id
                    )
                )
            );

            // Output PDF for download
            $filename = 'Case_Report_' . $case->id . '_' . date('Ymd') . '.pdf';
            
            return $this->response
                ->withType('application/pdf')
                ->withHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
                ->withStringBody($mpdf->Output($filename, 'S'));

        } catch (\Exception $e) {
            Log::error('Error generating PDF report: ' . $e->getMessage());
            $this->Flash->error(__('Error generating PDF report: {0}', $e->getMessage()));
            return $this->redirect(array('action' => 'view', $id));
        }
    }

    /**
     * Prepare report data using AI services
     *
     * @param \App\Model\Entity\MedicalCase $case The case entity
     * @param object $hospital The hospital object
     * @param int|null $userId The current user ID
     * @return array View variables
     */
    private function _prepareReportWithAI($case, $hospital, $userId)
    {
        // Check if AI report generation is enabled
        $aiEnabled = env('OPENAI_ENABLED');
        $reportGenEnabled = env('OPENAI_REPORT_ENABLED');

        if (!$aiEnabled || !$reportGenEnabled) {
            // Fallback to traditional method
            return $this->_prepareReportData($case, $hospital, $userId);
        }

        try {
            // Step 1: Extract content from attached documents
            $documentContentService = new \App\Service\DocumentContentService();
            $documentContents = array();

            Log::debug('AI Report: Starting document extraction for case ' . $case->id);
            
            if (!empty($case->cases_exams_procedures)) {
                Log::debug('AI Report: Found ' . count($case->cases_exams_procedures) . ' procedures');
                
                foreach ($case->cases_exams_procedures as $cep) {
                    if (!empty($cep->documents)) {
                        Log::debug('AI Report: Procedure ' . $cep->id . ' has ' . count($cep->documents) . ' documents');
                        
                        foreach ($cep->documents as $document) {
                            try {
                                Log::debug('AI Report: Extracting document ' . $document->id . ' (' . $document->original_filename . ')');
                                $content = $documentContentService->extractContent($document);
                                
                                Log::debug('AI Report: Document ' . $document->id . ' extraction result - Success: ' . 
                                          ($content['success'] ? 'YES' : 'NO') . 
                                          ', Text length: ' . (isset($content['text']) ? strlen($content['text']) : 0));
                                
                                if (!$content['success'] && isset($content['error'])) {
                                    Log::warning('AI Report: Document ' . $document->id . ' extraction error: ' . $content['error']);
                                }
                                
                                $documentContents[$document->id] = array(
                                    'content' => $content,
                                    'procedure_id' => $cep->id,
                                    'document' => $document
                                );
                                Log::debug('AI Report: Successfully extracted document ' . $document->id);
                            } catch (\Exception $e) {
                                Log::warning('AI Report: Failed to extract document ' . $document->id . ': ' . $e->getMessage());
                            }
                        }
                    } else {
                        Log::debug('AI Report: Procedure ' . $cep->id . ' has no documents');
                    }
                }
            } else {
                Log::debug('AI Report: No procedures found for case');
            }
            
            Log::debug('AI Report: Total documents extracted: ' . count($documentContents));

            // Step 2: Prepare metadata for AI (de-identified)
            $metadata = $this->_prepareAiMetadata($case, $documentContents);

            // Step 3: Generate report structure using AI
            // Use standardized MEG report format
            $reportAssemblyService = new \App\Service\ReportAssemblyService();
            $reportData = $reportAssemblyService->assembleMEGReport($case, $documentContents);

            // Add hospital context
            $reportData['hospital'] = $hospital;
            $reportData['current_user_id'] = $userId;

            return $reportData;

        } catch (\Exception $e) {
            Log::error('AI report generation failed, falling back to traditional: ' . $e->getMessage());
            return $this->_prepareReportData($case, $hospital, $userId);
        }
    }

    /**
     * Prepare metadata for AI report generation (de-identified, HIPAA compliant)
     *
     * @param \App\Model\Entity\MedicalCase $case The case entity
     * @param array $documentContents Extracted document contents
     * @return array De-identified metadata
     */
    private function _prepareAiMetadata($case, array $documentContents)
    {
        // Get patient age category (not exact age)
        $ageCategory = 'adult';
        $patient = $case->patient_user;
        if ($patient && !empty($patient->patients)) {
            $patientDetails = $patient->patients[0];
            if ($patientDetails->dob) {
                $dob = new \DateTime($patientDetails->dob->format('Y-m-d'));
                $now = new \DateTime();
                $age = $dob->diff($now)->y;
                
                if ($age < 18) {
                    $ageCategory = 'pediatric';
                } elseif ($age >= 65) {
                    $ageCategory = 'geriatric';
                }
            }
        }

        // Get procedure types (no PHI)
        $procedureTypes = array();
        if (!empty($case->cases_exams_procedures)) {
            foreach ($case->cases_exams_procedures as $cep) {
                if (isset($cep->exams_procedure->procedure)) {
                    $procedureTypes[] = $cep->exams_procedure->procedure->name;
                }
            }
        }

        // Get symptoms/indications (generic categories only, not patient-specific text)
        $symptomCategories = $this->_categorizeSymptoms($case->symptoms ? $case->symptoms : '');

        // Determine report type
        $aiReportService = new \App\Service\AiReportGenerationService();
        $reportType = $aiReportService->determineReportType($procedureTypes);

        return array(
            'procedures' => $procedureTypes,
            'procedure_types' => $procedureTypes,
            'report_type' => $reportType,
            'age_category' => $ageCategory,
            'gender' => $patient->gender ? $patient->gender : 'unknown',
            'symptom_categories' => $symptomCategories,
            'sedation_type' => $case->sedation ? $case->sedation->name : 'none',
            'department' => $case->department ? $case->department->name : 'general',
            'document_count' => count($documentContents),
            'has_prior_studies' => false
        );
    }

    /**
     * Categorize symptoms into generic medical categories
     *
     * @param string $symptomsText Patient symptoms text
     * @return array Generic symptom categories
     */
    private function _categorizeSymptoms($symptomsText)
    {
        $categories = array();
        $text = strtolower($symptomsText);

        // Map keywords to categories (no patient-specific data sent to AI)
        if (preg_match('/seizure|epilep|convuls/i', $text)) {
            $categories[] = 'seizure_disorder';
        }
        if (preg_match('/headache|migraine|pain/i', $text)) {
            $categories[] = 'headache';
        }
        if (preg_match('/tumor|mass|lesion/i', $text)) {
            $categories[] = 'structural_abnormality';
        }
        if (preg_match('/memory|cognitive|dementia/i', $text)) {
            $categories[] = 'cognitive_concern';
        }

        return empty($categories) ? array('general_evaluation') : $categories;
    }

    /**
     * Prepare data for case report PDF (traditional fallback method)
     *
     * @param \App\Model\Entity\MedicalCase $case The case entity
     * @param object $hospital The hospital object
     * @param int|null $userId The current user ID
     * @return array View variables
     */
    private function _prepareReportData($case, $hospital, $userId)
    {
        // Get patient details
        $patient = $case->patient_user;
        $patientDetails = null;
        if ($patient && !empty($patient->patients)) {
            $patientDetails = $patient->patients[0];
        }

        // Patient basic info
        $patientFirstName = $patient ? $patient->first_name : 'N/A';
        $patientLastName = $patient ? $patient->last_name : 'N/A';
        $patientDob = $patientDetails && $patientDetails->dob 
            ? $patientDetails->dob->format('m/d/Y') 
            : 'N/A';
        $patientMrn = $patientDetails && $patientDetails->mrn 
            ? $patientDetails->mrn 
            : 'N/A';
        $patientFin = $patientDetails && $patientDetails->fin 
            ? $patientDetails->fin 
            : 'N/A';

        // Calculate age from DOB
        $age = 'N/A';
        if ($patientDetails && $patientDetails->dob) {
            $dob = new \DateTime($patientDetails->dob->format('Y-m-d'));
            $now = new \DateTime();
            $age = $dob->diff($now)->y;
        }

        // Gender
        $gender = $patient && $patient->gender ? $patient->gender : 'N/A';

        // Medications
        $medications = $patientDetails && $patientDetails->medications 
            ? $patientDetails->medications 
            : '';

        // Study details
        $studyDate = $case->date ? $case->date->format('m/d/Y') : 'N/A';
        $referringPhysician = $case->user 
            ? ($case->user->first_name . ' ' . $case->user->last_name) 
            : 'N/A';
        $megId = 'MEG-' . str_pad((string)$case->id, 6, '0', STR_PAD_LEFT);

        // Sedation text
        $sedationText = $case->sedation && $case->sedation->name 
            ? 'with ' . strtolower($case->sedation->name) 
            : 'without sedation';

        // Get procedures list
        $proceduresList = array();
        if (!empty($case->cases_exams_procedures)) {
            foreach ($case->cases_exams_procedures as $cep) {
                if (isset($cep->exams_procedure->exam) && isset($cep->exams_procedure->procedure)) {
                    $proceduresList[] = $cep->exams_procedure->exam->name . ' - ' . $cep->exams_procedure->procedure->name;
                }
            }
        }

        // Additional notes
        $additionalNotes = $case->notes ? $case->notes : '';

        return array(
            'case' => $case,
            'hospital' => $hospital,
            'patientFirstName' => $patientFirstName,
            'patientLastName' => $patientLastName,
            'patientDob' => $patientDob,
            'patientMrn' => $patientMrn,
            'patientFin' => $patientFin,
            'age' => $age,
            'gender' => $gender,
            'medications' => $medications,
            'studyDate' => $studyDate,
            'referringPhysician' => $referringPhysician,
            'megId' => $megId,
            'sedationText' => $sedationText,
            'proceduresList' => $proceduresList,
            'additionalNotes' => $additionalNotes,
            'current_user_id' => $userId
        );
    }
}
