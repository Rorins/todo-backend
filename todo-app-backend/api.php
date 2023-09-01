<?php
session_start();
include 'database.php';

header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Origin: http://127.0.0.1:5173");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

// FETCH TASKS
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    //fetching user id from session local storage to associate it to his tasks
    //assign it to the session user so I can use it elsewhere
    $userId = $_GET['user_id'];
    $_SESSION['user'] = $userId;

    $query = "SELECT * FROM tasks WHERE user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result) {
        $tasks = array();
        while ($row = $result->fetch_assoc()) {
            $tasks[] = array(
                //I will use the id to delete and update the specific task
                'id' => $row['id'],
                'title' => $row['title'],
                'expiry_date' => $row['expiry_date'],
                //default value of 0 is set to false for frontend
                'completed' => (bool)$row['completed'],
            );
        }

        //sending out the tasks in json format
        http_response_code(200);
        echo json_encode($tasks);
        exit;

        // handling if there aren't any tasks in the table
        // it will send an empty array in this case
    } else {
        http_response_code(500);
        echo json_encode(array());
        exit;
    }
}

//CREATE TASKS
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CREATE TASK, THIS IS WORKING WILL NEED TO ADD THE ID LATER
    $taskData = json_decode(file_get_contents("php://input"));

    //retrieving user id to associate task with his user
    $userId = $taskData->userId;
    $title = $taskData->title;
    $expiryDate = $taskData->expiryDate;

    //inserting data in tasks table
    $query = "INSERT INTO tasks (user_id, title, expiry_date) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iss", $userId, $title, $expiryDate);

    if ($stmt->execute()) {
        // Task added successfully
        echo json_encode([
            "message" => "Task added successfully", 'user' => [
                'id' => $userId,
            ],
        ]);
    } else {
        // Error adding task
        http_response_code(500);
        echo json_encode(["message" => "Error adding task" . $stmt->error]);
    }

    $stmt->close();
}

//DELETE TASKS
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $taskId = $_GET['id'];
    $userId = $_GET['user_id'];

    // Delete task by id
    $query = "DELETE FROM tasks WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $taskId, $userId);

    if ($stmt->execute()) {
        echo json_encode(["message" => "Task deleted successfully"]);
    } else {
        http_response_code(500);
        echo json_encode(["message" => "Error deleting task" . $stmt->error]);
    }

    $stmt->close();
}

// UPDATE TASKS
if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $taskId = $_GET['id'];
    $userId = $_GET['user_id'];

    // Extract task completion status from the request data
    $taskData = json_decode(file_get_contents("php://input"));
    $completed = $taskData->completed;

    // Update task completion status 
    $query = "UPDATE tasks SET completed = ? WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iii", $completed, $taskId, $userId);

    if ($stmt->execute()) {
        echo json_encode(["message" => "Task updated successfully"]);
    } else {
        http_response_code(500);
        echo json_encode(["message" => "Error updating task" . $stmt->error]);
    }

    $stmt->close();
}
