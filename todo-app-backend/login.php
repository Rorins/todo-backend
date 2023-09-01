<?php
session_start();
// Include database connection
include 'database.php';

header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Origin: http://127.0.0.1:5173");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Get POST data
$data = json_decode(file_get_contents("php://input"));

$email = filter_var($data->email, FILTER_VALIDATE_EMAIL);
$password = $data->password;

//Query to get users 
$query = "SELECT * FROM users WHERE email = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if ($user && password_verify($password, $user['password'])) {
    //sending it out to the frontend to save it to local storage
    $response = [
        'status' => 'success',
        'message' => 'Login successful',
        'user' => [
            'id' => $user['id'],
        ],
    ];

    http_response_code(200);
} else {
    http_response_code(401);
    $response["status"] = "error";
    $response["message"] = "Login failed";;
}

echo json_encode($response);

// Closing statement and connection
$stmt->close();
$conn->close();
