<?php
include './config/database.php';

header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Origin: http://127.0.0.1:5173");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

// FETCH TASKS
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    //Fetchin tasks with related categories where the user_id is equal to the authenticated user
    //so I get only the authenticated user tasks
    $userId = $_GET['user_id'];

    $query = "SELECT tasks.id, tasks.title, tasks.expiry_date, tasks.completed, categories.category_name 
    FROM tasks INNER JOIN categories 
    ON tasks.category_id = categories.category_id 
    WHERE tasks.user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    //if the result is positive I create an associative array with fetch_assoc
    //where the column is the key and te row is the value and wrap each row of the table in the row variable
    //this way I get a single task inside of tasks that gets looped, so we are constructing tasks
    if ($result) {
        $tasks = [];
        while ($row = $result->fetch_assoc()) {
            $tasks[] = [
                //I will use the id to delete and update the specific task
                'id' => $row['id'],
                'title' => $row['title'],
                'expiry_date' => $row['expiry_date'],
                //default value of 0 is set to false for frontend
                'completed' => (bool)$row['completed'],
                'category_name' => $row['category_name'],
            ];
        }

        //sending out the tasks in json format
        http_response_code(200);
        echo json_encode($tasks);
        exit;
    } else {
        http_response_code(500);
        echo json_encode(["message" => "Error adding task: " . $stmt->error]);
        exit;
    }
}

//CREATE TASKS
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $taskData = json_decode(file_get_contents("php://input"));

    //tasks will have this data:
    $userId = $taskData->userId;
    $title = $taskData->title;
    $expiryDate = $taskData->expiryDate;

    //if category id doesn't exist or is null/ empty we send out error
    if (!isset($taskData->category_id) || empty($taskData->category_id)) {
        http_response_code(400);
        echo json_encode(
            $response = [
                'status' => 'error',
                'message' => 'Category is required',
            ]
        );
        exit;
    }

    $selectedCategory = $taskData->category_id;

    //inserting data in tasks table
    $query = "INSERT INTO tasks (user_id, title, expiry_date, category_id) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("issi", $userId, $title, $expiryDate, $selectedCategory);

    if ($stmt->execute()) {
        // Task added successfully
        echo json_encode([
            "message" => "Task added successfully",
            'user' => [
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
