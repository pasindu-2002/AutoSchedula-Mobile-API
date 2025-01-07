<?php

function readTimetableByBatch($batchId) {
    $pdo = getDbConnection();

    try {
        // Query the timetable for a specific batch and sort by date in ascending order
        $stmt = $pdo->prepare("
            SELECT 
                assign_module_tbl.batch_id, 
                assign_module_tbl.lecturer_id, 
                assign_module_tbl.module_id, 
                assign_module_tbl.date, 
                assign_module_tbl.status, 
                assign_module_tbl.session_type,
                lecturer_timetable.description
            FROM 
                assign_module_tbl
            INNER JOIN 
                lecturer_timetable 
            ON 
                assign_module_tbl.date = lecturer_timetable.date
            WHERE 
                assign_module_tbl.batch_id = :batch_id
            ORDER BY 
                assign_module_tbl.date ASC
        ");
        $stmt->bindParam(':batch_id', $batchId);
        $stmt->execute();
        $timetable = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!$timetable) {
            jsonResponse(404, "No timetable entries found for the specified batch");
        }

        jsonResponse(200, "Timetable retrieved successfully", $timetable);
    } catch (PDOException $e) {
        jsonResponse(500, "Database error", ["details" => $e->getMessage()]);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['batch_id'])) {
        $batchId = $_GET['batch_id'];
        readTimetableByBatch($batchId);
    } else {
        jsonResponse(400, "Batch ID is required");
    }
}


?>