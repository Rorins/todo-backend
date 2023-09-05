<?php

//DB Connection
define("DB_HOST", "localhost");
define("DB_USER", "root");
define("DB_PASS", "root");
define("DB_NAME", "todo_db");

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

//connection check
if (!$conn) {
    die("Connection failed: " . $conn->connect_error);
}
