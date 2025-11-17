<?php
// Database configuration and connection setup
// !!! IMPORTANT: Update these constants with your actual SQL credentials !!!
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root'); 
define('DB_PASSWORD', ''); 
define('DB_NAME', 'live_chat');

// Attempt to connect to MySQL database
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Check connection
if($conn === false){
    die("ERROR: Could not connect. " . $conn->connect_error);
}


// Set character set to UTF-8
$conn->set_charset("utf8mb4");
?>