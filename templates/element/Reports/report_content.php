<?php
/**
 * Report Content Template Element
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Case $case
 */
?>
<div style="font-family: Times, serif; font-size: 12pt; line-height: 1.6; color: #000; max-width: 8.5in; margin: 0 auto;">
    
    <!-- Header -->
    <div style="text-align: center; margin-bottom: 30px;">
        <h1 style="font-size: 16pt; font-weight: bold; margin: 0; text-decoration: underline;">
            Magnetoencephalography Report (MEG)
        </h1>
    </div>
    
    <!-- Patient Demographics Section -->
    <div style="margin-bottom: 20px;">
        <p style="margin: 3px 0;"><strong>Name:</strong> <?= h($case->patient_user->last_name ?? '') ?>, <?= h($case->patient_user->first_name ?? '') ?></p>
        <p style="margin: 3px 0;"><strong>Date of Birth:</strong> <?= $case->patient_user->date_of_birth ? h($case->patient_user->date_of_birth->format('m/d/Y')) : 'XX/XX/XXXX' ?></p>
        <p style="margin: 3px 0;"><strong>MRN:</strong> <?= h($case->patient_user->medical_record_number ?? 'XXXXX') ?> <strong>-FIN:</strong> <?= h($case->patient_user->financial_record_number ?? 'XXXXX') ?></p>
        <p style="margin: 3px 0;"><strong>Date of Study:</strong> <?= $case->date ? h($case->date->format('m/d/Y')) : 'XX/XX/XXXX' ?></p>
        <p style="margin: 3px 0;"><strong>Referring Physician:</strong> <?= h($case->user->first_name ?? '') ?> <?= h($case->user->last_name ?? '') ?>, MD</p>
        <p style="margin: 3px 0;"><strong>MEG ID:</strong> <?php 
            $caseId = (string)$case->id;
            $megId = str_repeat('X', max(0, 6 - strlen($caseId))) . $caseId;
            echo h('CASE_'.$megId);
        ?></p>
    </div>
    
    <!-- Patient History Section -->
    <div style="margin-bottom: 20px;">
        <p style="margin: 8px 0 4px 0;"><strong>Patient History:</strong></p>
        <?php 
        $age = 'XX';
        $gender = 'XXX';
        if ($case->patient_user->date_of_birth) {
            $birthDate = $case->patient_user->date_of_birth;
            $age = date('Y') - $birthDate->format('Y');
            if (date('md') < $birthDate->format('md')) {
                $age--;
            }
        }
        if ($case->patient_user->gender) {
            $gender = $case->patient_user->gender === 'male' ? 'male' : ($case->patient_user->gender === 'female' ? 'female' : $case->patient_user->gender);
        }
        ?>
        <p style="margin: 3px 0;"><?= h($age) ?> yo <?= h($gender) ?>.</p>
        <p style="margin: 8px 0 0 0;"><strong>Medication:</strong></p>
        <p style="margin: 3px 0;"><?= h($case->patient_user->medications ?? '') ?></p>
    </div>
    
    <!-- MEG Recordings Section -->
    <div style="margin-bottom: 20px;">
        <p style="margin: 8px 0 4px 0;"><strong>MEG RECORDINGS:</strong></p>
        <p style="margin: 3px 0; text-align: justify;">
            <?= h($case->notes ?? $case->symptoms ?? 'Magnetoencephalography (MEG) recording and magnetic source imaging (MSI) were requested as part of a presurgical evaluation to noninvasively localize epileptiform discharges and map functional cortical areas.') ?> 
            This study was performed <strong><?= !empty($case->sedations) ? 'with' : 'without' ?></strong> sedation.
        </p>
        
        <!-- Procedures List -->
        <p style="margin: 8px 0 4px 0;">The following procedures were performed:</p>
        <?php if (!empty($case->cases_exams_procedures)): ?>
            <ul style="margin: 0; padding-left: 40px;">
                <?php foreach ($case->cases_exams_procedures as $cep): ?>
                    <li style="margin: 2px 0;">
                        <?= h($cep->exams_procedure->exam->name ?? 'MEG Recording') ?>/<?= h($cep->exams_procedure->procedure->name ?? 'Standard MEG Protocol') ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
    
    <!-- MSI Technical Note -->
    <div style="margin-bottom: 20px;">
        <p style="margin: 8px 0 4px 0;"><strong><em>MSI Technical Note:</em></strong></p>
        <p style="margin: 3px 0; text-align: justify;">
            Spontaneous and evoked brain activity were recorded as the patient rested quietly in the supine position. 
            Cortical electrical fields were recorded using a whole-head 306-channel gradiometer/magnetometer system 
            (Neuromag Triux, Elekta, Inc.). Electroencephalography (EEG) was recorded simultaneously using the 
            international 10-20 electrode placement system.
        </p>
        
        <p style="margin: 8px 0 0 0; text-align: justify;">
            The source distributions were analyzed utilizing an equivalent current dipole model with the best fit 
            judged by statistical criteria of goodness of fit, confidence volume, and signal to noise ratio. Due to 
            the limitations of ECD in localizing language and motor functions, we employed L2-norm and L1-norm 
            approaches as complementary methods and reference tools. Display of EEG signals on the accompanying 
            report were for visualization and reference purposes only and were not used in the localization algorithms. 
            Dipoles were then projected onto the patient's structural MRI to generate magnetic source images. The MEG 
            recordings and MRI images were coregistered using fiducials placed on anatomical landmarks on the scalp.
        </p>
    </div>
    
    <!-- TECHNICAL DESCRIPTION OF PROCEDURES Section -->
    <div style="margin-bottom: 20px;">
        <p style="margin: 8px 0 4px 0;"><strong>TECHNICAL DESCRIPTION OF PROCEDURES:</strong></p>
        <?php if (!empty($case->cases_exams_procedures)): ?>
            <ul style="margin: 0; padding-left: 20px; list-style-type: disc;">
                <?php foreach ($case->cases_exams_procedures as $cep): ?>
                    <li style="margin: 8px 0; text-align: justify;">
                        <strong><em><?= h($cep->exams_procedure->exam->name ?? 'MEG Recording') ?>/<?= h($cep->exams_procedure->procedure->name ?? 'Standard MEG Protocol') ?>:</em></strong><br>
                        &nbsp;&nbsp;&nbsp;&nbsp;<?= h($cep->exams_procedure->notes ?? 'Standard MEG recording protocol performed according to institutional guidelines.') ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
    
    <!-- MSI CONCLUSIONS Section -->
    <div style="margin-bottom: 20px;">
        <p style="margin: 8px 0 4px 0;"><strong>MSI CONCLUSIONS:</strong></p>
        
        <?php if (!empty($case->cases_exams_procedures)): ?>
            <ul style="margin: 0; padding-left: 20px; list-style-type: disc;">
                <?php foreach ($case->cases_exams_procedures as $cep): ?>
                    <li style="margin: 8px 0; text-align: justify;">
                        <strong><em><?= h($cep->exams_procedure->exam->name ?? 'MEG Recording') ?>/<?= h($cep->exams_procedure->procedure->name ?? 'Standard MEG Protocol') ?>:</em></strong><br>
                        &nbsp;&nbsp;&nbsp;&nbsp;<?= h($cep->notes ?? 'Normal responses recorded and successfully localized.') ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
    <!-- Signature Block -->
    <div style="margin-top: 40px; page-break-inside: avoid;">
        <table style="width: 100%; border: none; font-size: 11pt;">
            <tr>
                <td style="border: none; vertical-align: top; width: 50%;">
                    <p style="margin: 0 0 5px 0;">Electronically signed by:</p>
                    <p style="margin: 0 0 5px 0;"><strong>Jane Doe, MD</strong></p>
                    <p style="margin: 0 0 5px 0;">Neurologist</p>
                    <p style="margin: 0;">Date: <?= date('m/d/Y') ?></p>
                </td>
                <td style="border: none; vertical-align: top; width: 50%; text-align: right;">
                    <p style="margin: 0 0 5px 0;">Report Generated: <?= date('m/d/Y h:i A') ?></p>
                    <p style="margin: 0;">MEG ID: <?php 
                        $caseId = (string)$case->id;
                        $megId = str_repeat('X', max(0, 6 - strlen($caseId))).$caseId;
                        echo h('CASE_'.$megId);
                    ?></p>
                </td>
            </tr>
        </table>
    </div>
    
</div>