<?php
// Disable HTML error output
ini_set('display_errors', 0);
error_reporting(0);

// API endpoint to retrieve all currently online users
header('Content-Type: application/json');
header("Cache-Control: no-cache, must-revalidate");

// Check if db.php exists
if (!file_exists('../config/db.php')) {
    echo json_encode([
        'success' => false,
        'error' => 'Database config file not found',
        'users' => []
    ]);
    exit;
}

require_once('../config/db.php');

$users = [];

// Get users who have been active in the last 5 minutes
$sql = "SELECT user_id, user_name, last_activity 
        FROM online_users 
        WHERE last_activity >= DATE_SUB(NOW(), INTERVAL 5 MINUTE)
        ORDER BY user_name ASC";

if ($result = $conn->query($sql)) {
    while ($row = $result->fetch_assoc()) {
        $users[] = [
            'user_id' => $row['user_id'],
            'user_name' => $row['user_name'],
            'last_activity' => $row['last_activity']
        ];
    }
    $result->free();
} else {
    $users = ['error' => 'Failed to fetch online users. Error: ' . $conn->error];
}

$conn->close();

echo json_encode([
    'success' => true, 
    'users' => $users,
    'count' => is_array($users) ? count($users) : 0
]);
?>  