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

function getLecturer($emp_no, $password) {
    $pdo = getDbConnection();
    try {
        $stmt = $pdo->prepare("SELECT full_name, email, password FROM lecturers_tbl WHERE emp_no = ?");
        $stmt->execute([$emp_no]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row && password_verify($password, $row['password'])) {
            return ["full_name" => $row['full_name'], "email" => $row['email']];
        } else {
            jsonResponse(404, "Lecturer not found or invalid password");
        }
    } catch (PDOException $e) {
        jsonResponse(500, "Query failed", ["details" => $e->getMessage()]);
    }
}

function insertLecturer($data) {
    $pdo = getDbConnection();
    try {
        $hashed_password = password_hash($data['password'], PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("INSERT INTO lecturers_tbl (emp_no, full_name, email, password) VALUES (?, ?, ?, ?)");
        $stmt->execute([$data['emp_no'], $data['full_name'], $data['email'], $hashed_password]);
        jsonResponse(201, "Lecturer added successfully");
    } catch (PDOException $e) {
        jsonResponse(500, "Insert failed", ["details" => $e->getMessage()]);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['emp_no'], $_GET['password'])) {
        $result = getLecturer($_GET['emp_no'], $_GET['password']);
        jsonResponse(200, "Lecturer fetched successfully", $result);
    } else {
        jsonResponse(400, "Please provide emp_no and password");
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$input = $_SERVER['REQUEST_METHOD'] === 'POST' ? json_decode(file_get_contents('php://input'), true) : $_GET;

	if (!isset($input['emp_no'], $input['full_name'], $input['email'], $input['password'])) {
		jsonResponse(400, "Missing required fields");
	}
	
	insertLecturer($input);
} else {
    jsonResponse(405, "Invalid request method. Use GET or POST.");
}

?>
