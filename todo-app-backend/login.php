<?php

// Include database connection
include_once './config/database.php';
include_once './config/token.php';

header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Origin: http://127.0.0.1:5173");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Get POST data from the frontend
$receivedData = file_get_contents("php://input");
error_log("Received data: " . $receivedData);
$data = json_decode($receivedData);
error_log("Decoded data: " . print_r($data, true));

//email validation, if it's not filtered I send out a json response to display
$email = filter_var($data->email, FILTER_VALIDATE_EMAIL);
if (!$email) {
    http_response_code(400);
    $response = [
        'status' => 'error',
        'message' => 'Invalid email format',
    ];
    echo json_encode($response);
    exit;
}

//password 
$password = $data->password;

//QUERY
$query = "SELECT * FROM users WHERE email = ?";
//prepared statement to prevent sql injection
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $email);
$stmt->execute();
//saving the result in the user variable
$result = $stmt->get_result();
$user = $result->fetch_assoc();

//SENDING TOKEN BACK

//verifying password by comparing plain password to hashed password in the db
if ($user && password_verify($password, $user['password'])) {
    //token generation
    $token = generateToken($user['id']);

    //sending it out to the frontend to save it to local storage
    $response = [
        'status' => 'success',
        'message' => 'Login successful',
        'user' => [
            'id' => $user['id'],
        ],
        'token' => $token,
    ];

    http_response_code(200);
} else {
    http_response_code(401);
    $response["status"] = "error";
    $response["message"] = "Login failed";
}

echo json_encode($response);

// Closing statement and connection
$stmt->close();
$conn->close();
