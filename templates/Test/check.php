<?php
/**
 * Test Redirect Template
 */
?>
<!DOCTYPE html>
<html>
<head>
    <title>Redirect Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        .info { background: #f0f0f0; padding: 15px; margin: 10px 0; }
    </style>
</head>
<body>
    <h1>Hospital Redirect Test</h1>
    
    <div class="error"><?php echo h($message) ?></div>
    
    <div class="info">
        <h3>Current State:</h3>
        <p><strong>Query Hospital:</strong> <?php echo h($queryHospital) ?></p>
        <p><strong>Session Hospital:</strong> <?php echo $hospital ? h($hospital->name . ' (' . $hospital->subdomain . ')') : 'None' ?></p>
        <p><strong>Hospital Status:</strong> <?php echo $hospital ? h($hospital->status) : 'No hospital' ?></p>
    </div>
    
    <h3>Test Links:</h3>
    <ul>
        <li><a href="/test/redirect?hospital=hospital1">Test Hospital1 (Active - should work)</a></li>
        <li><a href="/test/redirect?hospital=hospital2">Test Hospital2 (Inactive - should redirect)</a></li>
    </ul>
    
    <p><em>If hospital2 is working correctly, you should NOT see this page when clicking the hospital2 link.</em></p>
</body>
</html>