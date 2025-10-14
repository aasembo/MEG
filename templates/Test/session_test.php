<?php
/**
 * @var array $output
 */
?>
<!DOCTYPE html>
<html>
<head>
    <title>Session Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .output { background: #f5f5f5; padding: 15px; border-radius: 5px; }
        pre { background: white; padding: 10px; border: 1px solid #ddd; }
    </style>
</head>
<body>
    <h1>Session Test Results</h1>
    <div class="output">
        <h3>Session Information:</h3>
        <pre><?php echo print_r($output, true) ?></pre>
        
        <h3>Actions:</h3>
        <p><a href="/test-session">Set Session Data</a></p>
        <p><a href="/test-session/check">Check Session Data</a></p>
        <p><a href="/admin/login">Test Admin Login (Cross-Role Protection)</a></p>
    </div>
</body>
</html>