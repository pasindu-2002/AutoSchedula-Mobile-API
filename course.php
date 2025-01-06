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
            jsonResponse(404, "Course not found");
        }
    } catch (PDOException $e) {
        jsonResponse(500, "Query failed", ["details" => $e->getMessage()]);
    }
}

function insertCourse($data) {
    $pdo = getDbConnection();
    try {
        // Check if course_code already exists
        $stmt = $pdo->prepare("SELECT course_code FROM course_tbl WHERE course_code = ?");
        $stmt->execute([$data['course_code']]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existing) {
            jsonResponse(409, "Course with this code already exists");
            return;
        }

        // Insert the course data
        $stmt = $pdo->prepare("INSERT INTO course_tbl (course_code, course_name, school) VALUES (?, ?, ?)");
        $stmt->execute([$data['course_code'], $data['course_name'], $data['school']]);

        jsonResponse(201, "Course added successfully");
    } catch (PDOException $e) {
        jsonResponse(500, "Insert failed", ["details" => $e->getMessage()]);
    }
}

function deleteCourse($course_code) {
    $pdo = getDbConnection();
    try {
        $stmt = $pdo->prepare("DELETE FROM course_tbl WHERE course_code = ?");
        $stmt->execute([$course_code]);

        if ($stmt->rowCount() > 0) {
            jsonResponse(200, "Course deleted successfully");
        } else {
            jsonResponse(404, "Course not found");
        }
    } catch (PDOException $e) {
        jsonResponse(500, "Delete failed", ["details" => $e->getMessage()]);
    }
}

function updateCourse($course_code, $data) {
    $pdo = getDbConnection();
    try {
        $fields = [];
        $values = [];

        if (isset($data['course_name'])) {
            $fields[] = "course_name = ?";
            $values[] = $data['course_name'];
        }

        if (isset($data['school'])) {
            $fields[] = "school = ?";
            $values[] = $data['school'];
        }

        if (empty($fields)) {
            jsonResponse(400, "No fields to update");
        }

        $values[] = $course_code;

        $stmt = $pdo->prepare("UPDATE course_tbl SET " . implode(", ", $fields) . " WHERE course_code = ?");
        $stmt->execute($values);

        if ($stmt->rowCount() > 0) {
            jsonResponse(200, "Course updated successfully");
        } else {
            jsonResponse(404, "Course not found or no changes made");
        }
    } catch (PDOException $e) {
        jsonResponse(500, "Update failed", ["details" => $e->getMessage()]);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['course_code'])) {
        $result = getCourse($_GET['course_code']);
        jsonResponse(200, "Course fetched successfully", $result);
    } else {
        jsonResponse(400, "Please provide course code");
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    if (!isset($input['course_code'], $input['course_name'], $input['school'])) {
        jsonResponse(400, "Missing required fields");
    }

    insertCourse($input);
} elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $input = json_decode(file_get_contents('php://input'), true);

    if (!isset($_GET['course_code'])) {
        jsonResponse(400, "Please provide course_code");
    }

    updateCourse($_GET['course_code'], $input);
} elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    if (!isset($_GET['course_code'])) {
        jsonResponse(400, "Please provide course_code");
    }

    deleteCourse($_GET['course_code']);
} else {
    jsonResponse(405, "Invalid request method. Use GET, POST, PUT, or DELETE.");
}

?>
