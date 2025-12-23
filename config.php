<?php


define('DB_HOST', 'sql100.infinityfree.com'); 
define('DB_USER', 'if0_40537545'); 
define('DB_PASS', 'zf5Abdxb3GI8S'); 
define('DB_NAME', 'if0_40537545_carworkshop'); 


function getDBConnection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    $conn->set_charset("utf8mb4");
    return $conn;
}


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


define('BASE_URL', 'http://Karim.free.nf/');
?>