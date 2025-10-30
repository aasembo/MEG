<?php
declare(strict_types=1);

namespace App\Service;

use App\Model\Entity\Report;
use App\Model\Entity\MedicalCase;

/**
 * MEG Report Template Service
 * Provides standardized clinical MEG report templates
 */
class MegReportTemplateService
{
    /**
     * Generate complete MEG report template with placeholder data
     *
     * @param \App\Model\Entity\MedicalCase|null $case Medical case with patient data
     * @return array Report data structure with all sections
     */
    public function generateTemplate(?MedicalCase $case = null): array
    {
        $patientInfo = $this->getPatientInformation($case);
        $patientHistory = $this->getPatientHistory($case);
        $medication = $this->getMedication($case);
        $megRecordings = $this->getMegRecordings($case);
        $technicalDescription = $this->getTechnicalDescription($case);
        $msiConclusions = $this->getMsiConclusions($case);
        $signature = $this->getSignatureBlock($case);

        return [
            'patient_information' => $patientInfo,
            'patient_history' => $patientHistory,
            'medication' => $medication,
            'meg_recordings' => $megRecordings,
            'technical_description' => $technicalDescription,
            'msi_conclusions' => $msiConclusions,
            'signature_block' => $signature,
        ];
    }

    /**
     * Generate patient information section
     */
    protected function getPatientInformation(?MedicalCase $case): string
    {
        $lastName = $case->patient_user->last_name ?? '[LAST NAME]';
        $firstName = $case->patient_user->first_name ?? '[FIRST NAME]';
        $dob = $case->patient_user->dob ?? '[MM/DD/YYYY]';
        
        // Try to get patient-specific data if available
        $mrn = '[MRN]';
        $fin = '[FIN]';
        if (isset($case->patient_id)) {
            // If we have patient record, get MRN/FIN from there
            $patientsTable = \Cake\Datasource\FactoryLocator::get('Table')->get('Patients');
            try {
                $patient = $patientsTable->get($case->patient_id);
                $mrn = $patient->medical_record_number ?? '[MRN]';
                $fin = $patient->financial_record_number ?? '[FIN]';
            } catch (\Exception $e) {
                // Use defaults if patient not found
            }
        }
        
        $studyDate = $case->study_date ?? date('m/d/Y');
        $referringPhysician = $case->referring_physician ?? '[Referring Physician Name]';
        $megId = $case->meg_id ?? '[MEG ID]';

        return <<<HTML
<p><strong>Name:</strong> {$lastName}, {$firstName}</p>
<p><strong>Date of Birth:</strong> {$dob}</p>
<p><strong>MRN:</strong> {$mrn}</p>
<p><strong>FIN:</strong> {$fin}</p>
<p><strong>Date of Study:</strong> {$studyDate}</p>
<p><strong>Referring Physician:</strong> {$referringPhysician}</p>
<p><strong>MEG ID:</strong> {$megId}</p>
HTML;
    }

    /**
     * Generate patient history section
     */
    protected function getPatientHistory(?MedicalCase $case): string
    {
        // Calculate age from dob if available
        $age = '[AGE]';
        if (isset($case->patient_user->dob)) {
            $dob = new \DateTime($case->patient_user->dob);
            $now = new \DateTime();
            $age = $now->diff($dob)->y;
        }
        
        return <<<HTML
<p><strong>Patient History:</strong></p>
<p>This is a {$age}-year-old patient with a history of [primary diagnosis/condition]. The patient was referred for magnetoencephalography (MEG) evaluation to assist with [clinical indication - e.g., presurgical epilepsy evaluation, functional mapping for tumor resection, language lateralization, etc.].</p>

<p><strong>Clinical Background:</strong></p>
<ul>
    <li><strong>Primary Diagnosis:</strong> [e.g., Medically refractory epilepsy, Brain tumor, Stroke with aphasia]</li>
    <li><strong>Seizure History (if applicable):</strong> [Onset age, frequency, semiology, previous evaluations]</li>
    <li><strong>Prior Imaging:</strong> [MRI findings, CT findings, PET findings]</li>
    <li><strong>Previous Treatments:</strong> [Medications tried, surgeries, other interventions]</li>
    <li><strong>Reason for MEG Study:</strong> [Specific clinical question to be answered]</li>
</ul>
HTML;
    }

