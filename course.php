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

function getCourse($course_code) {
    $pdo = getDbConnection();
    try {
        $stmt = $pdo->prepare("SELECT course_name, school FROM course_tbl WHERE course_code = ?");
        $stmt->execute([$course_code]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            return ["course_name" => $row['course_name'], "school" => $row['school']];
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
        $stmt = $pdo->prepare("INSERT INTO course_tbl (course_code, course_name, school) VALUES (?, ?, ?)");
        $stmt->execute([$data['course_code'], $data['course_name'], $data['school']]);
        jsonResponse(201, "Course added successfully");
    } catch (PDOException $e) {
        jsonResponse(500, "Insert failed", ["details" => $e->getMessage()]);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['course_code'])) {
        $result = getCourse($_GET['course_code']);
        jsonResponse(200, "Course fetched successfully", $result);
    } else {
        jsonResponse(400, "Please provide course code or invalid");
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$input = $_SERVER['REQUEST_METHOD'] === 'POST' ? json_decode(file_get_contents('php://input'), true) : $_GET;

	if (!isset($input['course_code'], $input['course_name'], $input['school'])) {
		jsonResponse(400, "Missing required fields");
	}
	
	insertCourse($input);
} else {
    jsonResponse(405, "Invalid request method. Use GET or POST.");
}

?>
