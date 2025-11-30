<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>EEG Report</title>
        <style>
            @page {
                size: A4;
                margin: 0; /* No page margins as requested */
            }
            body {
                margin: 0;
                padding: 0;
                font-size:16px;
            }
            .page {
                font-size:16px;
				height:1122px;
				width:794px;
                max-width:100%;
				margin:0;
				padding:0;
				box-sizing: border-box;
				page-break-after: always; /* add break only between pages */
				page-break-inside: avoid; /* keep a page block together */
			}
            .page .page-content {
                width:100%;
				height: 1042px;
                vertical-align: top;    
                padding: 40px;
                
            }
            h1 {
                font-size: 24px;
                margin: 0;
                padding-top:15px;
                padding-bottom:15px;
            }
            .va-top {
                vertical-align: top;
            }
            .text-center {
                text-align: center;
            }
            td {
                vertical-align: top;
            }
            .w-100 {
                width: 100%;
            }
            table {
				border-collapse: collapse;
				border-spacing: 0;
			}
            td, th { padding: 0; }
            .h-40 {
                height: 40px;
            }
            .h-20 {
                height: 20px;
            }
            h2 {
                font-size: 20px;
                margin: 0;
                padding-top:10px;
                padding-bottom:10px;
            }
            .fw-bold {
                font-weight: bold;
            }
        </style>
    </head>
    <div class="page">
        <table class="page-content">
            <tr>
                <td>
                    <table class="w-100">
                        <tr>
                            <td class="va-top">
                                <h1 class="text-center fw-bold">Magnetoencephalography Report (MEG) </h1>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <table>
                                    <tr>
                                        <td>
                                            Name: <?php echo  $this->PatientMask->displayName($case->patient_user) ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            Date of Birth: <?php echo  $this->PatientMask->displayDob($case->patient_user) ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            MRN:<?php echo  $this->PatientMask->displayMrn($case->patient_user) ?> -FIN: <?php echo  $this->PatientMask->displayFin($case->patient_user) ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            Date of Study: <?php echo  $case->date ? h($case->date->format('m/d/Y')) : 'XX/XX/XXXX' ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            MEG ID: <?php 
                                                $caseId = (string)$case->id;
                                                $megId = str_repeat('X', max(0, 6 - strlen($caseId))) . $caseId;
                                                echo h('CASE_'.$megId);
                                            ?>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                        <tr>
                            <td class="h-40">&nbsp;</td>
                        </tr>
                        <tr>
                            <td>
                                <b>Patient History:</b>
                            </td>
                        </tr>
                        <tr>
                            <td>None</td>
                        </tr>
                        <tr>
                            <td class="h-40">&nbsp;</td>
                        </tr>
                        <tr>
                            <td><h2 class="fw-bold">MEG RECORDINGS: </h2></td>
                        </tr>
                        <tr>
                            <td>
                                <?php echo $case->notes; ?>
                                <?php echo $case->symptoms; ?> 
                                This study was performed <strong><?php echo  !empty($case->sedations) ? 'with' : 'without' ?></strong> sedation.
                            </td>
                        </tr>
                        <tr>
                            <td class="h-20"></td>
                        </tr>
                        <tr>
                            <td>The following procedures were performed:
                            <?php if (!empty($case->cases_exams_procedures)): ?>
                                <br />
                                <ul>
                                    <?php foreach ($case->cases_exams_procedures as $procedure): ?>
                                       <li class="mt-1 mb-1">
                                            <?php echo  h($cep->exams_procedure->exam->name ?? 'MEG Recording') ?>/<?php echo  h($cep->exams_procedure->procedure->name ?? 'Standard MEG Protocol') ?>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <td class="h-40">&nbsp;</td>
                        </tr>
                        <tr>
                            <td>
                                MSI Technical Notes:  <?php echo $case->notes; ?>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>
    <div class="page">
        <table class="page-content">
            <tr>
                <td>
                    <table class="w-100">
                        <tr>
                            <td><h2 class="fw-bold">TECHNICAL DESCRIPTION OF PROCEDURES: </h2></td>
                        </tr>
                        <tr>
                            <td>
                                <?php if (!empty($case->cases_exams_procedures)): ?>
                                    <ul>
                                        <?php foreach ($case->cases_exams_procedures as $cep): ?>
                                            <li>
                                                <strong><em><?php echo  h($cep->exams_procedure->exam->name ?? 'MEG Recording') ?>/<?php echo  h($cep->exams_procedure->procedure->name ?? 'Standard MEG Protocol') ?>:</em></strong><br>
                                                &nbsp;&nbsp;&nbsp;&nbsp;<?php echo  h($cep->exams_procedure->notes ?? 'Standard MEG recording protocol performed according to institutional guidelines.') ?>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <td class="h-40">&nbsp;</td>
                        </tr>
                        <tr>
                            <td><h2 class="fw-bold">MEG RECORDINGS: </h2></td>
                        </tr>
                        <tr>
                            <td>
                                <?php if (!empty($case->cases_exams_procedures)): ?>
                                    <ul >
                                        <?php foreach ($case->cases_exams_procedures as $cep): ?>
                                            <li>
                                                <strong><em><?php echo  h($cep->exams_procedure->exam->name ?? 'MEG Recording') ?>/<?php echo  h($cep->exams_procedure->procedure->name ?? 'Standard MEG Protocol') ?>:</em></strong><br>
                                                &nbsp;&nbsp;&nbsp;&nbsp;<?php echo  h($cep->notes ?? 'Normal responses recorded and successfully localized.') ?>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php endif; ?>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>
<body>
