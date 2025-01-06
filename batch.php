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

// Get batch details by batch_code
function getBatch($batch_code) {
    $pdo = getDbConnection();
    try {
        $stmt = $pdo->prepare("SELECT batch_code, course_code, course_director FROM batch_tbl WHERE batch_code = ?");
        $stmt->execute([$batch_code]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            return $row;
        } else {
            jsonResponse(404, "Batch not found");
        }
    } catch (PDOException $e) {
        jsonResponse(500, "Query failed", ["details" => $e->getMessage()]);
    }
}

// Insert a new batch
function insertBatch($data) {
    $pdo = getDbConnection();
    try {
        // Check if batch_code already exists
        $stmt = $pdo->prepare("SELECT batch_code FROM batch_tbl WHERE batch_code = ?");
        $stmt->execute([$data['batch_code']]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existing) {
            jsonResponse(409, "Batch with this code already exists");
            return;
        }

        // Insert the batch data
        $stmt = $pdo->prepare("INSERT INTO batch_tbl (batch_code, course_code, course_director) VALUES (?, ?, ?)");
        $stmt->execute([$data['batch_code'], $data['course_code'], $data['course_director']]);

        jsonResponse(201, "Batch added successfully");
    } catch (PDOException $e) {
        jsonResponse(500, "Insert failed", ["details" => $e->getMessage()]);
    }
}

// Update batch details
function updateBatch($batch_code, $data) {
    $pdo = getDbConnection();
    try {
        $fields = [];
        $values = [];

        if (isset($data['course_code'])) {
            $fields[] = "course_code = ?";
            $values[] = $data['course_code'];
        }

        if (isset($data['course_director'])) {
            $fields[] = "course_director = ?";
            $values[] = $data['course_director'];
        }

        if (empty($fields)) {
            jsonResponse(400, "No fields to update");
        }

        $values[] = $batch_code;

        $stmt = $pdo->prepare("UPDATE batch_tbl SET " . implode(", ", $fields) . " WHERE batch_code = ?");
        $stmt->execute($values);

        if ($stmt->rowCount() > 0) {
            jsonResponse(200, "Batch updated successfully");
        } else {
            jsonResponse(404, "Batch not found or no changes made");
        }
    } catch (PDOException $e) {
        jsonResponse(500, "Update failed", ["details" => $e->getMessage()]);
    }
}

// Delete a batch
function deleteBatch($batch_code) {
    $pdo = getDbConnection();
    try {
        $stmt = $pdo->prepare("DELETE FROM batch_tbl WHERE batch_code = ?");
        $stmt->execute([$batch_code]);

        if ($stmt->rowCount() > 0) {
            jsonResponse(200, "Batch deleted successfully");
        } else {
            jsonResponse(404, "Batch not found");
        }
    } catch (PDOException $e) {
        jsonResponse(500, "Delete failed", ["details" => $e->getMessage()]);
    }
}

// Handle HTTP requests
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['batch_code'])) {
        $result = getBatch($_GET['batch_code']);
        jsonResponse(200, "Batch fetched successfully", $result);
    } else {
        jsonResponse(400, "Please provide batch_code");
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    if (!isset($input['batch_code'], $input['course_code'], $input['course_director'])) {
        jsonResponse(400, "Missing required fields");
    }

    insertBatch($input);
} elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $input = json_decode(file_get_contents('php://input'), true);

    if (!isset($_GET['batch_code'])) {
        jsonResponse(400, "Please provide batch_code");
    }

    updateBatch($_GET['batch_code'], $input);
} elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    if (!isset($_GET['batch_code'])) {
        jsonResponse(400, "Please provide batch_code");
    }

    deleteBatch($_GET['batch_code']);
} else {
    jsonResponse(405, "Invalid request method. Use GET, POST, PUT, or DELETE.");
}

?>
