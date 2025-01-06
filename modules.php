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

function getModule($module_code) {
    $pdo = getDbConnection();
    try {
        $stmt = $pdo->prepare("SELECT module_hours, module_name, course_code FROM modules_tbl WHERE module_code = ?");
        $stmt->execute([$module_code]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            return ["module_name" => $row['module_name'], "module_hours" => $row['module_hours'], "course_code" => $row['course_code']];
        } else {
            jsonResponse(404, "Module not found");
        }
    } catch (PDOException $e) {
        jsonResponse(500, "Query failed", ["details" => $e->getMessage()]);
    }
}

function insertModule($data) {
    $pdo = getDbConnection();
    try {
        // Check if module_code already exists
        $stmt = $pdo->prepare("SELECT module_code FROM modules_tbl WHERE module_code = ?");
        $stmt->execute([$data['module_code']]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existing) {
            jsonResponse(409, "Module with this code already exists");
            return;
        }

        // Insert the module data
        $stmt = $pdo->prepare("INSERT INTO modules_tbl (module_code, module_hours, module_name, course_code) VALUES (?, ?, ?, ?)");
        $stmt->execute([$data['module_code'], $data['module_hours'], $data['module_name'], $data['course_code']]);

        jsonResponse(201, "Module added successfully");
    } catch (PDOException $e) {
        jsonResponse(500, "Insert failed", ["details" => $e->getMessage()]);
    }
}

function deleteModule($module_code) {
    $pdo = getDbConnection();
    try {
        $stmt = $pdo->prepare("DELETE FROM modules_tbl WHERE module_code = ?");
        $stmt->execute([$module_code]);

        if ($stmt->rowCount() > 0) {
            jsonResponse(200, "Module deleted successfully");
        } else {
            jsonResponse(404, "Module not found");
        }
    } catch (PDOException $e) {
        jsonResponse(500, "Delete failed", ["details" => $e->getMessage()]);
    }
}

function updateModule($module_code, $data) {
    $pdo = getDbConnection();
    try {
        $fields = [];
        $values = [];

        if (isset($data['module_hours'])) {
            $fields[] = "module_hours = ?";
            $values[] = $data['module_hours'];
        }

        if (isset($data['module_name'])) {
            $fields[] = "module_name = ?";
            $values[] = $data['module_name'];
        }

        if (isset($data['course_code'])) {
            $fields[] = "course_code = ?";
            $values[] = $data['course_code'];
        }

        if (empty($fields)) {
            jsonResponse(400, "No fields to update");
        }

        $values[] = $module_code;

        $stmt = $pdo->prepare("UPDATE modules_tbl SET " . implode(", ", $fields) . " WHERE module_code = ?");
        $stmt->execute($values);

        if ($stmt->rowCount() > 0) {
            jsonResponse(200, "Module updated successfully");
        } else {
            jsonResponse(404, "Module not found or no changes made");
        }
    } catch (PDOException $e) {
        jsonResponse(500, "Update failed", ["details" => $e->getMessage()]);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['module_code'])) {
        $result = getModule($_GET['module_code']);
        jsonResponse(200, "Module fetched successfully", $result);
    } else {
        jsonResponse(400, "Please provide module code");
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    if (!isset($input['module_code'], $input['module_hours'], $input['module_name'], $input['course_code'])) {
        jsonResponse(400, "Missing required fields");
    }

    insertModule($input);
} elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $input = json_decode(file_get_contents('php://input'), true);

    if (!isset($_GET['module_code'])) {
        jsonResponse(400, "Please provide module_code");
    }

    updateModule($_GET['module_code'], $input);
} elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    if (!isset($_GET['module_code'])) {
        jsonResponse(400, "Please provide module_code");
    }

    deleteModule($_GET['module_code']);
} else {
    jsonResponse(405, "Invalid request method. Use GET, POST, PUT, or DELETE.");
}

?>
