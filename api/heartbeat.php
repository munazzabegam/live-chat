<?php
// API endpoint to update user's last activity timestamp
require_once('../config/db.php');

header('Content-Type: application/json');

$response = ['success' => false, 'message' => 'Invalid request'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $data = json_decode(file_get_contents("php://input"), true);
    
    $userId = isset($data['userId']) ? trim($data['userId']) : '';
    $userName = isset($data['userName']) ? trim($data['userName']) : '';
    
    if (!empty($userId) && !empty($userName)) {
        
        // Use INSERT ... ON DUPLICATE KEY UPDATE to handle both new and existing users
        $sql = "INSERT INTO online_users (user_id, user_name, last_activity) 
                VALUES (?, ?, NOW()) 
                ON DUPLICATE KEY UPDATE 
                user_name = VALUES(user_name), 
                last_activity = NOW()";
        
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("ss", $param_userId, $param_userName);
            
            $param_userId = htmlspecialchars($userId, ENT_QUOTES, 'UTF-8');
            $param_userName = htmlspecialchars($userName, ENT_QUOTES, 'UTF-8');
            
            if ($stmt->execute()) {
                $response['success'] = true;
                $response['message'] = 'Heartbeat recorded';
            } else {
                $response['message'] = 'ERROR: Could not execute query. ' . $stmt->error;
            }
            
            $stmt->close();
        } else {
            $response['message'] = 'ERROR: SQL statement preparation failed.';
        }
    } else {
        $response['message'] = 'Missing userId or userName';
    }
}

$conn->close();
echo json_encode($response);
?>