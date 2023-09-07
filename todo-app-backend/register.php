<?php
// Include database connection and token
include './config/database.php';
include './config/token.php';

header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Origin: http://127.0.0.1:5173");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Get POST data
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $receivedData = file_get_contents("php://input");
    error_log("Received data: " . $receivedData);
    error_log("Decoded data: " . print_r($inputData, true));
    $inputData = json_decode($receivedData);

    //input verification
    if ($inputData === null) {
        http_response_code(400);

        $response = [
            'status' => 'error',
            'message' => 'Invalid data',
        ];
        echo json_encode($response);
        exit;
    }

    //email verification
    $email = filter_var($inputData->email, FILTER_VALIDATE_EMAIL);
    if (!$email) {
        http_response_code(400);
        $response = [
            'status' => 'error',
            'message' => 'Invalid email format',
        ];
        echo json_encode($response);
        exit;
    }

    //password hashing
    $password = password_hash($inputData->password, PASSWORD_BCRYPT);
    error_log("Email: " . $email);
    error_log("Password: " . $password);

    //counting rows where email matches if they exist we give back an error
    $checkQuery = "SELECT COUNT(*) FROM users WHERE email = ?";
    $checkStmt = $conn->prepare($checkQuery);
    $checkStmt->bind_param("s", $email);
    $checkStmt->execute();
    $checkStmt->bind_result($count);
    $checkStmt->fetch();

    $checkStmt->close();

    if ($count > 0) {
        http_response_code(400);
        $response = [
            'status' => 'error',
            'message' => 'Email already exists',
        ];
        echo json_encode($response);
        exit;
    }

    //insert in USERS table
    $query = "INSERT INTO users (email, password) VALUES (?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $email, $password);

    //sending json back with response code
    //if the statement is successfull user is registered
    if ($stmt->execute()) {

        //getting d for token generation
        $userId = $stmt->insert_id;
        //token generation
        $token = generateToken($userId);

        $response = [
            'status' => 'success',
            'message' => 'Login successful',
            'user' => [
                'id' => $userId,
            ],
            'token' => $token,
        ];
        http_response_code(200);
    } else {
        $response["status"] = "error";
        $response["message"] = "Registration failed.";
        http_response_code(500);
    }

    echo json_encode($response);

    //closing statement and connection
    $stmt->close();
    $conn->close();
}
