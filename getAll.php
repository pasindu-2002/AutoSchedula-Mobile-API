<?php

function getDbConnection() {
    $host = "localhost";
    $dbusername = "root";
    $dbpassword = "";
    $dbname = "autoschedula";

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname", $dbusername, $dbpassword);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch (PDOException $e) {
        jsonResponse(500, "Database connection failed", ["details" => $e->getMessage()]);
    }
}

function jsonResponse($status, $message, $data = []) {
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode(["message" => $message, "data" => $data]);
    exit;
}

// Fetch all records from a table
function getAll($table_name) {
    $pdo = getDbConnection();
    try {
        $stmt = $pdo->query("SELECT * FROM $table_name");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($rows) {
            return $rows;
        } else {
            jsonResponse(404, "No records found in $table_name");
        }
    } catch (PDOException $e) {
        jsonResponse(500, "Query failed for $table_name", ["details" => $e->getMessage()]);
    }
}

// Handle GET requests for all records
function handleGetAll($table_name) {
    $result = getAll($table_name);
    jsonResponse(200, "Records fetched successfully from $table_name", $result);
}

// Handle API requests
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['type'])) {
        $type = $_GET['type'];

        switch ($type) {
            case 'batch':
                handleGetAll('batch_tbl');
                break;
            case 'student':
                handleGetAll('student_tbl');
                break;
            case 'course':
                handleGetAll('course_tbl');
                break;
            case 'lecturer':
                handleGetAll('lecturers_tbl');
                break;
            case 'module':
                handleGetAll('modules_tbl');
                break;
            default:
                jsonResponse(400, "Invalid type specified. Use batch, student, course, lecturer, or module.");
        }
    } else {
        jsonResponse(400, "Please specify a type to fetch records.");
    }
} else {
    jsonResponse(405, "Invalid request method. Use GET.");
}

?>
