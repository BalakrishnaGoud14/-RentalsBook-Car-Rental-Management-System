<?php

// Enter DB connection details here
$servername = '';
$dbname = '';
$username = '';
$password = '';


// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
