<?php
// Include database connection
include 'database.php';

header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Origin: http://127.0.0.1:5173");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

// Get POST data

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($method === 'POST') {
    $receivedData = file_get_contents("php://input");
    error_log("Received data: " . $receivedData);
    error_log("Decoded data: " . print_r($inputData, true));
    $inputData = json_decode($receivedData);

    if ($inputData === null) {
        http_response_code(400);
        echo json_encode(array("status" => "error", "message" => "Invalid JSON data."));
        exit;
    }

    $email = filter_var($inputData->email);
    $password = password_hash($inputData->password, PASSWORD_BCRYPT);
    error_log("Email: " . $email);
    error_log("Password: " . $password);

    $checkQuery = "SELECT COUNT(*) FROM users WHERE email = ?";
    $checkStmt = $conn->prepare($checkQuery);
    $checkStmt->bind_param("s", $email);
    $checkStmt->execute();
    $checkStmt->bind_result($count);
    $checkStmt->fetch();
    $checkStmt->close();

    if ($count > 0) {
        http_response_code(400);
        echo json_encode(array("status" => "error", "message" => "Email already exists."));
        exit;
    }

    //insert in USERS table
    $query = "INSERT INTO users (email, password) VALUES (?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $email, $password);

    //sending json back with response code
    if ($stmt->execute()) {
        $userId = $stmt->insert_id; //id of new user
        $response = [
            'status' => 'success',
            'message' => 'Login successful',
            'user' => [
                'id' => $userId,
            ],
        ];
        http_response_code(200);
    } else {
        $response["status"] = "error";
        $response["message"] = "Registration failed.";
        http_response_code(500);
    }

    echo json_encode($response);
}


//closing statement and connection
$stmt->close();
$conn->close();
