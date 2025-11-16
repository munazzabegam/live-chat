<?php
// Endpoint to handle sending messages and storing them in the SQL database.
require_once('../config/db.php');

header('Content-Type: application/json');

$response = ['success' => false, 'message' => 'Invalid request'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Read JSON data from the request body
    $data = json_decode(file_get_contents("php://input"), true);

    // Validate incoming data
    $userName = isset($data['userName']) ? trim($data['userName']) : '';
    $messageText = isset($data['messageText']) ? trim($data['messageText']) : '';
    $userId = isset($data['userId']) ? trim($data['userId']) : '';

    if (!empty($userName) && !empty($messageText) && !empty($userId)) {
        
        // Prepare an insert statement
        $sql = "INSERT INTO messages (user_name, user_id, message_text) VALUES (?, ?, ?)";
        
        if ($stmt = $conn->prepare($sql)) {
            // Bind variables to the prepared statement as parameters
            $stmt->bind_param("sss", $param_userName, $param_userId, $param_messageText);
            
            // Set parameters (Sanitize before binding)
            $param_userName = htmlspecialchars($userName, ENT_QUOTES, 'UTF-8');
            $param_userId = htmlspecialchars($userId, ENT_QUOTES, 'UTF-8');
            $param_messageText = htmlspecialchars($messageText, ENT_QUOTES, 'UTF-8');

            // Attempt to execute the prepared statement
            if ($stmt->execute()) {
                $response['success'] = true;
                $response['message'] = 'Message sent successfully.';
            } else {
                $response['message'] = 'ERROR: Could not execute query. ' . $stmt->error;
            }

            $stmt->close();
        } else {
            $response['message'] = 'ERROR: SQL statement preparation failed.';
        }
    } else {
        $response['message'] = 'Missing required fields (name, message, or user ID).';
    }
}

$conn->close();
echo json_encode($response);
?>