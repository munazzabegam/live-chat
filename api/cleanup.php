<?php
/**
 * Cleanup Script - Removes inactive users and their messages
 * 
 * This script should be run periodically (e.g., every minute via cron job)
 * Command: * * * * * php /path/to/cleanup.php
 * 
 * Or it can be called via a separate API endpoint
 */

require_once('../config/db.php');

// Set to true if running as a standalone script, false for API
$isStandalone = (php_sapi_name() === 'cli');

if (!$isStandalone) {
    header('Content-Type: application/json');
}

$response = [
    'success' => false,
    'inactive_users_removed' => 0,
    'messages_deleted' => 0,
    'message' => ''
];

try {
    // Start transaction
    $conn->begin_transaction();
    
    // Find inactive users (no activity for more than 5 minutes)
    $findInactiveSQL = "SELECT user_id FROM online_users 
                        WHERE last_activity < DATE_SUB(NOW(), INTERVAL 5 MINUTE)";
    
    $inactiveUsers = [];
    if ($result = $conn->query($findInactiveSQL)) {
        while ($row = $result->fetch_assoc()) {
            $inactiveUsers[] = $row['user_id'];
        }
        $result->free();
    }
    
    if (count($inactiveUsers) > 0) {
        // Create placeholders for prepared statement
        $placeholders = str_repeat('?,', count($inactiveUsers) - 1) . '?';
        
        // Delete messages from inactive users
        $deleteMessagesSQL = "DELETE FROM messages WHERE user_id IN ($placeholders)";
        if ($stmt = $conn->prepare($deleteMessagesSQL)) {
            $types = str_repeat('s', count($inactiveUsers));
            $stmt->bind_param($types, ...$inactiveUsers);
            $stmt->execute();
            $response['messages_deleted'] = $stmt->affected_rows;
            $stmt->close();
        }
        
        // Remove inactive users from online_users table
        $deleteUsersSQL = "DELETE FROM online_users WHERE user_id IN ($placeholders)";
        if ($stmt = $conn->prepare($deleteUsersSQL)) {
            $types = str_repeat('s', count($inactiveUsers));
            $stmt->bind_param($types, ...$inactiveUsers);
            $stmt->execute();
            $response['inactive_users_removed'] = $stmt->affected_rows;
            $stmt->close();
        }
    }
    
    // Commit transaction
    $conn->commit();
    
    $response['success'] = true;
    $response['message'] = "Cleanup completed. Removed {$response['inactive_users_removed']} inactive users and {$response['messages_deleted']} messages.";
    
} catch (Exception $e) {
    // Rollback on error
    $conn->rollback();
    $response['message'] = 'Cleanup failed: ' . $e->getMessage();
}

$conn->close();

// Output response
if ($isStandalone) {
    echo $response['message'] . "\n";
    echo "Inactive users removed: " . $response['inactive_users_removed'] . "\n";
    echo "Messages deleted: " . $response['messages_deleted'] . "\n";
} else {
    echo json_encode($response);
}
?>