<?php
/**
 * Scientist Report Template Element
 * 
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Case $case
 * @var \App\Model\Entity\Hospital $hospital
 * @var array $options
 */

$patient = $case->patient_user ?? null;
$reportType = $options['type'] ?? 'general';
$includeAI = $options['include_ai'] ?? false;

?>

<div class="scientist-report-template">
    <h2>Medical Analysis Report</h2>
    
    <!-- Patient Header Section -->
    <div class="patient-header">
        <h3><i class="fas fa-user-injured"></i> Patient Information</h3>
        <table style="width: 100%; border-collapse: collapse; margin: 15px 0;">
            <tr style="background-color: #f8f9fa;">
                <td style="padding: 10px; border: 1px solid #ddd; font-weight: bold; width: 30%;">Patient Name:</td>
                <td style="padding: 10px; border: 1px solid #ddd;">
                    <?php echo  $patient ? $this->PatientMask->displayName($patient) : '[Patient Name]' ?>
                </td>
                <td style="padding: 10px; border: 1px solid #ddd; font-weight: bold; width: 30%;">Case ID:</td>
                <td style="padding: 10px; border: 1px solid #ddd;">#<?php echo  h($case->id) ?></td>
            </tr>
            <tr>
                <td style="padding: 10px; border: 1px solid #ddd; font-weight: bold;">Date of Birth:</td>
                <td style="padding: 10px; border: 1px solid #ddd;">
                    <?php echo  $patient ? $this->PatientMask->displayDob($patient) : '[Date of Birth]' ?>
                </td>
                <td style="padding: 10px; border: 1px solid #ddd; font-weight: bold;">Report Date:</td>
                <td style="padding: 10px; border: 1px solid #ddd;"><?php echo  date('F j, Y') ?></td>
            </tr>
            <tr style="background-color: #f8f9fa;">
                <td style="padding: 10px; border: 1px solid #ddd; font-weight: bold;">Hospital:</td>
                <td style="padding: 10px; border: 1px solid #ddd;" colspan="3"><?php echo  h($hospital->name) ?></td>
            </tr>
        </table>
    </div>

    <?php if ($reportType === 'general'): ?>
    <!-- General Medical Report -->
    <h2>Clinical Summary</h2>
    <p><strong>Chief Complaint:</strong> [Enter the primary reason for patient presentation and analysis]</p>
    
    <h3>Clinical History</h3>
    <p>[Provide detailed clinical history including:</p>
    <ul>
        <li>Present illness description and timeline</li>
        <li>Relevant past medical history</li>
        <li>Current medications and treatments</li>
        <li>Family history of relevance</li>
        <li>Social history factors</li>
    </ul>
    
    <h3>Scientific Analysis</h3>
    <p>[Document systematic analysis including:]</p>
    <ul>
        <li>Data interpretation and statistical analysis</li>
        <li>Laboratory and diagnostic findings correlation</li>
        <li>Scientific literature review and evidence</li>
        <li>Methodological considerations</li>
    </ul>
    
    <h3>Assessment and Findings</h3>
    <p>[Provide comprehensive assessment:]</p>
    <ul>
        <li>Primary scientific conclusions</li>
        <li>Supporting evidence and data</li>
        <li>Confidence levels and statistical significance</li>
        <li>Limitations and considerations</li>
    </ul>
    
    <h3>Recommendations</h3>
    <p>[Scientific recommendations for:]</p>
    <ol>
        <li>Further investigation needs</li>
        <li>Additional testing requirements</li>
        <li>Methodological improvements</li>
        <li>Clinical correlation suggestions</li>
    </ol>

    <?php elseif ($reportType === 'pathology'): ?>
    <!-- Pathology Analysis Report -->
    <h2>Pathological Analysis</h2>
    
    <h3>Specimen Information</h3>
    <table style="width: 100%; border-collapse: collapse; margin: 15px 0;">
        <tr style="background-color: #f8f9fa;">
            <td style="padding: 8px; border: 1px solid #ddd; font-weight: bold;">Specimen Type:</td>
            <td style="padding: 8px; border: 1px solid #ddd;">[Specify specimen type]</td>
        </tr>
        <tr>
            <td style="padding: 8px; border: 1px solid #ddd; font-weight: bold;">Collection Date:</td>
            <td style="padding: 8px; border: 1px solid #ddd;">[Collection date and time]</td>
        </tr>
        <tr style="background-color: #f8f9fa;">
            <td style="padding: 8px; border: 1px solid #ddd; font-weight: bold;">Specimen ID:</td>
            <td style="padding: 8px; border: 1px solid #ddd;">[Unique specimen identifier]</td>
        </tr>
    </table>
    
    <h3>Gross Examination</h3>
    <p>[Describe macroscopic findings including size, color, texture, and overall appearance of the specimen]</p>
    
    <h3>Microscopic Analysis</h3>
    <p>[Detailed histological examination including:]</p>
    <ul>
        <li>Cellular morphology and architecture</li>
        <li>Tissue organization and structure</li>
        <li>Presence of abnormal findings</li>
        <li>Inflammatory or pathological changes</li>
    </ul>
    
    <h3>Scientific Interpretation</h3>
    <p>[Provide scientific analysis of findings:]</p>
    <ul>
        <li>Correlation with clinical presentation</li>
        <li>Comparison with normal parameters</li>
        <li>Statistical analysis of measurements</li>
        <li>Evidence-based interpretation</li>
    </ul>
    
    <h3>Pathological Conclusion</h3>
    <p>[Final pathological assessment with confidence levels and recommendations for clinical correlation]</p>

    <?php elseif ($reportType === 'laboratory'): ?>
    <!-- Laboratory Analysis Report -->
    <h2>Laboratory Analysis Report</h2>
    
    <h3>Test Parameters</h3>
    <table style="width: 100%; border-collapse: collapse; margin: 15px 0;">
        <thead>
            <tr style="background-color: #28a745; color: white;">
                <th style="padding: 10px; border: 1px solid #ddd;">Test Name</th>
                <th style="padding: 10px; border: 1px solid #ddd;">Result</th>
                <th style="padding: 10px; border: 1px solid #ddd;">Reference Range</th>
                <th style="padding: 10px; border: 1px solid #ddd;">Status</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="padding: 8px; border: 1px solid #ddd;">[Test Parameter 1]</td>
                <td style="padding: 8px; border: 1px solid #ddd;">[Result Value]</td>
                <td style="padding: 8px; border: 1px solid #ddd;">[Normal Range]</td>
                <td style="padding: 8px; border: 1px solid #ddd;">[Normal/Abnormal]</td>
            </tr>
            <tr style="background-color: #f8f9fa;">
                <td style="padding: 8px; border: 1px solid #ddd;">[Test Parameter 2]</td>
                <td style="padding: 8px; border: 1px solid #ddd;">[Result Value]</td>
                <td style="padding: 8px; border: 1px solid #ddd;">[Normal Range]</td>
                <td style="padding: 8px; border: 1px solid #ddd;">[Normal/Abnormal]</td>
            </tr>
        </tbody>
    </table>
    
    <h3>Statistical Analysis</h3>
    <p>[Provide statistical interpretation of results including:]</p>
    <ul>
        <li>Variance analysis and standard deviations</li>
        <li>Correlation coefficients between parameters</li>
        <li>Trend analysis over time</li>
        <li>Quality control metrics</li>
    </ul>
    
    <h3>Scientific Interpretation</h3>
    <p>[Detailed scientific analysis including:]</p>
    <ul>
        <li>Biochemical significance of findings</li>
        <li>Physiological implications</li>
        <li>Potential interfering factors</li>
        <li>Literature correlation</li>
    </ul>

    <?php elseif ($reportType === 'radiology'): ?>
    <!-- Radiology Analysis Report -->
    <h2>Imaging Analysis Report</h2>
    
    <h3>Examination Details</h3>
    <table style="width: 100%; border-collapse: collapse; margin: 15px 0;">
        <tr style="background-color: #f8f9fa;">
            <td style="padding: 8px; border: 1px solid #ddd; font-weight: bold;">Imaging Modality:</td>
            <td style="padding: 8px; border: 1px solid #ddd;">[CT/MRI/X-ray/Ultrasound]</td>
        </tr>
        <tr>
            <td style="padding: 8px; border: 1px solid #ddd; font-weight: bold;">Study Date:</td>
            <td style="padding: 8px; border: 1px solid #ddd;">[Examination date]</td>
        </tr>
        <tr style="background-color: #f8f9fa;">
            <td style="padding: 8px; border: 1px solid #ddd; font-weight: bold;">Contrast Used:</td>
            <td style="padding: 8px; border: 1px solid #ddd;">[Yes/No - specify type]</td>
        </tr>
    </table>
    
    <h3>Technical Parameters</h3>
    <p>[Document technical aspects including image quality, acquisition parameters, and any limitations]</p>
    
    <h3>Imaging Findings</h3>
    <p>[Systematic description of findings by anatomical region or system]</p>
    
    <h3>Quantitative Analysis</h3>
    <p>[Provide measurements and quantitative data where applicable:]</p>
    <ul>
        <li>Dimensional measurements</li>
        <li>Density/intensity values</li>
        <li>Volume calculations</li>
        <li>Comparative analysis</li>
    </ul>
    
    <h3>Scientific Assessment</h3>
    <p>[Evidence-based interpretation correlating findings with clinical presentation and literature]</p>

    <?php endif; ?>

    <?php if ($includeAI): ?>
    <!-- AI Analysis Section -->
    <div class="technical-section" style="background-color: #f0f8ff; border-left: 4px solid #007bff; padding: 15px; margin: 20px 0;">
        <h3><i class="fas fa-robot"></i> AI-Assisted Analysis</h3>
        
        <h4>Machine Learning Insights</h4>
        <p>[AI-generated analysis including:]</p>
        <ul>
            <li>Pattern recognition findings</li>
            <li>Anomaly detection results</li>
            <li>Predictive modeling outcomes</li>
            <li>Risk stratification analysis</li>
        </ul>
        
        <h4>Confidence Metrics</h4>
        <table style="width: 100%; border-collapse: collapse; margin: 10px 0;">
            <tr>
                <td style="padding: 5px; border: 1px solid #ddd; font-weight: bold;">Overall Confidence:</td>
                <td style="padding: 5px; border: 1px solid #ddd;">[XX.X%]</td>
            </tr>
            <tr style="background-color: #f8f9fa;">
                <td style="padding: 5px; border: 1px solid #ddd; font-weight: bold;">Algorithm Version:</td>
                <td style="padding: 5px; border: 1px solid #ddd;">[Model version]</td>
            </tr>
            <tr>
                <td style="padding: 5px; border: 1px solid #ddd; font-weight: bold;">Training Data Size:</td>
                <td style="padding: 5px; border: 1px solid #ddd;">[Dataset size]</td>
            </tr>
        </table>
        
        <h4>AI Recommendations</h4>
        <p>[Machine learning generated recommendations for:]</p>
        <ol>
            <li>Additional testing priorities</li>
            <li>Follow-up scheduling optimization</li>
            <li>Risk factor monitoring</li>
            <li>Literature review suggestions</li>
        </ol>
        
        <p><em><strong>Note:</strong> AI analysis is provided as a scientific tool to assist in evaluation. 
        All AI-generated content should be reviewed and validated by qualified medical professionals.</em></p>
    </div>
    <?php endif; ?>

    <h2>Conclusion and Recommendations</h2>
    
    <h3>Summary of Findings</h3>
    <p>[Concise summary of key scientific findings and their clinical significance]</p>
    
    <h3>Evidence-Based Recommendations</h3>
    <p>[Provide specific, actionable recommendations based on scientific evidence:]</p>
    <ol>
        <li><strong>Immediate Actions:</strong> [Urgent recommendations]</li>
        <li><strong>Follow-up Studies:</strong> [Additional testing needed]</li>
        <li><strong>Monitoring Parameters:</strong> [What to track over time]</li>
        <li><strong>Quality Improvement:</strong> [Process enhancement suggestions]</li>
    </ol>
    
    <h3>Limitations and Considerations</h3>
    <p>[Acknowledge any limitations in the analysis including:]</p>
    <ul>
        <li>Sample size or data limitations</li>
        <li>Technical constraints</li>
        <li>Methodological considerations</li>
        <li>Areas requiring further investigation</li>
    </ul>
    
    <h3>Scientific References</h3>
    <p>[Include relevant literature citations and evidence base used in the analysis]</p>
    
    <!-- Report Footer -->
    <div style="margin-top: 40px; padding-top: 20px; border-top: 2px solid #28a745;">
        <p><strong>Report Generated:</strong> <?php echo  date('F j, Y \a\t g:i A') ?></p>
        <p><strong>Scientist Review Required:</strong> This report requires review and approval by a qualified scientist before clinical use.</p>
        <p><strong>Quality Assurance:</strong> This analysis follows established scientific protocols and quality standards.</p>
    </div>
</div>

<style>
.scientist-report-template {
    font-family: 'Times New Roman', serif;
    line-height: 1.6;
    color: #333;
}

.scientist-report-template h2 {
    color: #2c3e50;
    border-bottom: 2px solid #28a745;
    padding-bottom: 5px;
    margin-top: 25px;
    margin-bottom: 15px;
}

.scientist-report-template h3 {
    color: #495057;
    margin-top: 20px;
    margin-bottom: 10px;
}

.scientist-report-template h4 {
    color: #6c757d;
    margin-top: 15px;
    margin-bottom: 8px;
}

.scientist-report-template p {
    margin-bottom: 12px;
    text-align: justify;
}

.scientist-report-template ul, 
.scientist-report-template ol {
    margin-bottom: 15px;
    padding-left: 25px;
}

.scientist-report-template li {
    margin-bottom: 5px;
}

.patient-header {
    background-color: #f8fff9;
    border: 2px solid #28a745;
    padding: 15px;
    margin: 15px 0;
    border-radius: 5px;
}

.technical-section {
    background-color: #f8f9fa;
    border-left: 4px solid #28a745;
    padding: 15px;
    margin: 15px 0;
}

.scientist-report-template table {
    margin: 15px 0;
    font-size: 14px;
}

.scientist-report-template strong {
    color: #2c3e50;
}

.scientist-report-template em {
    color: #6c757d;
}
</style>