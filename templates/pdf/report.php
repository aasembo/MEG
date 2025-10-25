<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        @page {
            margin: 1in;
        }
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 11pt;
            line-height: 1.6;
            color: #000;
            margin: 0;
            padding: 0;
        }
        .header {
            text-align: center;
            margin-bottom: 25px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }
        .header h1 {
            font-size: 16pt;
            font-weight: bold;
            margin: 0 0 5px 0;
        }
        .header h2 {
            font-size: 12pt;
            font-weight: normal;
            margin: 0;
        }
        .section {
            margin-bottom: 18px;
            page-break-inside: avoid;
        }
        .section-title {
            font-weight: bold;
            font-size: 12pt;
            margin-bottom: 8px;
            margin-top: 5px;
            text-decoration: underline;
        }
        .patient-info {
            margin-bottom: 20px;
            line-height: 1.4;
        }
        .info-row {
            margin-bottom: 4px;
        }
        .label {
            font-weight: bold;
            display: inline-block;
            width: 160px;
        }
        .value {
            display: inline;
        }
        p {
            margin: 8px 0;
            text-align: justify;
            line-height: 1.6;
        }
        .indented {
            margin-left: 25px;
        }
        .signature-block {
            margin-top: 40px;
            page-break-inside: avoid;
        }
        .signature-line {
            border-top: 1px solid #000;
            width: 300px;
            margin-top: 30px;
        }
        ul {
            margin: 8px 0;
            padding-left: 35px;
            line-height: 1.5;
        }
        li {
            margin-bottom: 5px;
        }
        strong {
            font-weight: bold;
        }
        em {
            font-style: italic;
        }
        .document-findings {
            margin: 12px 0;
        }
        img {
            max-width: 100%;
            height: auto;
            display: block;
            margin: 15px auto;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1><?= h($hospital->name ?? 'Medical Center') ?></h1>
        <h2><?= h($report_name ?? 'Magnetoencephalography Report (MEG)') ?></h2>
    </div>

    <!-- Patient Information -->
    <div class="patient-info">
        <div class="info-row">
            <span class="label">Name:</span>
            <span class="value"><?= h($patientLastName) ?>, <?= h($patientFirstName) ?></span>
        </div>
        <div class="info-row">
            <span class="label">Date of Birth:</span>
            <span class="value"><?= h($patientDob) ?></span>
        </div>
        <div class="info-row">
            <span class="label">MRN:</span>
            <span class="value"><?= h($patientMrn) ?> &nbsp;&nbsp;&nbsp; <strong>FIN:</strong> <?= h($patientFin) ?></span>
        </div>
        <div class="info-row">
            <span class="label">Date of Study:</span>
            <span class="value"><?= h($studyDate) ?></span>
        </div>
        <div class="info-row">
            <span class="label">Referring Physician:</span>
            <span class="value"><?= h($referringPhysician) ?></span>
        </div>
        <div class="info-row">
            <span class="label">MEG ID:</span>
            <span class="value"><?= h($megId) ?></span>
        </div>
    </div>

    <!-- Dynamic Content: Support both AI-generated and traditional reports -->
    <?php if (isset($sections) && !empty($sections)): ?>
        <!-- AI-Generated Report Structure -->
        <?php foreach ($sections as $section): ?>
            <div class="section">
                <div class="section-title"><?= h($section['title']) ?>:</div>
                
                <!-- Main section content -->
                <?php if (!empty($section['content'])): ?>
                    <?= $section['content'] ?>
                <?php endif; ?>
                
                <!-- Subsections -->
                <?php if (!empty($section['subsections'])): ?>
                    <?php foreach ($section['subsections'] as $subsection): ?>
                        <div class="indented">
                            <p><strong><?= h($subsection['title']) ?>:</strong></p>
                            <?= $subsection['content'] ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <!-- Traditional Report Structure (Fallback) -->
        <!-- Patient History -->
        <div class="section">
            <div class="section-title">Patient History:</div>
            <p><?= h($age ?? 'N/A') ?>-year-old <?= h($gender ?? 'patient') ?> <?= !empty($medications) ? 'on ' . h($medications) : 'with no reported medications' ?>.</p>
        </div>

        <!-- MEG Recordings -->
        <div class="section">
            <div class="section-title">MEG Recordings:</div>
            <p>
                The patient underwent magnetoencephalography (MEG) <?= h($sedationText ?? 'without sedation') ?>. 
                The following procedures were performed:
            </p>
            <ul>
                <?php if (isset($proceduresList) && !empty($proceduresList)): ?>
                    <?php foreach ($proceduresList as $procedure): ?>
                        <li><?= h($procedure) ?></li>
                    <?php endforeach; ?>
                <?php else: ?>
                    <li>Standard MEG recording</li>
                <?php endif; ?>
            </ul>
        </div>

        <!-- Technical Note -->
        <div class="section">
            <div class="section-title">Technical Note:</div>
            <p>
                The MEG was recorded using a whole-head Neuromag Triux 306-channel biomagnetometer system. 
                The sensor array consists of 102 sensor elements, each with two orthogonal planar gradiometers 
                and one magnetometer, uniformly distributed over the entire scalp. Continuous head position 
                monitoring was employed throughout the recording to ensure data quality. The patient's head 
                position relative to the MEG sensors was determined using four head position indicator (HPI) coils.
            </p>
        </div>

        <!-- Technical Descriptions based on procedures -->
        <?php if (isset($technicalDescriptions) && !empty($technicalDescriptions)): ?>
            <?php foreach ($technicalDescriptions as $description): ?>
                <div class="section">
                    <?= $description ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <!-- MSI Conclusions -->
        <div class="section">
            <div class="section-title">MSI Conclusions:</div>
            <?php if (isset($msiConclusions) && !empty($msiConclusions)): ?>
                <?php foreach ($msiConclusions as $conclusion): ?>
                    <p><?= $conclusion ?></p>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Detailed analysis and conclusions will be provided following comprehensive review of all acquired data.</p>
            <?php endif; ?>
        </div>

        <!-- Additional Notes -->
        <?php if (isset($additionalNotes) && !empty($additionalNotes)): ?>
            <div class="section">
                <div class="section-title">Additional Notes:</div>
                <p><?= nl2br(h($additionalNotes)) ?></p>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <!-- Signature Block -->
    <div class="signature-block">
        <p><strong>Electronically signed by:</strong></p>
        <p>
            Clifford Soren Calley, PhD, D.ABMPP<br>
            Board Certified Clinical Neuropsychologist<br>
            MEG Program Director
        </p>
        <p>Date: <?= date('F d, Y') ?></p>
    </div>
</body>
</html>
