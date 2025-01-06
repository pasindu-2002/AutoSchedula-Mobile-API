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

function deleteStudent($stu_id) {
    $pdo = getDbConnection();
    try {
        $stmt = $pdo->prepare("DELETE FROM student_tbl WHERE stu_id = ?");
        $stmt->execute([$stu_id]);

        if ($stmt->rowCount() > 0) {
            jsonResponse(200, "Student deleted successfully");
        } else {
            jsonResponse(404, "Student not found");
        }
    } catch (PDOException $e) {
        jsonResponse(500, "Delete failed", ["details" => $e->getMessage()]);
    }
}

function updateStudent($stu_id, $data) {
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

        $values[] = $stu_id;

        $stmt = $pdo->prepare("UPDATE student_tbl SET " . implode(", ", $fields) . " WHERE stu_id = ?");
        $stmt->execute($values);

        if ($stmt->rowCount() > 0) {
            jsonResponse(200, "Student updated successfully");
        } else {
            jsonResponse(404, "Student not found or no changes made");
        }
    } catch (PDOException $e) {
        jsonResponse(500, "Update failed", ["details" => $e->getMessage()]);
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
    $input = json_decode(file_get_contents('php://input'), true);

    if (!isset($input['stu_id'], $input['full_name'], $input['email'], $input['password'])) {
        jsonResponse(400, "Missing required fields");
    }

    insertStudent($input);
} elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $input = json_decode(file_get_contents('php://input'), true);

    if (!isset($_GET['stu_id'])) {
        jsonResponse(400, "Please provide stu_id");
    }

    updateStudent($_GET['stu_id'], $input);
} elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    if (!isset($_GET['stu_id'])) {
        jsonResponse(400, "Please provide stu_id");
    }

    deleteStudent($_GET['stu_id']);
} else {
    jsonResponse(405, "Invalid request method. Use GET, POST, PUT, or DELETE.");
}
?>
