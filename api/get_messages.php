<?php
// Disable HTML error output
ini_set('display_errors', 1);
error_reporting(1);

// API endpoint to retrieve all chat messages
header('Content-Type: application/json');
header("Cache-Control: no-cache, must-revalidate");

// Check if db.php exists
if (!file_exists('../config/db.php')) {
    echo json_encode([
        'success' => false,
        'error' => 'Database config file not found',
        'messages' => []
    ]);
    exit;
}

require_once('../config/db.php');

$messages = [];

// Only get messages from users who are currently online (active in last 5 minutes)
$sql = "SELECT m.user_name, m.user_id, m.message_text, m.timestamp 
        FROM messages m
        INNER JOIN online_users ou ON m.user_id = ou.user_id
        WHERE ou.last_activity >= DATE_SUB(NOW(), INTERVAL 5 MINUTE)
        ORDER BY m.timestamp ASC";

if ($result = $conn->query($sql)) {
    while ($row = $result->fetch_assoc()) {
        // Format timestamp
        $row['timestamp'] = (new DateTime($row['timestamp']))->format('h:i A');
        $messages[] = $row;
    }
    $result->free();
} else {
    $messages = ['error' => 'Database Query Failed. Error: ' . $conn->error];
}

$conn->close();

echo json_encode(['success' => true, 'messages' => $messages]);
?>