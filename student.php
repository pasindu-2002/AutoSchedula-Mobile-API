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

function getStudent($stu_id, $password) {
    $pdo = getDbConnection();
    try {
        $stmt = $pdo->prepare("SELECT full_name, email, password FROM student_tbl WHERE stu_id = ?");
        $stmt->execute([$stu_id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row && password_verify($password, $row['password'])) {
            return ["full_name" => $row['full_name'], "email" => $row['email']];
        } else {
            jsonResponse(404, "Student not found or invalid password");
        }
    } catch (PDOException $e) {
        jsonResponse(500, "Query failed", ["details" => $e->getMessage()]);
    }
}

function insertStudent($data) {
    $pdo = getDbConnection();
    try {
        // Check if stu_id already exists
        $stmt = $pdo->prepare("SELECT stu_id FROM student_tbl WHERE stu_id = ?");
        $stmt->execute([$data['stu_id']]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existing) {
            jsonResponse(409, "Student with this ID already exists");
            return;
        }

        // Hash the password
        $hashed_password = password_hash($data['password'], PASSWORD_BCRYPT);

        // Insert the student data
        $stmt = $pdo->prepare("INSERT INTO student_tbl (stu_id, full_name, email, password) VALUES (?, ?, ?, ?)");
        $stmt->execute([$data['stu_id'], $data['full_name'], $data['email'], $hashed_password]);

        jsonResponse(201, "Student added successfully");
    } catch (PDOException $e) {
        jsonResponse(500, "Insert failed", ["details" => $e->getMessage()]);
    }
}


if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['stu_id'], $_GET['password'])) {
        $result = getStudent($_GET['stu_id'], $_GET['password']);
        jsonResponse(200, "Student fetched successfully", $result);
    } else {
        jsonResponse(400, "Please provide stu_id and password");
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$input = $_SERVER['REQUEST_METHOD'] === 'POST' ? json_decode(file_get_contents('php://input'), true) : $_GET;

	if (!isset($input['stu_id'], $input['full_name'], $input['email'], $input['password'])) {
		jsonResponse(400, "Missing required fields");
	}
	
	insertStudent($input);
} else {
    jsonResponse(405, "Invalid request method. Use GET or POST.");
}

?>
