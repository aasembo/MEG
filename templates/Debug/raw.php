<?php
/**
 * Raw Debug Template
 */
?>
<!DOCTYPE html>
<html>
<head>
    <title>Raw Debug Test</title>
    <style>
        body { font-family: monospace; margin: 20px; }
        .info { margin: 10px 0; padding: 10px; background: #f0f0f0; }
    </style>
</head>
<body>
    <h1>Raw Debug Test</h1>
    
    <div class="info">
        <strong>Host:</strong> <?php echo h($host) ?>
    </div>
    
    <div class="info">
        <strong>Query Hospital:</strong> <?php echo h($queryHospital) ?>
    </div>
    
    <div class="info">
        <strong>Current URL:</strong> <?php echo h($currentUrl) ?>
    </div>
    
    <div class="info">
        <strong>Status:</strong> This page loaded successfully without redirect
    </div>
    
    <h3>Test Links:</h3>
    <ul>
        <li><a href="?hospital=hospital1">Test Hospital1</a></li>
        <li><a href="?hospital=hospital2">Test Hospital2 (should redirect if logic working)</a></li>
    </ul>
</body>
</html>