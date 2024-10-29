<?php
require_once 'connectdb.php';
require_once 'script.php';

$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Initialize the Script class
$script = new Script($pdo);

// Set the appropriate response headers
header('Content-Type: application/json');

// Call the convertToJson method to get the JSON response
$jsonData = $script->convertToJson();

// Return the JSON response to the client (e.g., Postman)
echo $jsonData;
?>
