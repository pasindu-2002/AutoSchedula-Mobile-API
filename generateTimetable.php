<?php

function getDbConnection() {
    $host = "localhost";
    $username = "root";
    $password = "";
    $dbname = "autoschedula";

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
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

function generateTimetable($data) {
    $pdo = getDbConnection();

    // Extract inputs
    $lec = $data['lecturer_id'];
    $module = $data['module_code'];
    $batch = $data['batch_code'];
    $type = $data['session_type'];
    $start_date = $data['start_date'];
    $end_date = $data['end_date'];

    // Validate inputs
    if (empty($lec) || empty($module) || empty($batch) || empty($type) || empty($start_date) || empty($end_date)) {
        jsonResponse(400, "Missing required fields");
    }

    try {
        // Check module hours
        $stmt = $pdo->prepare("SELECT module_hours FROM modules_tbl WHERE  module_code = :module_code");
        $stmt->bindParam(':module_code', $module);
        $stmt->execute();
        $moduleData = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$moduleData) {
            jsonResponse(404, "Module not found");
        }

        $hour = $moduleData['module_hours'];

        // Fetch existing dates for the lecturer
        $stmt = $pdo->prepare("SELECT date FROM lecturer_timetable WHERE lecturer_id = :lecturer_id");
        $stmt->bindParam(':lecturer_id', $lec);
        $stmt->execute();
        $previouslyInsertedDatesLec = $stmt->fetchAll(PDO::FETCH_COLUMN);

         // Fetch existing dates for the Batch
         $stmt = $pdo->prepare("SELECT date FROM assign_module_tbl WHERE batch_id = :batch_id");
         $stmt->bindParam(':batch_id', $batch);
         $stmt->execute();
         $previouslyInsertedDatesBatch = $stmt->fetchAll(PDO::FETCH_COLUMN);

        // Calculate non-previously inserted dates
        $startDate = new DateTime($start_date);
        $endDate = new DateTime($end_date);
        $nonInsertedDates = [];
        $currentDate = clone $startDate;

        if ($hour == 60) {
            $startDateTimestamp = $startDate->getTimestamp();
                $endDateTimestamp = $endDate->getTimestamp();

                while (count($nonInsertedDates) < 10) {
                    // Generate a random timestamp within the range
                    $randomTimestamp = mt_rand($startDateTimestamp, $endDateTimestamp);
                    $randomDateStr = date('Y-m-d', $randomTimestamp);

                    // Check if the date has not been previously inserted
                    if (!in_array($randomDateStr, $previouslyInsertedDatesLec) && !in_array($randomDateStr, $previouslyInsertedDatesBatch) && !in_array($randomDateStr, $nonInsertedDates)) {
                        $nonInsertedDates[] = $randomDateStr;
                    }

                    // Break if all possible dates are exhausted
                    if (count($nonInsertedDates) >= 10 || count($nonInsertedDates) + count($previouslyInsertedDatesLec) + count($previouslyInsertedDatesBatch) >= ($endDateTimestamp - $startDateTimestamp) / (60 * 60 * 24)) {
                        break;
                    }
                }


            if (count($nonInsertedDates) < 10) {
                jsonResponse(400, "Not enough available dates");
            }

            // Insert dates into the database
            foreach ($nonInsertedDates as $random_date) {
                $pdo->beginTransaction();

                // Insert into asign_module
                $stmt = $pdo->prepare("INSERT INTO assign_module_tbl (batch_id, lecturer_id, module_id, date, status, session_type) 
                                       VALUES (:batch_code, :lecturer, :module_code, :date, :status, :session_type)");
                $stmt->execute([
                    ':batch_code' => $batch,
                    ':lecturer' => $lec,
                    ':module_code' => $module,
                    ':date' => $random_date,
                    ':status' => "0",
                    ':session_type' => $type
                ]);

                // Insert into lecturer_timetable
                $stmt = $pdo->prepare("INSERT INTO lecturer_timetable (lecturer_id, description, date) 
                                       VALUES (:lecturer, :description, :date)");
                $stmt->execute([
                    ':lecturer' => $lec,
                    ':description' => "Lectures",
                    ':date' => $random_date,
                ]);

                $pdo->commit();
            }

            jsonResponse(200, "Timetable successfully generated", ["dates" => $nonInsertedDates]);
        } else {
            jsonResponse(400, "Module hours not supported");
        }
    } catch (PDOException $e) {
        $pdo->rollBack();
        jsonResponse(500, "Database error", ["details" => $e->getMessage()]);
    }
}

// Handle POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    generateTimetable($input);
} else {
    jsonResponse(405, "Invalid request method. Use POST.");
}

?>
