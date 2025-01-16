<?php
include 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $student_id_varchar = $_POST['student_id']; 
    $in_date = $_POST['in_date'];
    $return_date = $_POST['return_date'];
    $reason = $_POST['reason'];

    
    $stmt = $conn->prepare("SELECT id FROM students WHERE student_id = ?");
    $stmt->bind_param("s", $student_id_varchar); 
    $stmt->execute();
    $stmt->bind_result($student_id);
    $stmt->fetch();
    $stmt->close();

    if ($student_id) {
        
        $update_stmt = $conn->prepare("UPDATE students SET exception = 1 WHERE id = ?");
        $update_stmt->bind_param("i", $student_id);
        $update_stmt->execute();
        $update_stmt->close();

        
        $insert_stmt = $conn->prepare("INSERT INTO exceptions (student_id, start_date, end_date, reason) VALUES (?, ?, ?, ?)");
        $insert_stmt->bind_param("isss", $student_id, $in_date, $return_date, $reason);
        $insert_stmt->execute();
        $insert_stmt->close();

        
        header("Location: index.php");
        exit; 
    } else {
        echo "Student not found!";
    }
}
?>
