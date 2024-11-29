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

function getCourse($module_code) {
    $pdo = getDbConnection();
    try {
        $stmt = $pdo->prepare("SELECT module_hours, module_name, course_code FROM modules_tbl WHERE module_code = ?");
        $stmt->execute([$module_code]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            return ["module_name" => $row['module_name'], "module_hours" => $row['module_hours'], "course_code" => $row['course_code'],];
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
        $stmt = $pdo->prepare("INSERT INTO modules_tbl (module_code, module_hours, module_name, course_code) VALUES (?, ?, ?, ?)");
        $stmt->execute([$data['module_code'], $data['module_hours'], $data['module_name'], $data['course_code']]); 
        jsonResponse(201, "Course added successfully");
    } catch (PDOException $e) {
        jsonResponse(500, "Insert failed", ["details" => $e->getMessage()]);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['module_code'])) {
        $result = getCourse($_GET['module_code']);
        jsonResponse(200, "Module fetched successfully", $result);
    } else {
        jsonResponse(400, "Please provide course code or invalid");
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$input = $_SERVER['REQUEST_METHOD'] === 'POST' ? json_decode(file_get_contents('php://input'), true) : $_GET;

	if (!isset($input['module_code'], $input['module_hours'], $input['module_name'])) {
		jsonResponse(400, "Missing required fields");
	}
	
	insertCourse($input);
} else {
    jsonResponse(405, "Invalid request method. Use GET or POST.");
}

?>