    /**
     * Generate medication section
     */
    protected function getMedication(?MedicalCase $case): string
    {
        return <<<HTML
<p><strong>Current Medications:</strong></p>
<ul>
    <li>[Medication Name] - [Dosage] - [Frequency]</li>
    <li>[Medication Name] - [Dosage] - [Frequency]</li>
    <li>[Medication Name] - [Dosage] - [Frequency]</li>
</ul>
<p><em>Note: If no medications, state "None" or "No current medications reported."</em></p>

<p><strong>Antiepileptic Drugs (if applicable):</strong></p>
<ul>
    <li>[AED Name] - [Current dose] - [Therapeutic level if available]</li>
</ul>

<p><strong>Medication Adjustments for Study:</strong> [e.g., No changes made / Reduced by 50% / Held for 24 hours prior]</p>
HTML;
    }

    /**
     * Generate MEG recordings section
     */
    protected function getMegRecordings(?MedicalCase $case): string
    {
        return <<<HTML
<p><strong>MEG RECORDINGS</strong></p>

<p><strong>Purpose of Study:</strong></p>
<p>Magnetoencephalography (MEG) with Magnetic Source Imaging (MSI) was performed to [localize epileptiform activity / map eloquent cortex / lateralize language function / localize sensory/motor cortex / other clinical indication].</p>

<p><strong>Procedures Performed:</strong></p>
<ul>
    <li>Spontaneous MEG recording for interictal epileptiform activity</li>
    <li>Somatosensory evoked fields (SEF) - [specify: median nerve, tibial nerve]</li>
    <li>Auditory evoked fields (AEF)</li>
    <li>Visual evoked fields (VEF)</li>
    <li>Motor mapping (voluntary movement)</li>
    <li>Receptive language mapping (auditory word recognition)</li>
    <li>Expressive language mapping (verb generation / picture naming)</li>
    <li>[Additional procedures as applicable]</li>
</ul>

<p><strong>Technical Note:</strong></p>
<p>The study was performed using a whole-head MEG system with [306/248/275] channels consisting of [204 planar gradiometers and 102 magnetometers / specify configuration]. Simultaneous 32-channel EEG was recorded using the international 10-20 system with additional electrodes [Fpz, F7, F8, T7, T8, P7, P8, Oz, etc.]. Head position was continuously monitored using [4-5] head position indicator (HPI) coils. Anatomical MRI was co-registered with MEG coordinate system using digitized fiducial points and head surface points acquired with [Polhemus Fastrak / 3D digitizer]. Data were acquired at a sampling rate of [1000 Hz / 1200 Hz] with online bandpass filter of [0.1-330 Hz / specify]. Environmental noise was reduced using [temporal Signal Space Separation (tSSS) / Signal Space Projection (SSP) / other method]. Source localization was performed using [equivalent current dipole (ECD) modeling / beamformer analysis / minimum norm estimate / other method] with the head modeled as a [single sphere / realistic boundary element model].</p>
HTML;
    }

