<?php
// Endpoint to retrieve all chat messages from the SQL database.
require_once('../config/db.php');

header('Content-Type: application/json');
header("Cache-Control: no-cache, must-revalidate");

$messages = [];
$sql = "SELECT user_name, user_id, message_text, timestamp FROM messages ORDER BY timestamp ASC";

if ($result = $conn->query($sql)) {
    while ($row = $result->fetch_assoc()) {
        // Format timestamp for easier use in JavaScript
        $row['timestamp'] = (new DateTime($row['timestamp']))->format('h:i A');
        $messages[] = $row;
    }
    $result->free();
} else {
    // Return an error message if the query fails (e.g., table doesn't exist)
    $messages = ['error' => 'Database Query Failed. Check SQL table setup. Error: ' . $conn->error];
}

$conn->close();

echo json_encode(['success' => true, 'messages' => $messages]);
?>