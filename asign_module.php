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
        $stmt = $pdo->prepare("SELECT * FROM asign_module WHERE batch_code = ?");
        $stmt->execute([$course_code]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            return ["lec_emp_code" => $row['lec_emp_code'], "module_code" => $row['module_code'], "batch_code" => $row['batch_code'], "session_type" => $row['session_type'], "date" => $row['date']];
        } else {
            jsonResponse(404, "Asign Module not found or invalid password");
        }
    } catch (PDOException $e) {
        jsonResponse(500, "Query failed", ["details" => $e->getMessage()]);
    }
}


function insertCourse($data) {
    $pdo = getDbConnection();
    try {
        $stmt = $pdo->prepare("INSERT INTO asign_module (lec_emp_code, module_code, batch_code, session_type, date) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$data['lec_emp_code'], $data['module_code'], $data['batch_code'], $data['session_type'], $data['date']]);
        jsonResponse(201, "asign module successfully");
    } catch (PDOException $e) {
        jsonResponse(500, "Insert failed", ["details" => $e->getMessage()]);
    }
}


if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['course_code'])) {
        $result = getCourse($_GET['course_code']);
        jsonResponse(200, "asign module fetched successfully", $result);
    } else {
        jsonResponse(400, "Please provide valid data!");
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