    /**
     * Generate technical description of procedures
     */
    protected function getTechnicalDescription(?MedicalCase $case): string
    {
        return <<<HTML
<p><strong>TECHNICAL DESCRIPTION OF PROCEDURES</strong></p>

<h5>1. Spontaneous Recording (Interictal Epileptiform Activity Detection)</h5>
<ul>
    <li><strong>Protocol:</strong> Continuous MEG and EEG recording for [20-60] minutes in awake resting state</li>
    <li><strong>Instructions:</strong> Patient instructed to remain still, alternating eyes open and closed</li>
    <li><strong>Activation Procedures:</strong> [Hyperventilation (3 minutes) / Photic stimulation / Sleep / None]</li>
    <li><strong>Analysis:</strong> Data reviewed for interictal epileptiform discharges (spikes, sharp waves). Identified events marked and averaged. Source localization performed on averaged waveforms using ECD modeling with goodness of fit >80%.</li>
    <li><strong>Filtering:</strong> Bandpass [1-70 Hz] for spike detection, notch filter at 60 Hz</li>
</ul>

<h5>2. Somatosensory Evoked Fields (SEF)</h5>
<ul>
    <li><strong>Stimulation Site:</strong> [Right/Left] median nerve at wrist [and/or tibial nerve at ankle]</li>
    <li><strong>Stimulation Parameters:</strong> 
        <ul>
            <li>Intensity: [Motor threshold + X mA or sensory threshold]</li>
            <li>Duration: 0.2 ms square wave pulse</li>
            <li>Rate: 2-4 Hz</li>
        </ul>
    </li>
    <li><strong>Trials:</strong> [200-400] artifact-free trials averaged</li>
    <li><strong>Analysis Epoch:</strong> -100 to 400 ms relative to stimulus onset</li>
    <li><strong>Filtering:</strong> Bandpass 1-200 Hz</li>
    <li><strong>Components Analyzed:</strong> 
        <ul>
            <li>N20m (median) / P40m (tibial) - Primary somatosensory cortex (S1)</li>
            <li>Later components - Secondary somatosensory cortex (S2) if present</li>
        </ul>
    </li>
    <li><strong>Source Localization:</strong> Single ECD fit at peak latency with GOF >80%</li>
</ul>

<h5>3. Auditory Evoked Fields (AEF)</h5>
<ul>
    <li><strong>Stimulus:</strong> 1000 Hz pure tone, 50 ms duration, 5 ms rise/fall time</li>
    <li><strong>Presentation:</strong> Binaural via air conduction tubes, 60-70 dB above hearing threshold</li>
    <li><strong>Rate:</strong> 0.5-1 Hz</li>
    <li><strong>Trials:</strong> [100-200] per hemisphere</li>
    <li><strong>Analysis Epoch:</strong> -100 to 500 ms</li>
    <li><strong>Components:</strong> M50, M100 (N100m), M200</li>
    <li><strong>Localization:</strong> ECD at M100 peak (~100 ms) for each hemisphere</li>
</ul>

<h5>4. Visual Evoked Fields (VEF)</h5>
<ul>
    <li><strong>Stimulus:</strong> Reversing checkerboard pattern, [8° × 8° or full field]</li>
    <li><strong>Reversal Rate:</strong> 1-2 Hz</li>
    <li><strong>Trials:</strong> [100-200] artifact-free trials</li>
    <li><strong>Presentation:</strong> [Left hemifield / Right hemifield / Full field]</li>
    <li><strong>Analysis Epoch:</strong> -100 to 400 ms</li>
    <li><strong>Components:</strong> M75 (P75m), M100 (N100m), M145 (P100m)</li>
    <li><strong>Localization:</strong> ECD at M100 peak for primary visual cortex (V1)</li>
</ul>

<h5>5. Motor Mapping (Voluntary Movement)</h5>
<ul>
    <li><strong>Task:</strong> Self-paced [finger tapping / hand squeeze / toe movement]</li>
    <li><strong>Side:</strong> [Right/Left/Bilateral]</li>
    <li><strong>Trials:</strong> [50-100] movements</li>
    <li><strong>EMG Recording:</strong> Surface electrodes over [flexor digitorum / first dorsal interosseous / tibialis anterior]</li>
    <li><strong>Analysis:</strong> Event-related desynchronization (ERD) in beta band (15-30 Hz) prior to movement onset</li>
    <li><strong>Localization:</strong> Beamformer/SAM analysis or ECD of movement-related fields</li>
</ul>

<h5>6. Receptive Language (Auditory Word Recognition)</h5>
<ul>
    <li><strong>Paradigm:</strong> Passive listening to [words vs. tones / words vs. pseudowords]</li>
    <li><strong>Stimuli:</strong> [200-400] words and [200-400] control stimuli</li>
    <li><strong>Presentation Rate:</strong> 1 every 2-3 seconds</li>
    <li><strong>Analysis:</strong> Contrast words > control stimuli, time window 200-600 ms</li>
    <li><strong>Localization:</strong> Statistical parametric mapping or ECD of language-specific responses</li>
    <li><strong>Lateralization Index:</strong> Calculated based on activity in left vs. right superior temporal regions</li>
</ul>

<h5>7. Expressive Language (Verb Generation / Picture Naming)</h5>
<ul>
    <li><strong>Paradigm:</strong> Covert [verb generation / picture naming / word generation]</li>
    <li><strong>Stimuli:</strong> [50-100] nouns/pictures and [50-100] control stimuli (e.g., fixation cross)</li>
    <li><strong>Presentation:</strong> Visual, 2-4 seconds per stimulus</li>
    <li><strong>Instructions:</strong> Silently generate verb / name picture without vocalization</li>
    <li><strong>Analysis:</strong> Contrast language task > control, time window 300-800 ms</li>
    <li><strong>Localization:</strong> Inferior frontal (Broca's area) and posterior temporal activation</li>
    <li><strong>Lateralization Index:</strong> Left vs. right frontal and temporal activation</li>
</ul>
HTML;
    }

    /**
     * Generate MSI conclusions section
     */
    protected function getMsiConclusions(?MedicalCase $case): string
    {
        return <<<HTML
<p><strong>MSI CONCLUSIONS</strong></p>

<h5>1. Interictal Epileptiform Activity</h5>
<p><strong>Findings:</strong> [Number] interictal epileptiform discharges were identified during the recording session. Source localization revealed clustering in the [left/right] [temporal/frontal/parietal/occipital] region, specifically in the vicinity of [specific gyrus/anatomical structure]. Dipole solutions showed goodness of fit ranging from [80-95]% with tight spatial clustering (< [10] mm).</p>
<p><strong>Anatomical Localization:</strong> [Left/Right] [specific cortical region], [Brodmann Area if applicable]</p>
<p><strong>Clinical Correlation:</strong> Findings are consistent with [clinical hypothesis/imaging findings/seizure semiology].</p>
<p><strong>SNR:</strong> [Good/Moderate/Low] - [brief note on signal quality]</p>

<h5>2. Somatosensory Mapping</h5>
<p><strong>Right Median Nerve Stimulation:</strong></p>
<ul>
    <li><strong>N20m Component:</strong> Localized to [left/right] postcentral gyrus, [specific location on hand knob]</li>
    <li><strong>Latency:</strong> [~20] ms</li>
    <li><strong>GOF:</strong> [X]%</li>
    <li><strong>Distance from Lesion/Target:</strong> [X] mm</li>
</ul>
<p><strong>Left Median Nerve Stimulation:</strong></p>
<ul>
    <li><strong>N20m Component:</strong> Localized to [left/right] postcentral gyrus</li>
    <li><strong>Latency:</strong> [~20] ms</li>
    <li><strong>GOF:</strong> [X]%</li>
</ul>
<p><strong>Anatomical Organization:</strong> Normal somatotopic organization in primary sensory cortex (S1). [Any abnormalities noted]</p>

<h5>3. Motor Cortex Localization</h5>
<p><strong>Right Hand Movement:</strong> Beta band desynchronization localized to [left/right] precentral gyrus, consistent with primary motor cortex (M1). Location is [X] mm [anterior/posterior/medial/lateral] to [anatomical landmark or lesion].</p>
<p><strong>Left Hand Movement:</strong> [Similar description]</p>
<p><strong>SNR:</strong> [Good/Moderate/Low]</p>

<h5>4. Auditory Cortex Localization</h5>
<p><strong>Right Hemisphere:</strong> M100 component at [~100] ms localized to superior temporal gyrus, Heschl's gyrus region (primary auditory cortex).</p>
<p><strong>Left Hemisphere:</strong> M100 component at [~100] ms localized to superior temporal gyrus, Heschl's gyrus region.</p>
<p><strong>Symmetry:</strong> [Bilateral symmetric activation / Asymmetric with predominance on left/right]</p>
<p><strong>SNR:</strong> [Good/Moderate/Low]</p>

<h5>5. Visual Cortex Localization</h5>
<p><strong>Findings:</strong> Visual evoked responses localized to [bilateral/left/right] occipital cortex, consistent with primary visual cortex (V1) in the region of the calcarine fissure.</p>
<p><strong>Retinotopy:</strong> [Left hemifield stimulation activated right V1, right hemifield activated left V1 - normal retinotopic organization]</p>
<p><strong>SNR:</strong> [Good/Moderate/Low due to distance from sensors]</p>

<h5>6. Receptive Language Lateralization</h5>
<p><strong>Findings:</strong> Auditory word processing revealed [strong left/bilateral/right] lateralization. Peak activation in [left/right] superior temporal gyrus (Wernicke's area) during time window 300-500 ms.</p>
<p><strong>Lateralization Index (LI):</strong> [+0.X] indicating [strong left/moderate left/bilateral/right] dominance</p>
<p><strong>LI Scale:</strong> -1.0 (complete right) to +1.0 (complete left); typical left dominance: +0.5 to +1.0</p>

<h5>7. Expressive Language Lateralization</h5>
<p><strong>Findings:</strong> Verb generation task revealed [strong left/bilateral/right] lateralization with activation in [left/right] inferior frontal gyrus (Broca's area) and [left/right] posterior temporal regions.</p>
<p><strong>Frontal LI:</strong> [+0.X] - [strong left/moderate left/bilateral]</p>
<p><strong>Temporal LI:</strong> [+0.X] - [strong left/moderate left/bilateral]</p>
<p><strong>Clinical Interpretation:</strong> [Consistent with typical left hemisphere language dominance / Atypical lateralization noted / Bilateral representation suggests increased risk for language deficits with left hemisphere surgery]</p>

<h5>Overall Summary</h5>
<p>MEG/MSI examination successfully localized [epileptiform activity to / eloquent cortex including] [specific findings]. These findings should be integrated with other presurgical evaluations including [structural MRI, functional MRI, Wada testing, neuropsychological assessment, etc.] for comprehensive surgical planning.</p>

<p><strong>Clinical Recommendations:</strong></p>
<ul>
    <li>[Proceed with surgical evaluation]</li>
    <li>[Consider additional testing: fMRI, Wada test, intracranial monitoring]</li>
    <li>[Distance from eloquent cortex suggests favorable surgical outcome]</li>
    <li>[Close proximity to language areas suggests risk of postoperative deficit]</li>
    <li>[Atypical findings warrant further investigation]</li>
</ul>

<p><strong>Limitations:</strong> [Any technical issues, patient cooperation problems, or interpretation caveats should be noted here]</p>
HTML;
    }

    /**
     * Generate signature block
     */
    protected function getSignatureBlock(?MedicalCase $case): string
    {
        return <<<HTML
<p><strong>___________________________________</strong></p>
<p><strong>[Physician Name], MD, PhD</strong></p>
<p>Director, MEG Laboratory</p>
<p>Associate Professor of Neurology and Neuroscience</p>
<p>[Institution Name]</p>
<p>[Department of Neurology]</p>
<p>[Medical Center Address]</p>
<p>Date: [MM/DD/YYYY]</p>

<p><em>Reviewed and approved by:</em></p>
<p><strong>___________________________________</strong></p>
<p><strong>[Attending Physician Name], MD</strong></p>
<p>[Title]</p>
<p>Date: [MM/DD/YYYY]</p>

<hr>

<p><em><strong>Note:</strong> This magnetoencephalography report provides localization of brain function based on magnetic source imaging. Clinical decisions regarding surgical treatment should incorporate multiple diagnostic modalities and multidisciplinary team discussion.</em></p>
HTML;
    }

    /**
     * Generate plain text version of the report (for TXT export)
     *
     * @param array $reportData Report data array
     * @return string Plain text formatted report
     */
    public function generatePlainText(array $reportData): string
    {
        $text = "MAGNETOENCEPHALOGRAPHY (MEG) REPORT\n";
        $text .= str_repeat("=", 80) . "\n\n";

        foreach ($reportData as $section => $content) {
            $title = $this->formatSectionTitle($section);
            $text .= strtoupper($title) . "\n";
            $text .= str_repeat("-", 80) . "\n";
            $text .= strip_tags($content) . "\n\n";
        }

        return $text;
    }

    /**
     * Format section key to readable title
     */
    protected function formatSectionTitle(string $key): string
    {
        return ucwords(str_replace('_', ' ', $key));
    }
}
