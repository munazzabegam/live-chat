<?php
/**
 * API Test File - Use this to diagnose issues
 * Place in your live-chat root directory and access via browser
 */

// Disable error display in HTML (we want JSON only)
ini_set('display_errors', 0);
error_reporting(E_ALL);

header('Content-Type: application/json');

$results = [
    'timestamp' => date('Y-m-d H:i:s'),
    'tests' => []
];

// Test 1: Check if config file exists
$configPath = __DIR__ . '/config/db.php';
$results['tests']['config_file_exists'] = file_exists($configPath);
$results['config_path'] = $configPath;

// Test 2: Try to include config
if ($results['tests']['config_file_exists']) {
    try {
        require_once($configPath);
        $results['tests']['config_loaded'] = true;
        
        // Test 3: Check database connection
        if (isset($conn)) {
            $results['tests']['db_connection'] = !$conn->connect_error;
            if ($conn->connect_error) {
                $results['db_error'] = $conn->connect_error;
            } else {
                $results['db_status'] = 'Connected successfully';
                
                // Test 4: Check if tables exist
                $tables = ['messages', 'online_users'];
                foreach ($tables as $table) {
                    $result = $conn->query("SHOW TABLES LIKE '$table'");
                    $results['tests']["table_$table"] = ($result && $result->num_rows > 0);
                }
                
                // Test 5: Count records
                $messageCount = $conn->query("SELECT COUNT(*) as count FROM messages");
                if ($messageCount) {
                    $row = $messageCount->fetch_assoc();
                    $results['message_count'] = $row['count'];
                }
                
                $userCount = $conn->query("SELECT COUNT(*) as count FROM online_users");
                if ($userCount) {
                    $row = $userCount->fetch_assoc();
                    $results['online_user_count'] = $row['count'];
                }
            }
        } else {
            $results['tests']['db_connection'] = false;
            $results['db_error'] = 'Connection object not created';
        }
    } catch (Exception $e) {
        $results['tests']['config_loaded'] = false;
        $results['config_error'] = $e->getMessage();
    }
} else {
    $results['tests']['config_loaded'] = false;
}

// Test 6: Check API files
$apiFiles = [
    'send_message.php',
    'get_messages.php',
    'heartbeat.php',
    'get_online_users.php',
    'cleanup.php'
];

$results['api_files'] = [];
foreach ($apiFiles as $file) {
    $filePath = __DIR__ . '/api/' . $file;
    $results['api_files'][$file] = [
        'exists' => file_exists($filePath),
        'readable' => is_readable($filePath),
        'path' => $filePath
    ];
}

// Test 7: PHP version and extensions
$results['php_info'] = [
    'version' => phpversion(),
    'mysqli_loaded' => extension_loaded('mysqli'),
    'json_loaded' => extension_loaded('json')
];

echo json_encode($results, JSON_PRETTY_PRINT);
?>