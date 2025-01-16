<?php
include 'db_connection.php';

$attendance_query = "
    SELECT s.student_id, s.name, s.room_number, a.status, a.timestamp
    FROM students s
    JOIN attendance a ON s.id = a.student_id
    WHERE a.date = CURDATE()
";
$result = $conn->query($attendance_query);

if (!$result) {
    die("Query failed: " . $conn->error);
}


$student_id_query = "SELECT student_id FROM students";
$student_id_result = $conn->query($student_id_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hostel Attendance System</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            color: #333;
            padding: 20px;
        }

        .container {
            max-width: 1000px;
            margin: auto;
        }

        h1, h2 {
            margin-bottom: 15px;
            color: #4a4a89;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            background: white;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            border-radius: 8px;
        }
        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #4a4a89;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        tr.red {
            color: red;
        }
        tr.active {
            background-color: lightgreen; 
        }

        form {
            display: flex;
            flex-direction: column;
            margin-bottom: 20px;
        }
        input[type="text"], input[type="datetime-local"], select, textarea {
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 1em;
            width: 100%;
        }
        .status-button, input[type="submit"] {
            padding: 10px 20px;
            background-color: #4a4a89;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        .status-button:hover, input[type="submit"]:hover {
            background-color: #383866;
        }

        a.button-link {
            display: inline-block;
            text-decoration: none;
            padding: 10px 20px;
            background-color: #4a4a89;
            color: white;
            border-radius: 5px;
            text-align: center;
        }
        a.button-link:hover {
            background-color: #383866;
        }

        @media (max-width: 768px) {
            table, th, td, input, select, textarea {
                font-size: 0.9em;
            }
            h1, h2 {
                font-size: 1.4em;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Hostel In/Out Management Dashboard</h1>
        <h2>Mark Status</h2>
        <form method="POST" action="status.php">
            <select name="student_id_optional" id="student_id_input">
                <option value="">Select Student ID</option>
                <?php
                if ($student_id_result->num_rows > 0) {
                    while ($student_row = $student_id_result->fetch_assoc()) {
                        echo "<option value='{$student_row['student_id']}'>{$student_row['student_id']}</option>";
                    }
                }
                ?>
            </select>
            <select name="status">
                <option value="in">In</option>
                <option value="out">Out</option>
            </select>
            
            <input type="submit" value="Submit">
        </form>        

        <h2>Current Status</h2>
<table>
    <thead>
        <tr>
            <th>Student ID</th>
            <th>Name</th>
            <th>Room Number</th>
            <th>Status</th>
            <th>Timestamp</th>
            <th>Actions</th>
        </tr>
    </thead>
        <tbody>
            <?php
            $latest_records = []; 
            while ($row = $result->fetch_assoc()) {
                $student_id = $row['student_id'];
                if (!isset($latest_records[$student_id]) || 
                    $row['timestamp'] > $latest_records[$student_id]['timestamp']) {
                    $latest_records[$student_id] = $row; 
                }
            }

            $result->data_seek(0); 
            while ($row = $result->fetch_assoc()) {
                $student_id = $row['student_id'];
                $status_class = ($row['status'] === 'Out') ? 'red' : '';
                $new_status = ($row['status'] === 'In') ? 'Out' : 'In';
                $button_text = ($row['status'] === 'In') ? 'Mark Out' : 'Mark In';
                $show_button = ($row['timestamp'] === $latest_records[$student_id]['timestamp']);

                echo "<tr class='$status_class'>
                        <td>{$row['student_id']}</td>
                        <td>{$row['name']}</td>
                        <td>{$row['room_number']}</td>
                        <td>{$row['status']}</td>
                        <td>{$row['timestamp']}</td>
                        <td>";

                if ($show_button) {
                    echo "<form method='POST' action='status.php' style='display:inline;'>
                            <input type='hidden' name='student_id' value='{$row['student_id']}'>
                            <input type='hidden' name='status' value='$new_status'>
                            <input type='submit' class='status-button' value='$button_text'>
                        </form>";
                }

                echo "</td></tr>";
            }

            if (empty($latest_records)) {
                echo "<tr><td colspan='6'>No attendance records found for today.</td></tr>";
            }
            ?>
        </tbody>
    </table>


        <h2>Late Students</h2>
        <table>
            <thead>
                <tr>
                    <th>Student ID</th>
                    <th>Name</th>
                    <th>Reason</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $late_students_query = "SELECT students.student_id, students.name, attendance.timestamp
                                        FROM students
                                        JOIN attendance ON students.student_id = attendance.student_id
                                        WHERE attendance.status = 'Out' 
                                        AND TIME(attendance.timestamp) > '22:00:00' 
                                        AND attendance.date = CURDATE()";

                $late_result = $conn->query($late_students_query);
                if ($late_result->num_rows > 0) {
                    while ($late_row = $late_result->fetch_assoc()) {
                        echo "<tr>
                                <td>{$late_row['student_id']}</td>
                                <td>{$late_row['name']}</td>
                                <td>Late</td>
                              </tr>";
                    }
                } else {
                    echo "<tr><td colspan='3'>No late students today.</td></tr>";
                }
                ?>
            </tbody>
        </table>

        <h2>Request Leave</h2>
<form method="POST" action="exceptions.php">
    <input type="text" name="student_id" placeholder="Student ID" required>
    <input type="datetime-local" name="in_date" required>
    <input type="datetime-local" name="return_date" required>
    <textarea name="reason" placeholder="Reason for absence"></textarea>
    <input type="submit" value="Request Leave">
</form>

<?php
$exceptions_query = "SELECT exceptions.exception_id, students.student_id, students.name, exceptions.start_date, exceptions.end_date, exceptions.reason, 
                    exceptions.status
                    FROM exceptions
                    JOIN students ON students.id = exceptions.student_id";

$exceptions_result = $conn->query($exceptions_query);

if (!$exceptions_result) {
    die("Query failed: " . $conn->error);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['deactivate_id'])) {
    $deactivate_id = $_POST['deactivate_id'];
    
    $deactivate_query = "UPDATE exceptions SET status = 'Resolved' WHERE exception_id = ?";
    $stmt = $conn->prepare($deactivate_query);
    $stmt->bind_param("i", $deactivate_id);
    $stmt->execute();
    $stmt->close();

    $student_id_query = "SELECT student_id FROM exceptions WHERE exception_id = ?";
    $stmt = $conn->prepare($student_id_query);
    $stmt->bind_param("i", $deactivate_id);
    $stmt->execute();
    $stmt->bind_result($student_id); 
    $stmt->fetch();
    $stmt->close();

    
    $update_student_stmt = $conn->prepare("UPDATE students SET exception = 0 WHERE id = ?");
    $update_student_stmt->bind_param("i", $student_id);
    if (!$update_student_stmt->execute()) {
        die("Failed to update student exception status: " . $update_student_stmt->error);
    }
    $update_student_stmt->close();

    
    $update_attendance_query = "UPDATE attendance 
                                SET status = 'In' 
                                WHERE student_id = ?";
    $stmt = $conn->prepare($update_attendance_query);
    $stmt->bind_param("i", $student_id); 
    if (!$stmt->execute()) {
        die("Failed to update attendance status: " . $stmt->error);
    }
    $stmt->close();
}
?>

<div class="container">
    <h2>Exceptions - Leave Requests</h2>
    <table>
        <thead>
            <tr>
                <th>Student ID</th>
                <th>Name</th>
                <th>Start Date</th>
                <th>End Date</th>
                <th>Reason</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($exceptions_result->num_rows > 0) {
                while ($row = $exceptions_result->fetch_assoc()) {
                    $status_class = ($row['status'] === 'Active') ? 'active' : '';
                    echo "<tr class='$status_class'>
                            <td>{$row['student_id']}</td>
                            <td>{$row['name']}</td>
                            <td>{$row['start_date']}</td>
                            <td>{$row['end_date']}</td>
                            <td>{$row['reason']}</td>
                            <td class='$status_class'>{$row['status']}</td>
                            <td>
                                <form method='POST' action='' style='display:inline;' onsubmit='return confirm(\"Are you sure you want to deactivate this exception?\")'>
                                    <input type='hidden' name='deactivate_id' value='{$row['exception_id']}'>
                                    <input type='submit' class='status-button' value='Deactivate'>
                                </form>
                            </td>
                        </tr>";
                }
            } else {
                echo "<tr><td colspan='7'>No exceptions found.</td></tr>";
            }
            ?>
        </tbody>
    </table>
        </div>
        
        <h2><a href="register.php" class="button-link">Register New Student</a></h2>
    </div>
</body>
</html>
