<?php
/**
 * Print Layout
 * Simple layout for printing reports
 * @var \App\View\AppView $this
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $this->fetch('title') ?></title>
    
    <!-- Bootstrap 5 CSS for basic styling -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Print-specific styles -->
    <style>
        @media print {
            .no-print {
                display: none !important;
            }
            
            body {
                font-family: 'Times New Roman', serif !important;
                font-size: 12pt;
                line-height: 1.4;
                color: black !important;
                background: white !important;
                margin: 0;
                padding: 0;
            }
            
            .container-fluid {
                padding: 0 !important;
                margin: 0 !important;
                max-width: none !important;
            }
            
            .card, .card-body {
                border: none !important;
                box-shadow: none !important;
                background: white !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            
            h1, h2, h3, h4, h5, h6 {
                page-break-after: avoid;
                color: black !important;
            }
            
            p {
                orphans: 3;
                widows: 3;
            }
            
            table {
                page-break-inside: avoid;
                border-collapse: collapse;
            }
            
            .page-break {
                page-break-before: always;
            }
            
            /* Hide all Bootstrap utility classes that might interfere */
            .btn, .badge, .alert {
                display: none !important;
            }
        }
        
        @media screen {
            body {
                font-family: 'Times New Roman', serif;
                line-height: 1.6;
                background-color: #f8f9fa;
            }
            
            .print-container {
                max-width: 8.5in;
                margin: 0 auto;
                background: white;
                padding: 1in;
                min-height: 11in;
                box-shadow: 0 0 20px rgba(0,0,0,0.1);
            }
        }
    </style>
    
    <?= $this->fetch('meta') ?>
    <?= $this->fetch('css') ?>
</head>
<body>
    <div class="print-container">
        <?= $this->Flash->render() ?>
        <?= $this->fetch('content') ?>
    </div>
    
    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <?= $this->fetch('script') ?>
</body>
</html>