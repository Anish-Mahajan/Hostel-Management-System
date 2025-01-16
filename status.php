<?php
include 'db_connection.php';


function send_email_to_parent($student_id, $message) {
    global $conn;
    
    $email_query = "SELECT parent_email FROM students WHERE student_id = ?";
    $stmt_email = $conn->prepare($email_query);
    $stmt_email->bind_param("s", $student_id);
    $stmt_email->execute();
    $email_result = $stmt_email->get_result();
    
    if ($email_result->num_rows > 0) {
        $row = $email_result->fetch_assoc();
        $parent_email = $row['parent_email'];
        
        if ($parent_email) {
            $subject = "Late Alert for Your Child";
            $headers = "From: ameyapatel77@gmail.com";
            mail($parent_email, $subject, $message, $headers);
        }
    }
    $stmt_email->close();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    $student_id = !empty($_POST['student_id']) ? $_POST['student_id'] : $_POST['student_id_optional'];
    
    
    if (empty($student_id)) {
        '<script>
            alert("Error: No student ID provided.");
            window.location.href = "index.php";
        </script>';
        exit(); 
    }

    $status = $_POST['status']; 
    $time = date("Y-m-d H:i:s");

    
    $check_stmt = $conn->prepare("SELECT id, exception FROM students WHERE student_id = ?");
    $check_stmt->bind_param("s", $student_id); 
    $check_stmt->execute();
    $check_stmt->bind_result($id, $exception);
    $check_stmt->fetch();
    $check_stmt->close();

    if (empty($id)) {
        echo '<script>
            alert("Error: Student ID does not exist.");
            window.location.href = "index.php";
        </script>';
        exit(); 
    }

    
    if ($exception == 1) {
        echo '<script>
            alert("Attendance cannot be toggled as this student is on an exception.");
            window.location.href = "index.php";
        </script>';
        exit(); 
    }


   
    $stmt = $conn->prepare("INSERT INTO attendance (student_id, date, status, timestamp) VALUES (?, CURDATE(), ?, ?)");
    if ($stmt) {
        $stmt->bind_param("iss", $id, $status, $time); 
        $stmt->execute();

        
        if ($status === 'out') {
           
            $late_time_threshold = '22:00:00'; 
            $current_time = date("H:i:s");

            if ($current_time > $late_time_threshold) {
                $message = "Dear Parent,\n\nYour child with ID $student_id is currently marked 'Out' and was late today.\n\nThank you.";
                send_email_to_parent($student_id, $message);
            }
        }

        $stmt->close(); 
    } else {
        echo "Error preparing statement: " . $conn->error;
    }

    header("Location: index.php");
    exit();
}

$student_id_query = "SELECT student_id FROM students";
$student_id_result = $conn->query($student_id_query);

if (!$student_id_result) {
    die("Query failed: " . $conn->error);
}
?>
<form method="POST" action="status.php">
    
    <select name="student_id_optional">
        <option value="">Select Student ID </option>
        <?php
       
        if ($student_id_result->num_rows > 0) {
            while ($student_row = $student_id_result->fetch_assoc()) {
                echo "<option value='{$student_row['student_id']}'>{$student_row['student_id']}</option>";
            }
        }
        ?>
    </select>
    <input type="text" name="student_id" placeholder="Or enter Student ID manually">
    
    <select name="status">
        <option value="in">In</option>
        <option value="out">Out</option>
    </select>
    
    <input type="submit" value="Submit">
</form>