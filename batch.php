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

function getCourse($batch_code) {
    $pdo = getDbConnection();
    try {
        $stmt = $pdo->prepare("SELECT course_code, course_director FROM batch_tbl WHERE batch_code = ?");
        $stmt->execute([$batch_code]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            return ["module_name" => $row['module_name'], "course_code" => $row['course_code'], "course_director" => $row['course_director'],];
        } else {
            jsonResponse(404, "Course not found or invalid password");
        }
    } catch (PDOException $e) {
        jsonResponse(500, "Query failed", ["details" => $e->getMessage()]);
    }
}

function insertCourse($data) {
    $pdo = getDbConnection();
    try {
        $stmt = $pdo->prepare("INSERT INTO batch_tbl (batch_code, course_code, course_director) VALUES (?, ?, ?)");
        $stmt->execute([$data['batch_code'], $data['course_code'], $data['course_director']]);
        jsonResponse(201, "Batch added successfully");
    } catch (PDOException $e) {
        jsonResponse(500, "Insert failed", ["details" => $e->getMessage()]);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['batch_code'])) {
        $result = getCourse($_GET['batch_code']);
        jsonResponse(200, "Module fetched successfully", $result);
    } else {
        jsonResponse(400, "Please provide course code or invalid");
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$input = $_SERVER['REQUEST_METHOD'] === 'POST' ? json_decode(file_get_contents('php://input'), true) : $_GET;

	if (!isset($input['batch_code'], $input['module_hours'], $input['module_name'])) {
		jsonResponse(400, "Missing required fields");
	}
	
	insertCourse($input);
} else {
    jsonResponse(405, "Invalid request method. Use GET or POST.");
}

?>
