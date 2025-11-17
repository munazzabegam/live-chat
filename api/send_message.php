<?php
// Disable HTML error output
ini_set('display_errors', 0);
error_reporting(0);

// API endpoint to handle sending messages and updating user activity
header('Content-Type: application/json');

// Check if db.php exists
if (!file_exists('../config/db.php')) {
    echo json_encode([
        'success' => false,
        'message' => 'Database config file not found'
    ]);
    exit;
}

require_once('../config/db.php');

$response = ['success' => false, 'message' => 'Invalid request'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $data = json_decode(file_get_contents("php://input"), true);

    $userName = isset($data['userName']) ? trim($data['userName']) : '';
    $messageText = isset($data['messageText']) ? trim($data['messageText']) : '';
    $userId = isset($data['userId']) ? trim($data['userId']) : '';

    if (!empty($userName) && !empty($messageText) && !empty($userId)) {
        
        try {
            // Start transaction
            $conn->begin_transaction();
            
            // Insert message
            $sql = "INSERT INTO messages (user_name, user_id, message_text) VALUES (?, ?, ?)";
            
            if ($stmt = $conn->prepare($sql)) {
                $stmt->bind_param("sss", $param_userName, $param_userId, $param_messageText);
                
                $param_userName = htmlspecialchars($userName, ENT_QUOTES, 'UTF-8');
                $param_userId = htmlspecialchars($userId, ENT_QUOTES, 'UTF-8');
                $param_messageText = htmlspecialchars($messageText, ENT_QUOTES, 'UTF-8');

                if ($stmt->execute()) {
                    // Update user's last activity
                    $updateActivitySQL = "INSERT INTO online_users (user_id, user_name, last_activity) 
                                         VALUES (?, ?, NOW()) 
                                         ON DUPLICATE KEY UPDATE 
                                         user_name = VALUES(user_name), 
                                         last_activity = NOW()";
                    
                    if ($stmt2 = $conn->prepare($updateActivitySQL)) {
                        $stmt2->bind_param("ss", $param_userId, $param_userName);
                        $stmt2->execute();
                        $stmt2->close();
                    }
                    
                    $conn->commit();
                    
                    $response['success'] = true;
                    $response['message'] = 'Message sent successfully.';
                } else {
                    $conn->rollback();
                    $response['message'] = 'ERROR: Could not execute query. ' . $stmt->error;
                }

                $stmt->close();
            } else {
                $conn->rollback();
                $response['message'] = 'ERROR: SQL statement preparation failed.';
            }
        } catch (Exception $e) {
            $conn->rollback();
            $response['message'] = 'ERROR: ' . $e->getMessage();
        }
        
    } else {
        $response['message'] = 'Missing required fields (name, message, or user ID).';
    }
}

$conn->close();
echo json_encode($response);
?>