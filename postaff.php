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

function getEmployee($emp_no, $password) {
    $pdo = getDbConnection();
    try {
        $stmt = $pdo->prepare("SELECT full_name, email, password FROM postaff_tbl WHERE emp_no = ?");
        $stmt->execute([$emp_no]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row && password_verify($password, $row['password'])) {
            return ["full_name" => $row['full_name'], "email" => $row['email']];
        } else {
            jsonResponse(404, "Employee not found or invalid password");
        }
    } catch (PDOException $e) {
        jsonResponse(500, "Query failed", ["details" => $e->getMessage()]);
    }
}

function insertEmployee($data) {
    $pdo = getDbConnection();
    try {
        // Check if emp_no already exists
        $stmt = $pdo->prepare("SELECT emp_no FROM postaff_tbl WHERE emp_no = ?");
        $stmt->execute([$data['emp_no']]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existing) {
            jsonResponse(409, "Employee number already exists");
            return;
        }

        // Hash the password
        $hashed_password = password_hash($data['password'], PASSWORD_BCRYPT);

        // Insert the employee data
        $stmt = $pdo->prepare("INSERT INTO postaff_tbl (emp_no, full_name, email, password) VALUES (?, ?, ?, ?)");
        $stmt->execute([$data['emp_no'], $data['full_name'], $data['email'], $hashed_password]);
        
        jsonResponse(201, "Employee added successfully");
    } catch (PDOException $e) {
        jsonResponse(500, "Insert failed", ["details" => $e->getMessage()]);
    }
}


if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['emp_no'], $_GET['password'])) {
        $result = getEmployee($_GET['emp_no'], $_GET['password']);
        jsonResponse(200, "Employee fetched successfully", $result);
    } else {
        jsonResponse(400, "Please provide emp_no and password");
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$input = $_SERVER['REQUEST_METHOD'] === 'POST' ? json_decode(file_get_contents('php://input'), true) : $_GET;

	if (!isset($input['emp_no'], $input['full_name'], $input['email'], $input['password'])) {
		jsonResponse(400, "Missing required fields");
	}
	
	insertEmployee($input);
} else {
    jsonResponse(405, "Invalid request method. Use GET or POST.");
}

?>
