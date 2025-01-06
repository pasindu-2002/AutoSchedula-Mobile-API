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

function deleteEmployee($emp_no) {
    $pdo = getDbConnection();
    try {
        $stmt = $pdo->prepare("DELETE FROM postaff_tbl WHERE emp_no = ?");
        $stmt->execute([$emp_no]);

        if ($stmt->rowCount() > 0) {
            jsonResponse(200, "Employee deleted successfully");
        } else {
            jsonResponse(404, "Employee not found");
        }
    } catch (PDOException $e) {
        jsonResponse(500, "Delete failed", ["details" => $e->getMessage()]);
    }
}

function updateEmployee($emp_no, $data) {
    $pdo = getDbConnection();
    try {
        $fields = [];
        $values = [];

        if (isset($data['full_name'])) {
            $fields[] = "full_name = ?";
            $values[] = $data['full_name'];
        }

        if (isset($data['email'])) {
            $fields[] = "email = ?";
            $values[] = $data['email'];
        }

        if (isset($data['password'])) {
            $fields[] = "password = ?";
            $values[] = password_hash($data['password'], PASSWORD_BCRYPT);
        }

        if (empty($fields)) {
            jsonResponse(400, "No fields to update");
        }

        $values[] = $emp_no;

        $stmt = $pdo->prepare("UPDATE postaff_tbl SET " . implode(", ", $fields) . " WHERE emp_no = ?");
        $stmt->execute($values);

        if ($stmt->rowCount() > 0) {
            jsonResponse(200, "Employee updated successfully");
        } else {
            jsonResponse(404, "Employee not found or no changes made");
        }
    } catch (PDOException $e) {
        jsonResponse(500, "Update failed", ["details" => $e->getMessage()]);
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
    $input = json_decode(file_get_contents('php://input'), true);

    if (!isset($input['emp_no'], $input['full_name'], $input['email'], $input['password'])) {
        jsonResponse(400, "Missing required fields");
    }

    insertEmployee($input);
} elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $input = json_decode(file_get_contents('php://input'), true);

    if (!isset($_GET['emp_no'])) {
        jsonResponse(400, "Please provide emp_no");
    }

    updateEmployee($_GET['emp_no'], $input);
} elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    if (!isset($_GET['emp_no'])) {
        jsonResponse(400, "Please provide emp_no");
    }

    deleteEmployee($_GET['emp_no']);
} else {
    jsonResponse(405, "Invalid request method. Use GET, POST, PUT, or DELETE.");
}
?>
