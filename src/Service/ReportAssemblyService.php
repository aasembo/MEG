<?php
declare(strict_types=1);

namespace App\Service;

use Cake\Log\Log;

/**
 * Report Assembly Service
 * 
 * Combines AI-generated structure, document contents, and case data
 * into a complete report ready for PDF generation
 */
class ReportAssemblyService
{
    /**
     * Assemble report using the standardized MEG format
     * 
     * @param object $case Case entity with all associations
     * @param array $documentContents Extracted document contents
     * @return array Complete report data for view
     */
    public function assembleMEGReport($case, array $documentContents): array
    {
        try {
            // Determine report title from document content or use default
            $reportTitle = $this->determineReportTitle($documentContents);
            
            // Build standardized sections
            $sections = [];
            
            // Section 1: Patient History (conditional - only if history exists)
            $historySection = $this->buildPatientHistorySection($case);
            if ($historySection) {
                $sections[] = $historySection;
            }
            
            // Section 2: MEG Recordings (always included)
            $sections[] = $this->buildMEGRecordingsSection($case);
            
            // Section 3: Technical Description of Procedures (conditional - only if procedures exist)
            $proceduresSection = $this->buildTechnicalProceduresSection($case);
            if ($proceduresSection) {
                $sections[] = $proceduresSection;
            }
            
            // Section 4: Findings (from document summaries)
            $sections[] = $this->buildFindingsSection($case, $documentContents);
            
            // Section 5: Reference Documents (images only)
            $referenceSection = $this->buildReferenceDocumentsSection($case, $documentContents);
            if ($referenceSection) {
                $sections[] = $referenceSection;
            }
            
            // Section 6: Impressions (conditional - only if doctor assigned)
            $impressionsSection = $this->buildImpressionsSection($case);
            if ($impressionsSection) {
                $sections[] = $impressionsSection;
            }

            // Extract patient and case data for template variables
            $patientData = $this->extractPatientData($case);
            $caseData = $this->extractCaseData($case);

            // Prepare complete report data
            $reportData = array_merge(
                $patientData,
                $caseData,
                [
                    'report_name' => $reportTitle,
                    'report_type' => 'MEG_CLINICAL',
                    'formatting' => $this->getDefaultFormatting(),
                    'sections' => $sections,
                    'case' => $case,
                ]
            );

            return $reportData;

        } catch (\Exception $e) {
            Log::error('MEG report assembly failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Determine report title from document content
     *
     * @param array $documentContents Document contents
     * @return string Report title
     */
    private function determineReportTitle(array $documentContents): string
    {
        // Try to extract title from document summaries
        foreach ($documentContents as $docData) {
            if (isset($docData['content']['analysis']['report_type'])) {
                return h($docData['content']['analysis']['report_type']);
            }
        }
        
        // Use default MEG title
        return 'Magnetoencephalography Report (MEG)';
    }

    /**
     * Build Section 1: Patient Information
     *
     * @param object $case Case entity
     * @return array Section data
     */
    private function buildPatientInformationSection($case): array
    {
        $patient = $case->patient_user ?? null;
        $patientDetails = null;
        
        if ($patient && !empty($patient->patients)) {
            $patientDetails = $patient->patients[0];
        }

        // Format MEG ID with 6-character padding using X
        $megId = 'case_' . str_pad((string)$case->id, 6, 'X', STR_PAD_LEFT);

        // Get referring physician
        $referringPhysician = 'N/A';
        if ($case->user) {
            $referringPhysician = $case->user->first_name . ' ' . $case->user->last_name;
        }

        $content = '<table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">';
        $content .= '<tr><td style="padding: 5px; width: 40%;"><strong>Name:</strong></td>';
        $content .= '<td style="padding: 5px;">' . h($patient->last_name ?? 'N/A') . ', ' . h($patient->first_name ?? 'N/A') . '</td></tr>';
        
        $content .= '<tr><td style="padding: 5px;"><strong>Date of Birth:</strong></td>';
        $content .= '<td style="padding: 5px;">' . ($patientDetails && $patientDetails->dob ? $patientDetails->dob->format('m/d/Y') : 'N/A') . '</td></tr>';
        
        $content .= '<tr><td style="padding: 5px;"><strong>MRN:</strong></td>';
        $content .= '<td style="padding: 5px;">' . h($patientDetails->mrn ?? 'N/A') . ' &nbsp;&nbsp;&nbsp; <strong>FIN:</strong> ' . h($patientDetails->fin ?? 'N/A') . '</td></tr>';
        
        $content .= '<tr><td style="padding: 5px;"><strong>Date of Study:</strong></td>';
        $content .= '<td style="padding: 5px;">' . ($case->date ? $case->date->format('m/d/Y') : 'N/A') . '</td></tr>';
        
        $content .= '<tr><td style="padding: 5px;"><strong>Referring Physician:</strong></td>';
        $content .= '<td style="padding: 5px;">' . h($referringPhysician) . '</td></tr>';
        
        $content .= '<tr><td style="padding: 5px;"><strong>MEG ID:</strong></td>';
        $content .= '<td style="padding: 5px;">' . h($megId) . '</td></tr>';
        $content .= '</table>';

        return [
            'title' => 'Patient Information',
            'required' => true,
            'content' => $content,
            'subsections' => [],
        ];
    }

    /**
     * Build Section 2: Patient History (conditional)
     *
     * @param object $case Case entity
     * @return array|null Section data or null if no history
     */
    private function buildPatientHistorySection($case): ?array
    {
        $hasHistory = false;
        $content = '';

        // Check for symptoms/clinical indication
        if (!empty($case->symptoms)) {
            $hasHistory = true;
            $content .= '<p><strong>Clinical Indication:</strong> ' . h($case->symptoms) . '</p>';
        }

        // Check for medical history from patient details
        $patient = $case->patient_user ?? null;
        if ($patient && !empty($patient->patients)) {
            $patientDetails = $patient->patients[0];
            if (!empty($patientDetails->medical_history)) {
                $hasHistory = true;
                $content .= '<p>' . nl2br(h($patientDetails->medical_history)) . '</p>';
            }
        }

        // Check for medications
        if ($patient && !empty($patient->patients)) {
            $patientDetails = $patient->patients[0];
            if (!empty($patientDetails->medications)) {
                $hasHistory = true;
                $content .= '<p><strong>Current Medications:</strong> ' . nl2br(h($patientDetails->medications)) . '</p>';
            }
        }

        // Only return section if we have history
        if (!$hasHistory) {
            return null;
        }

        return [
            'title' => 'Patient History',
            'required' => false,
            'content' => $content,
            'subsections' => [],
        ];
    }

    /**
     * Build Section 2: MEG Recordings (always included)
     *
     * @param object $case Case entity
     * @return array Section data
     */
    private function buildMEGRecordingsSection($case): array
    {
        $content = '';
        
        // Determine sedation text
        $sedationText = 'without';
        if (!empty($case->sedation)) {
            $sedationText = 'with';
        }
        
        // Use case notes as the description if available
        if (!empty($case->notes)) {
            $content .= '<p>' . nl2br(h($case->notes)) . '</p>';
            // Add sedation information
            $content .= '<p>This study was performed <strong>' . $sedationText . '</strong> sedation.</p>';
        } else {
            // Fallback to default description if no notes
            $content .= '<p>Magnetoencephalography (MEG) recording and magnetic source imaging (MSI) were requested ';
            $content .= 'as part of a presurgical evaluation to noninvasively localize epileptiform discharges and map ';
            $content .= 'functional cortical areas. This study was performed <strong>' . $sedationText . '</strong> sedation.</p>';
        }

        // Add procedures list
        $content .= '<p><strong>The following procedures were performed:</strong></p>';
        
        if (!empty($case->cases_exams_procedures)) {
            $content .= '<ul>';
            foreach ($case->cases_exams_procedures as $cep) {
                $examName = $cep->exams_procedure->exam->name ?? 'Unknown Exam';
                $modalityName = $cep->exams_procedure->exam->modality->name ?? null;
                $procedureName = $cep->exams_procedure->procedure->name ?? 'Unknown Procedure';
                
                // Format: Exam / Modality / Procedure (skip modality if not set)
                $displayText = $examName;
                if ($modalityName) {
                    $displayText .= ' / ' . $modalityName;
                }
                $displayText .= ' / ' . $procedureName;
                
                $content .= '<li>' . h($displayText) . '</li>';
            }
            $content .= '</ul>';
        } else {
            $content .= '<p>No procedures recorded.</p>';
        }

        return [
            'title' => 'MEG Recordings',
            'required' => true,
            'content' => $content,
            'subsections' => [],
        ];
    }

    /**
     * Build Section 3: Technical Description of Procedures (conditional)
     *
     * @param object $case Case entity
     * @return array|null Section data or null if no procedures
     */
    private function buildTechnicalProceduresSection($case): ?array
    {
        if (empty($case->cases_exams_procedures)) {
            return null;
        }

        $content = '';

        foreach ($case->cases_exams_procedures as $cep) {
            $examName = $cep->exams_procedure->exam->name ?? 'Unknown Exam';
            $modalityName = $cep->exams_procedure->exam->modality->name ?? null;
            $procedureName = $cep->exams_procedure->procedure->name ?? 'Unknown Procedure';
            $procedureDescription = $cep->exams_procedure->procedure->description ?? '';
            
            // Only include completed procedures
            if ($cep->status !== 'completed') {
                continue;
            }

            // Format: Exam / Modality / Procedure (skip modality if not set)
            $displayTitle = $examName;
            if ($modalityName) {
                $displayTitle .= ' / ' . $modalityName;
            }
            $displayTitle .= ' / ' . $procedureName;

            $content .= '<h4 style="margin-top: 15px; margin-bottom: 10px; font-size: 12pt;">' . h($displayTitle) . '</h4>';
            
            if (!empty($procedureDescription)) {
                $content .= '<p>' . nl2br(h($procedureDescription)) . '</p>';
            }
            
            // Add any procedure-specific notes
            if (!empty($cep->notes)) {
                $content .= '<p>' . nl2br(h($cep->notes)) . '</p>';
            }
        }

        // If no completed procedures, return null
        if (empty($content)) {
            return null;
        }

        return [
            'title' => 'Technical Description of Procedures',
            'required' => false,
            'content' => $content,
            'subsections' => [],
        ];
    }

    /**
     * Build Section 4: Findings
     *
     * @param object $case Case entity
     * @param array $documentContents Document contents
     * @return array Section data
     */
    private function buildFindingsSection($case, array $documentContents): array
    {
        $content = '';

        // Use document summaries/analysis for findings
        if (!empty($documentContents)) {
            foreach ($documentContents as $docData) {
                $analysis = $docData['content']['analysis'] ?? null;
                
                if (!$analysis) {
                    continue;
                }

                // Add summary
                if (!empty($analysis['summary'])) {
                    $content .= '<p>' . $this->formatTextForPdf(h($analysis['summary'])) . '</p>';
                }

                // Add key findings
                if (!empty($analysis['findings'])) {
                    $content .= '<p><strong>Key Findings:</strong></p>';
                    $content .= '<ul>';
                    foreach ($analysis['findings'] as $finding) {
                        $content .= '<li>' . $this->formatTextForPdf(h($finding)) . '</li>';
                    }
                    $content .= '</ul>';
                }
            }
        }

        // If no findings from documents, add placeholder
        if (empty($content)) {
            $content = '<p>Detailed findings will be provided upon completion of analysis.</p>';
        }

        return [
            'title' => 'Findings',
            'required' => true,
            'content' => $content,
            'subsections' => [],
        ];
    }

    /**
     * Build Section 5: Reference Documents (images only, conditional)
     *
     * @param object $case Case entity
     * @param array $documentContents Document contents (not used, kept for compatibility)
     * @return array|null Section data or null if no image documents
     */
    private function buildReferenceDocumentsSection($case, array $documentContents): ?array
    {
        $content = '';
        $hasImages = false;

        // Get all image documents from case procedures
        if (!empty($case->cases_exams_procedures)) {
            foreach ($case->cases_exams_procedures as $cep) {
                if (empty($cep->documents)) {
                    continue;
                }
                
                foreach ($cep->documents as $document) {
                    // Only include image documents
                    if (!$this->isImageDocument($document)) {
                        continue;
                    }

                    $hasImages = true;
                    
                    // Get exam/modality/procedure name from the procedure link
                    $examName = $cep->exams_procedure->exam->name ?? 'Unknown Exam';
                    $modalityName = $cep->exams_procedure->exam->modality->name ?? null;
                    $procedureName = $cep->exams_procedure->procedure->name ?? 'Unknown Procedure';
                    
                    // Format: Exam / Modality / Procedure (skip modality if not set)
                    $displayName = $examName;
                    if ($modalityName) {
                        $displayName .= ' / ' . $modalityName;
                    }
                    $displayName .= ' / ' . $procedureName;

                    // Use table-based layout for stronger page break control in mPDF
                    // Tables have better page-break-inside support than divs
                    $content .= '<table style="width: 100%; page-break-inside: avoid; margin-bottom: 20px;">';
                    $content .= '<tr><td>';
                    
                    // Add heading for the exam/procedure
                    $content .= '<h4 style="margin-top: 20px; margin-bottom: 10px; font-size: 11pt;">' . h($displayName) . '</h4>';
                    
                    // Embed the actual image
                    $content .= $this->embedImage($document);
                    
                    $content .= '</td></tr>';
                    $content .= '</table>';
                }
            }
        }

        // Only return section if we have image documents
        if (!$hasImages) {
            return null;
        }

        return [
            'title' => 'Reference Documents',
            'required' => false,
            'content' => $content,
            'subsections' => [],
        ];
    }

    /**
     * Build Section 6: Impressions (conditional - only if doctor assigned)
     *
     * @param object $case Case entity
     * @return array|null Section data or null if no doctor assigned
     */
    private function buildImpressionsSection($case): ?array
    {
        // Check if a doctor/scientist is assigned
        $assignedDoctor = null;
        
        if (!empty($case->case_assignments)) {
            foreach ($case->case_assignments as $assignment) {
                if (isset($assignment->assigned_to_user->role->type) && 
                    in_array($assignment->assigned_to_user->role->type, ['doctor', 'scientist'])) {
                    $assignedDoctor = $assignment->assigned_to_user;
                    break;
                }
            }
        }

        // No doctor assigned, skip this section
        if (!$assignedDoctor) {
            return null;
        }

        // Build impressions content with signature block
        $content = '<p>Pending final interpretation and clinical correlation.</p>';
        $content .= '<div style="margin-top: 30px; border-top: 1px solid #000; padding-top: 15px;">';
        $content .= '<p><strong>' . h($assignedDoctor->first_name . ' ' . $assignedDoctor->last_name) . ', MD</strong></p>';
        $content .= '<p style="margin: 5px 0;">Assistant Professor of Neurology, Neurosurgery, and Pediatrics</p>';
        $content .= '<p style="margin: 5px 0;">Medical Director of the Magnetoencephalography Lab (MEG)</p>';
        $content .= '<p style="margin: 5px 0;">The University of Texas Dell Medical School</p>';
        $content .= '<p style="margin: 5px 0;">Dell Children\'s Medical Center</p>';
        $content .= '</div>';

        return [
            'title' => 'Impressions',
            'required' => false,
            'content' => $content,
            'subsections' => [],
        ];
    }

    /**
     * Assemble complete report from all components
     *
     * @param object $case Case entity with all associations
     * @param array $structure Report structure from AI
     * @param array $documentContents Extracted document contents
     * @return array Complete report data for view
     */
    public function assembleReport($case, array $structure, array $documentContents): array
    {
        try { die;
            // Get basic patient and case info
            $patientData = $this->extractPatientData($case);
            $caseData = $this->extractCaseData($case);
            
            // Build sections based on AI structure
            $sections = $this->buildSections($structure['sections'] ?? [], $case, $documentContents);

            // Prepare complete report data
            $reportData = array_merge(
                [
                    'report_name' => $structure['report_name'] ?? 'Medical Report',
                    'report_type' => $structure['report_type'] ?? 'CLINICAL',
                    'formatting' => $structure['formatting'] ?? $this->getDefaultFormatting(),
                    'sections' => $sections,
                ],
                $patientData,
                $caseData
            );

            return $reportData;

        } catch (\Exception $e) {
            Log::error('Report assembly failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Extract patient data (de-identified for display)
     *
     * @param object $case Case entity
     * @return array Patient data
     */
    private function extractPatientData($case): array
    {
        $patient = $case->patient_user ?? null;
        $patientDetails = null;
        
        if ($patient && !empty($patient->patients)) {
            $patientDetails = $patient->patients[0];
        }

        return [
            'patientFirstName' => $patient->first_name ?? 'N/A',
            'patientLastName' => $patient->last_name ?? 'N/A',
            'patientDob' => $patientDetails && $patientDetails->dob 
                ? $patientDetails->dob->format('m/d/Y') 
                : 'N/A',
            'patientMrn' => $patientDetails->mrn ?? 'N/A',
            'patientFin' => $patientDetails->fin ?? 'N/A',
            'age' => $this->calculateAge($patientDetails->dob ?? null),
            'gender' => $patient->gender ?? 'N/A',
            'medications' => $patientDetails->medications ?? '',
        ];
    }

    /**
     * Extract case data
     *
     * @param object $case Case entity
     * @return array Case data
     */
    private function extractCaseData($case): array
    {
        return [
            'case' => $case,
            'case_id' => $case->id,
            'studyDate' => $case->date ? $case->date->format('m/d/Y') : 'N/A',
            'symptoms' => $case->symptoms ?? '',
            'additionalNotes' => $case->notes ?? '',
            'department' => $case->department->name ?? 'N/A',
            'sedationText' => $this->formatSedation($case->sedation ?? null),
            'priority' => ucfirst($case->priority ?? 'normal'),
            'status' => ucfirst(str_replace('_', ' ', $case->status ?? 'draft')),
            'referringPhysician' => $this->getReferringPhysician($case),
            'megId' => 'MEG_' . str_pad((string)$case->id, 6, 'X', STR_PAD_LEFT),
            'hospital' => $case->hospital ?? null,
        ];
    }

    /**
     * Build sections based on AI structure
     *
     * @param array $sectionDefs Section definitions from AI
     * @param object $case Case entity
     * @param array $documentContents Document contents
     * @return array Built sections
     */
    private function buildSections(array $sectionDefs, $case, array $documentContents): array
    {
        $sections = [];

        foreach ($sectionDefs as $def) {
            $section = [
                'title' => $def['title'] ?? 'Untitled Section',
                'required' => $def['required'] ?? false,
                'content' => '',
                'subsections' => [],
            ];

            // Build content based on content_type
            if (isset($def['content_type'])) {
                $section['content'] = $this->buildSectionContent(
                    $def['content_type'],
                    $case,
                    $documentContents,
                    $def
                );
            }

            // Build subsections
            if (!empty($def['subsections'])) {
                $section['subsections'] = $this->buildSubsections(
                    $def['subsections'],
                    $case,
                    $documentContents
                );
            }

            $sections[] = $section;
        }

        return $sections;
    }

    /**
     * Build section content based on type
     *
     * @param string $contentType Type of content
     * @param object $case Case entity
     * @param array $documentContents Document contents
     * @param array $def Section definition
     * @return string Built content
     */
    private function buildSectionContent(
        string $contentType, 
        $case, 
        array $documentContents,
        array $def
    ): string {
        switch ($contentType) {
            case 'symptoms_and_history':
                return $this->buildSymptomsSection($case);

            case 'procedure_list':
                return $this->buildProcedureList($case);

            case 'procedure_findings':
                return $this->buildProcedureFindings($case, $documentContents);

            case 'document_summaries':
                return $this->buildDocumentSummaries($documentContents);

            case 'conclusions':
                return $this->buildConclusions($case);

            default:
                return '';
        }
    }

    /**
     * Build symptoms and history section
     *
     * @param object $case Case entity
     * @return string HTML content
     */
    private function buildSymptomsSection($case): string
    {
        $age = $this->calculateAge($case->patient_user->patients[0]->dob ?? null);
        $gender = $case->patient_user->gender ?? 'patient';
        
        $html = "<p>{$age}-year-old {$gender}.</p>";
        
        if (!empty($case->symptoms)) {
            $html .= "<p><strong>Clinical Indication:</strong> " . h($case->symptoms) . "</p>";
        }

        return $html;
    }

    /**
     * Build procedure list
     *
     * @param object $case Case entity
     * @return string HTML content
     */
    private function buildProcedureList($case): string
    {
        if (empty($case->cases_exams_procedures)) {
            return '<p>No procedures documented.</p>';
        }

        $html = '<ul>';
        foreach ($case->cases_exams_procedures as $cep) {
            if (isset($cep->exams_procedure->procedure)) {
                $html .= '<li>' . h($cep->exams_procedure->procedure->name) . '</li>';
            }
        }
        $html .= '</ul>';

        return $html;
    }

    /**
     * Build procedure findings section
     *
     * @param object $case Case entity
     * @param array $documentContents Document contents
     * @return string HTML content
     */
    private function buildProcedureFindings($case, array $documentContents): string
    {
        $html = '';

        foreach ($case->cases_exams_procedures as $cep) {
            $procedureName = $cep->exams_procedure->procedure->name ?? 'Unknown Procedure';
            $html .= "<h4>{$procedureName}</h4>";

            // Add procedure-specific findings from documents
            $findings = $this->findProcedureDocuments($cep->id, $documentContents);
            
            if (!empty($findings)) {
                $html .= $findings;
            } else {
                $html .= "<p>Status: " . ucfirst(str_replace('_', ' ', $cep->status)) . "</p>";
                if ($cep->notes) {
                    $html .= "<p>" . nl2br(h($cep->notes)) . "</p>";
                }
            }
        }

        return $html;
    }

    /**
     * Build document summaries section
     *
     * @param array $documentContents Document contents
     * @return string HTML content
     */
    private function buildDocumentSummaries(array $documentContents): string
    {
        if (empty($documentContents)) {
            return '<p>No supporting documents attached.</p>';
        }

        $html = '';
        foreach ($documentContents as $docId => $docData) {
            $content = $docData['content'] ?? [];
            $document = $docData['document'] ?? null;
            
            if (!$document) {
                continue;
            }
            
            // Check if we have meaningful content
            $hasAnalysis = isset($content['analysis']) && 
                          (!empty($content['analysis']['summary']) || 
                           !empty($content['analysis']['findings']));
            $hasText = !empty($content['text']) && strlen(trim($content['text'])) > 20;
            
            // Skip if no content
            if (!$hasAnalysis && !$hasText && !$this->isImageDocument($document)) {
                continue;
            }
            
            $html .= '<div style="margin-bottom: 15px;">';
            
            // Priority 1: Embed actual image if this is an image document
            if ($this->isImageDocument($document)) {
                $imageHtml = $this->embedImage($document);
                if (!empty($imageHtml)) {
                    $html .= $imageHtml;
                    $html .= '</div>';
                    continue; // Skip text for images
                }
            }
            
            // Priority 2: Add analysis if available
            if (isset($content['analysis'])) {
                $analysis = $content['analysis'];
                
                // Add summary - formatted as proper paragraphs
                if (!empty($analysis['summary'])) {
                    $html .= $this->formatTextForPdf(h($analysis['summary']));
                }
                
                // Add findings
                if (!empty($analysis['findings'])) {
                    $html .= '<p><strong>Key Findings:</strong></p>';
                    $html .= '<ul>';
                    foreach (array_slice($analysis['findings'], 0, 5) as $finding) {
                        $html .= '<li>' . $this->formatTextForPdf(h($finding)) . '</li>';
                    }
                    $html .= '</ul>';
                }
            } else {
                // Priority 3: Fallback to text excerpt
                if (!empty($content['text'])) {
                    $excerpt = substr($content['text'], 0, 500);
                    if (strlen($content['text']) > 500) {
                        // Try to end at sentence
                        $lastPeriod = strrpos($excerpt, '.');
                        if ($lastPeriod !== false && $lastPeriod > 300) {
                            $excerpt = substr($excerpt, 0, $lastPeriod + 1);
                        } else {
                            $excerpt .= '...';
                        }
                    }
                    $html .= $this->formatTextForPdf(h($excerpt));
                }
            }
            
            $html .= '</div>';
        }

        return $html;
    }

    /**
     * Build conclusions section
     *
     * @param object $case Case entity
     * @return string HTML content
     */
    private function buildConclusions($case): string
    {
        $html = '';

        // Add case-specific notes as conclusions
        if (!empty($case->notes)) {
            $html .= $this->formatTextForPdf(h($case->notes));
        }

        // Add generic conclusion based on procedures
        $procedureCount = count($case->cases_exams_procedures ?? []);
        if ($procedureCount > 0) {
            $html .= '<p>Study completed with ' . $procedureCount . ' procedure(s). ';
            $html .= 'Detailed analysis and clinical correlation will be provided in the final interpretation.</p>';
        }

        return $html ?: '<p>Pending final interpretation.</p>';
    }

    /**
     * Build subsections
     *
     * @param array $subsectionTitles List of subsection titles
     * @param object $case Case entity
     * @param array $documentContents Document contents
     * @return array Built subsections
     */
    private function buildSubsections(
        array $subsectionTitles, 
        $case, 
        array $documentContents
    ): array {
        $subsections = [];

        foreach ($subsectionTitles as $item) {
            // Handle both string titles and array subsection definitions
            if (is_array($item)) {
                $title = $item['title'] ?? 'Untitled';
                $subsections[] = [
                    'title' => $title,
                    'content' => $this->buildSubsectionContent($title, $case, $documentContents)
                ];
            } else {
                // Simple string title
                $subsections[] = [
                    'title' => $item,
                    'content' => $this->buildSubsectionContent($item, $case, $documentContents)
                ];
            }
        }

        return $subsections;
    }

    /**
     * Build subsection content
     *
     * @param string $title Subsection title
     * @param object $case Case entity
     * @param array $documentContents Document contents
     * @return string Content HTML
     */
    private function buildSubsectionContent(
        string $title, 
        $case, 
        array $documentContents
    ): string {
        // Try to find relevant content from documents based on subsection title
        $relevantContent = $this->extractRelevantDocumentContent($title, $documentContents);
        
        if (!empty($relevantContent)) {
            return $relevantContent;
        }

        // Fallback to generic content based on title
        $titleLower = strtolower($title);

        if (str_contains($titleLower, 'equipment') || str_contains($titleLower, 'specifications')) {
            return $this->buildEquipmentContent($case, $documentContents);
        } elseif (str_contains($titleLower, 'methodology') || str_contains($titleLower, 'acquisition')) {
            return $this->buildMethodologyContent($case, $documentContents);
        } elseif (str_contains($titleLower, 'analysis') || str_contains($titleLower, 'methods')) {
            return $this->buildAnalysisContent($case, $documentContents);
        } elseif (str_contains($titleLower, 'demographics') || str_contains($titleLower, 'patient info')) {
            return $this->buildDemographicsContent($case);
        } elseif (str_contains($titleLower, 'contact')) {
            return '<p><em>Contact information on file.</em></p>';
        } elseif (str_contains($titleLower, 'medical history') || str_contains($titleLower, 'chronic conditions')) {
            return $this->buildMedicalHistoryContent($case, $documentContents);
        } elseif (str_contains($titleLower, 'medication')) {
            return $this->buildMedicationContent($case);
        } elseif (str_contains($titleLower, 'procedure details') || str_contains($titleLower, 'procedures performed')) {
            return $this->buildProcedureList($case);
        } elseif (str_contains($titleLower, 'follow-up') || str_contains($titleLower, 'care instructions')) {
            return $this->buildFollowUpContent($case, $documentContents);
        } elseif (str_contains($titleLower, 'document list') || str_contains($titleLower, 'attached documents')) {
            return $this->buildDocumentList($documentContents);
        }

        // If we have any documents, show a summary
        if (!empty($documentContents)) {
            return $this->buildGeneralDocumentSummary($documentContents);
        }

        return '<p><em>Information will be added following detailed review.</em></p>';
    }

    /**
     * Extract relevant content from documents based on subsection title
     *
     * @param string $title Subsection title
     * @param array $documentContents All document contents
     * @return string HTML content or empty string
     */
    private function extractRelevantDocumentContent(string $title, array $documentContents): string
    {
        if (empty($documentContents)) {
            return '';
        }

        $html = '';
        $titleLower = strtolower($title);
        $keywords = $this->extractKeywords($titleLower);

        foreach ($documentContents as $docId => $docData) {
            $content = $docData['content'] ?? [];
            $document = $docData['document'] ?? null;
            
            if (!$document || empty($content['text'])) {
                continue;
            }

            // Search for relevant content in the document
            $relevantSections = $this->findRelevantSections($content['text'], $keywords);
            
            if (!empty($relevantSections)) {
                $html .= '<div style="margin-bottom: 10px;">';
                $html .= '<p><small><em>From: ' . h($document->original_filename) . '</em></small></p>';
                
                foreach ($relevantSections as $section) {
                    $html .= '<p>' . nl2br(h($section)) . '</p>';
                }
                
                $html .= '</div>';
            }
        }

        return $html;
    }

    /**
     * Extract keywords from title for content matching
     *
     * @param string $title Subsection title (lowercase)
     * @return array Keywords
     */
    private function extractKeywords(string $title): array
    {
        // Remove common words
        $commonWords = ['the', 'a', 'an', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for'];
        $words = explode(' ', $title);
        
        return array_filter($words, function($word) use ($commonWords) {
            return !in_array($word, $commonWords) && strlen($word) > 2;
        });
    }

    /**
     * Find relevant sections in text based on keywords
     *
     * @param string $text Full document text
     * @param array $keywords Keywords to search for
     * @return array Relevant text sections
     */
    private function findRelevantSections(string $text, array $keywords): array
    {
        $sections = [];
        $lines = explode("\n", $text);
        
        foreach ($lines as $i => $line) {
            $lineLower = strtolower($line);
            
            // Check if line contains any keywords
            foreach ($keywords as $keyword) {
                if (str_contains($lineLower, $keyword)) {
                    // Get context: current line + 2 lines before and after
                    $start = max(0, $i - 2);
                    $end = min(count($lines) - 1, $i + 2);
                    
                    $section = '';
                    for ($j = $start; $j <= $end; $j++) {
                        $section .= trim($lines[$j]) . "\n";
                    }
                    
                    $section = trim($section);
                    if (strlen($section) > 20 && !in_array($section, $sections)) {
                        $sections[] = $section;
                    }
                    
                    break; // Found keyword, move to next line
                }
            }
            
            // Limit to 3 most relevant sections
            if (count($sections) >= 3) {
                break;
            }
        }
        
        return $sections;
    }

    /**
     * Build equipment/specifications content
     */
    private function buildEquipmentContent($case, array $documentContents): string
    {
        $html = '<p>MEG was recorded using a whole-head Neuromag Triux 306-channel biomagnetometer system.</p>';
        
        // Add any equipment details from documents
        $equipmentInfo = $this->extractRelevantDocumentContent('equipment specifications', $documentContents);
        if (!empty($equipmentInfo)) {
            $html .= $equipmentInfo;
        }
        
        return $html;
    }

    /**
     * Build methodology/acquisition content
     */
    private function buildMethodologyContent($case, array $documentContents): string
    {
        $html = '<p>Data acquired following established clinical MEG protocols with continuous head position monitoring.</p>';
        
        // Add any methodology details from documents
        $methodInfo = $this->extractRelevantDocumentContent('methodology acquisition protocol', $documentContents);
        if (!empty($methodInfo)) {
            $html .= $methodInfo;
        }
        
        return $html;
    }

    /**
     * Build analysis methods content
     */
    private function buildAnalysisContent($case, array $documentContents): string
    {
        $html = '<p>Analysis performed using validated clinical methods and source localization techniques.</p>';
        
        // Add any analysis details from documents
        $analysisInfo = $this->extractRelevantDocumentContent('analysis methods results', $documentContents);
        if (!empty($analysisInfo)) {
            $html .= $analysisInfo;
        }
        
        return $html;
    }

    /**
     * Build demographics content
     */
    private function buildDemographicsContent($case): string
    {
        $patient = $case->patient_user ?? null;
        $patientDetails = null;
        
        if ($patient && !empty($patient->patients)) {
            $patientDetails = $patient->patients[0];
        }
        
        $html = '<ul>';
        $html .= '<li><strong>Name:</strong> ' . h($patient->first_name ?? 'N/A') . ' ' . h($patient->last_name ?? 'N/A') . '</li>';
        
        if ($patientDetails && $patientDetails->dob) {
            $age = $this->calculateAge($patientDetails->dob);
            $html .= '<li><strong>Age:</strong> ' . h($age) . ' years</li>';
            $html .= '<li><strong>Date of Birth:</strong> ' . h($patientDetails->dob->format('m/d/Y')) . '</li>';
        }
        
        $html .= '<li><strong>Gender:</strong> ' . h($patient->gender ?? 'N/A') . '</li>';
        $html .= '</ul>';
        
        return $html;
    }

    /**
     * Build medical history content
     */
    private function buildMedicalHistoryContent($case, array $documentContents): string
    {
        $html = '';
        
        if (!empty($case->symptoms)) {
            $html .= '<p><strong>Clinical Indication:</strong> ' . h($case->symptoms) . '</p>';
        }
        
        // Extract medical history from documents
        $historyInfo = $this->extractRelevantDocumentContent('history diagnosis condition', $documentContents);
        if (!empty($historyInfo)) {
            $html .= $historyInfo;
        } else {
            $html .= '<p>Medical history on file.</p>';
        }
        
        return $html;
    }

    /**
     * Build medication content
     */
    private function buildMedicationContent($case): string
    {
        $patient = $case->patient_user ?? null;
        $patientDetails = null;
        
        if ($patient && !empty($patient->patients)) {
            $patientDetails = $patient->patients[0];
        }
        
        if ($patientDetails && !empty($patientDetails->medications)) {
            return '<p>' . nl2br(h($patientDetails->medications)) . '</p>';
        }
        
        return '<p>No medications reported.</p>';
    }

    /**
     * Build follow-up care content
     */
    private function buildFollowUpContent($case, array $documentContents): string
    {
        $html = '';
        
        // Extract follow-up instructions from documents
        $followUpInfo = $this->extractRelevantDocumentContent('follow-up care instructions recommendations', $documentContents);
        if (!empty($followUpInfo)) {
            return $followUpInfo;
        }
        
        return '<p>Follow-up recommendations will be provided upon completion of analysis.</p>';
    }

    /**
     * Build document list
     */
    private function buildDocumentList(array $documentContents): string
    {
        if (empty($documentContents)) {
            return '<p>No supporting documents attached.</p>';
        }
        
        $html = '<ul>';
        foreach ($documentContents as $docId => $docData) {
            $document = $docData['document'] ?? null;
            if ($document) {
                $html .= '<li>' . h($document->original_filename);
                if (!empty($document->document_type)) {
                    $html .= ' <em>(' . h(ucwords(str_replace('_', ' ', $document->document_type))) . ')</em>';
                }
                $html .= '</li>';
            }
        }
        $html .= '</ul>';
        
        return $html;
    }

    /**
     * Build general document summary when no specific content found
     */
    private function buildGeneralDocumentSummary(array $documentContents): string
    {
        $html = '';
        
        foreach ($documentContents as $docId => $docData) {
            $content = $docData['content'] ?? [];
            $document = $docData['document'] ?? null;
            
            if (!$document) {
                continue;
            }
            
            $html .= '<div style="margin-bottom: 10px;">';
            $html .= '<p><strong>' . h($document->original_filename) . '</strong></p>';
            
            // Show analysis summary if available
            if (isset($content['analysis']['summary'])) {
                $html .= '<p>' . h($content['analysis']['summary']) . '</p>';
            }
            
            // Show brief text excerpt
            if (!empty($content['text'])) {
                $excerpt = substr($content['text'], 0, 200);
                if (strlen($content['text']) > 200) {
                    $excerpt .= '...';
                }
                $html .= '<p><small>' . h($excerpt) . '</small></p>';
            }
            
            $html .= '</div>';
        }
        
        return $html;
    }

    /**
     * Find documents related to a specific procedure
     *
     * @param int $procedureId Procedure ID
     * @param array $documentContents All document contents
     * @return string HTML with findings
     */
    private function findProcedureDocuments(int $procedureId, array $documentContents): string
    {
        $html = '';
        $foundDocuments = false;

        foreach ($documentContents as $docId => $docData) {
            // Check if this document is linked to this procedure
            if (isset($docData['procedure_id']) && $docData['procedure_id'] == $procedureId) {
                $foundDocuments = true;
                $content = $docData['content'] ?? [];
                $document = $docData['document'] ?? null;
                
                if (!$document) {
                    continue;
                }
                
                // Debug logging
                Log::debug('Processing document ' . $document->id . ': ' . $document->original_filename);
                Log::debug('Content keys: ' . implode(', ', array_keys($content)));
                Log::debug('Has text: ' . (!empty($content['text']) ? 'YES (' . strlen($content['text']) . ' chars)' : 'NO'));
                Log::debug('Has analysis: ' . (!empty($content['analysis']) ? 'YES' : 'NO'));
                Log::debug('Content success: ' . (!empty($content['success']) ? 'TRUE' : 'FALSE'));
                
                $html .= '<div class="document-findings" style="margin-bottom: 15px;">';
                
                // Check if we have meaningful content to display
                $hasAnalysis = isset($content['analysis']) && 
                              (!empty($content['analysis']['summary']) || 
                               !empty($content['analysis']['findings']));
                $hasText = !empty($content['text']) && strlen(trim($content['text'])) > 20;
                
                // If no meaningful content, skip this document
                if (!$hasAnalysis && !$hasText) {
                    $html .= '</div>';
                    continue;
                }
                
                // Priority 0: Embed image if document is an image file
                if ($this->isImageDocument($document)) {
                    $imageHtml = $this->embedImage($document);
                    if (!empty($imageHtml)) {
                        $html .= $imageHtml;
                        // If image is embedded, don't show text content (redundant OCR text)
                        $html .= '</div>';
                        continue;
                    }
                }
                
                // Priority 1: Show analysis summary and findings (most valuable)
                if (isset($content['analysis'])) {
                    $analysis = $content['analysis'];
                    
                    // Add summary first (concise overview) - formatted as proper paragraphs
                    if (!empty($analysis['summary'])) {
                        $html .= '<p>' . $this->formatTextForPdf(h($analysis['summary'])) . '</p>';
                    }
                    
                    // Add key findings (structured information)
                    if (!empty($analysis['findings'])) {
                        $html .= '<p><strong>Key Findings:</strong></p>';
                        $html .= '<ul>';
                        foreach ($analysis['findings'] as $finding) {
                            $html .= '<li>' . $this->formatTextForPdf(h($finding)) . '</li>';
                        }
                        $html .= '</ul>';
                    }
                }
                
                // Priority 2: Show full text content (detailed information)
                if (!empty($content['text']) && !isset($content['analysis']['summary'])) {
                    // Only show text if we don't have analysis summary (to avoid redundancy)
                    $text = trim($content['text']);
                    
                    // Format text into proper paragraphs (up to 2000 chars for comprehensive view)
                    $excerpt = substr($text, 0, 2000);
                    if (strlen($text) > 2000) {
                        // Try to end at a sentence
                        $lastPeriod = strrpos($excerpt, '.');
                        if ($lastPeriod !== false && $lastPeriod > 1500) {
                            $excerpt = substr($excerpt, 0, $lastPeriod + 1);
                        } else {
                            $excerpt .= '...';
                        }
                    }
                    
                    // Convert to paragraphs (split on double newlines or multiple spaces)
                    $html .= $this->formatTextForPdf(h($excerpt));
                }
                
                $html .= '</div>';
            }
        }

        if (!$foundDocuments) {
            return '';
        }

        return $html;
    }

    /**
     * Check if document is an image file
     *
     * @param object $document Document entity
     * @return bool True if image
     */
    private function isImageDocument($document): bool
    {
        if (empty($document->file_type)) {
            return false;
        }
        
        $fileType = strtolower($document->file_type);
        $imageTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/tiff', 'image/bmp'];
        
        return in_array($fileType, $imageTypes) || str_contains($fileType, 'image/');
    }

    /**
     * Embed image in report
     *
     * @param object $document Document entity
     * @return string HTML with embedded image
     */
    private function embedImage($document): string
    {
        try {
            // Get file path
            $filePath = $this->getDocumentFilePath($document);
            
            if (!$filePath || !file_exists($filePath)) {
                Log::warning('Image file not found for embedding: ' . ($filePath ?? 'null path'));
                return '';
            }
            
            // Convert image to base64 for embedding in PDF
            $imageData = file_get_contents($filePath);
            $base64 = base64_encode($imageData);
            
            // Get image info for proper sizing
            $imageInfo = @getimagesize($filePath);
            $mimeType = $imageInfo ? image_type_to_mime_type($imageInfo[2]) : $document->file_type;
            
            // Calculate display dimensions (max width 700px, maintain aspect ratio)
            $maxWidth = 700;
            $width = $imageInfo[0] ?? $maxWidth;
            $height = $imageInfo[1] ?? 500;
            
            if ($width > $maxWidth) {
                $ratio = $maxWidth / $width;
                $width = $maxWidth;
                $height = (int)($height * $ratio);
            }
            
            // Build HTML with embedded image
            $html = '<div style="text-align: center; margin: 15px 0;">';
            $html .= '<img src="data:' . $mimeType . ';base64,' . $base64 . '" ';
            $html .= 'style="max-width: ' . $width . 'px; height: auto; border: 1px solid #ddd; padding: 5px;" ';
            $html .= 'alt="' . h($document->original_filename) . '" />';
            $html .= '<p style="font-size: 9pt; color: #666; margin-top: 5px;"><em>Image: ' . h($document->original_filename) . '</em></p>';
            $html .= '</div>';
            
            return $html;
            
        } catch (\Exception $e) {
            Log::error('Failed to embed image: ' . $e->getMessage());
            return '';
        }
    }

    /**
     * Get document file path
     *
     * @param object $document Document entity
     * @return string File path
     */
    private function getDocumentFilePath($document): ?string
    {
        $storedPath = $document->file_path ?? null;
        
        // Handle null or empty path
        if (empty($storedPath)) {
            return null;
        }
        
        // Check if storedPath already starts with 'uploads/' to avoid duplication
        if (str_starts_with($storedPath, 'uploads/') || str_starts_with($storedPath, 'uploads' . DS)) {
            return WWW_ROOT . $storedPath;
        }
        
        // Path doesn't include uploads directory
        return WWW_ROOT . 'uploads' . DS . $storedPath;
    }

    /**
     * Calculate age from date of birth
     *
     * @param mixed $dob Date of birth
     * @return string Age or 'N/A'
     */
    private function calculateAge($dob): string
    {
        if (!$dob) {
            return 'N/A';
        }

        try {
            $birthDate = new \DateTime($dob->format('Y-m-d'));
            $today = new \DateTime();
            $age = $birthDate->diff($today)->y;
            return (string)$age;
        } catch (\Exception $e) {
            return 'N/A';
        }
    }

    /**
     * Format sedation information
     *
     * @param mixed $sedation Sedation entity
     * @return string Formatted sedation text
     */
    private function formatSedation($sedation): string
    {
        if (!$sedation || !$sedation->name) {
            return 'without sedation';
        }

        return 'with ' . strtolower($sedation->name);
    }

    /**
     * Get referring physician name
     *
     * @param object $case Case entity
     * @return string Physician name
     */
    private function getReferringPhysician($case): string
    {
        if ($case->user) {
            return $case->user->first_name . ' ' . $case->user->last_name;
        }

        return 'N/A';
    }

    /**
     * Get default formatting settings
     *
     * @return array Formatting settings
     */
    private function getDefaultFormatting(): array
    {
        return [
            'font' => 'Times New Roman',
            'title_size' => '16pt',
            'section_size' => '12pt',
            'body_size' => '11pt'
        ];
    }

    /**
     * Format text for professional PDF display
     * Converts text with newlines into proper HTML paragraphs
     *
     * @param string $text Text to format
     * @return string Formatted HTML
     */
    private function formatTextForPdf(string $text): string
    {
        if (empty($text)) {
            return '';
        }
        
        // Split on double newlines (paragraph breaks)
        $paragraphs = preg_split('/\n\s*\n/', $text);
        
        $html = '';
        foreach ($paragraphs as $paragraph) {
            $paragraph = trim($paragraph);
            if (empty($paragraph)) {
                continue;
            }
            
            // Replace single newlines with spaces (join lines within paragraph)
            $paragraph = preg_replace('/\s*\n\s*/', ' ', $paragraph);
            
            // Remove excessive spaces
            $paragraph = preg_replace('/\s+/', ' ', $paragraph);
            
            // Wrap in paragraph tags
            $html .= '<p>' . $paragraph . '</p>';
        }
        
        return $html;
    }
}
