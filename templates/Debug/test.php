<?php
/**
 * Debug Test Template
 * @var array $debugInfo
 */
?>
<!DOCTYPE html>
<html>
<head>
    <title>Debug Hospital Redirection</title>
    <style>
        body { font-family: monospace; margin: 20px; }
        .debug-section { margin: 20px 0; padding: 15px; border: 1px solid #ccc; }
        .redirect-needed { background-color: #ffebee; border-color: #f44336; }
        .no-redirect { background-color: #e8f5e8; border-color: #4caf50; }
        pre { background: #f5f5f5; padding: 10px; overflow: auto; }
    </style>
</head>
<body>
    <h1>Hospital Redirection Debug</h1>
    
    <div class="debug-section <?php echo $debugInfo['should_redirect'] ? 'redirect-needed' : 'no-redirect' ?>">
        <h2>Status: <?php echo $debugInfo['should_redirect'] ? 'SHOULD REDIRECT' : 'NO REDIRECT NEEDED' ?></h2>
        
        <?php if ($debugInfo['should_redirect']): ?>
            <p><strong>Redirect URL:</strong> <?php echo h($debugInfo['redirect_url']) ?></p>
            <p><a href="<?php echo h($debugInfo['redirect_url']) ?>">Click here to test redirect</a></p>
        <?php endif; ?>
    </div>
    
    <div class="debug-section">
        <h3>Request Information</h3>
        <ul>
            <li><strong>Host:</strong> <?php echo h($debugInfo['request_host']) ?></li>
            <li><strong>Extracted Subdomain:</strong> <?php echo h($debugInfo['extracted_subdomain']) ?></li>
            <li><strong>Query Hospital Parameter:</strong> <?php echo h($debugInfo['query_hospital']) ?></li>
            <li><strong>Final Subdomain Used:</strong> <?php echo h($debugInfo['final_subdomain']) ?></li>
        </ul>
    </div>
    
    <div class="debug-section">
        <h3>Hospital Information</h3>
        <?php if ($debugInfo['hospital_found']): ?>
            <ul>
                <li><strong>Name:</strong> <?php echo h($debugInfo['hospital_found']['name']) ?></li>
                <li><strong>Subdomain:</strong> <?php echo h($debugInfo['hospital_found']['subdomain']) ?></li>
                <li><strong>Status:</strong> <?php echo h($debugInfo['hospital_found']['status']) ?></li>
                <li><strong>ID:</strong> <?php echo h($debugInfo['hospital_found']['id']) ?></li>
            </ul>
        <?php else: ?>
            <p><em>No hospital found for subdomain "<?php echo h($debugInfo['final_subdomain']) ?>"</em></p>
        <?php endif; ?>
    </div>
    
    <div class="debug-section">
        <h3>Session Information</h3>
        <?php if ($debugInfo['session_hospital']): ?>
            <ul>
                <li><strong>Session Hospital:</strong> <?php echo h($debugInfo['session_hospital']['name']) ?></li>
                <li><strong>Session Status:</strong> <?php echo h($debugInfo['session_hospital']['status']) ?></li>
            </ul>
        <?php else: ?>
            <p><em>No hospital in session</em></p>
        <?php endif; ?>
    </div>
    
    <div class="debug-section">
        <h3>Full Debug Data</h3>
        <pre><?php echo h(json_encode($debugInfo, JSON_PRETTY_PRINT)) ?></pre>
    </div>
    
    <div class="debug-section">
        <h3>Test Links</h3>
        <ul>
            <li><a href="?hospital=hospital1">Test Hospital1 (Active)</a></li>
            <li><a href="?hospital=hospital2">Test Hospital2 (Inactive)</a></li>
            <li><a href="/debug/force-redirect">Force Redirect Test</a></li>
        </ul>
    </div>
</body>
</html>