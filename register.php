<?php
include 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $student_id = $_POST['student_id'];
    $name = $_POST['name'];
    $room_number = $_POST['room_number'];
    $parent_email = $_POST['parent_email'];

    $stmt = $conn->prepare("INSERT INTO students (student_id, name, room_number, parent_email) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $student_id, $name, $room_number, $parent_email);

    if ($stmt->execute()) {
        echo "New student registered successfully!";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();

    // Redirect to homepage after adding the student
    header("Location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Student</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
        }

        body {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background-color: #f5f7fa;
        }

        .container {
            width: 400px;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        h1 {
            font-size: 1.5em;
            margin-bottom: 20px;
            text-align: center;
            color: #6a0dad;
        }

        .registration-form {
            display: flex;
            flex-direction: column;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            font-size: 0.9em;
            color: #555;
            margin-bottom: 5px;
        }

        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1em;
        }

        .form-group input:focus {
            border-color: #6a0dad;
            outline: none;
        }

        .submit-button {
            padding: 10px;
            background-color: #6a0dad;
            border: none;
            color: #fff;
            font-size: 1em;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .submit-button:hover {
            background-color: #4b0082;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Register New Student</h1>
        <form method="POST" action="register.php" class="registration-form">
            <div class="form-group">
                <label for="student_id">Student ID</label>
                <input type="text" name="student_id" id="student_id" placeholder="Enter Student ID" required>
            </div>
            <div class="form-group">
                <label for="name">Name</label>
                <input type="text" name="name" id="name" placeholder="Enter Name" required>
            </div>
            <div class="form-group">
                <label for="room_number">Room Number</label>
                <input type="text" name="room_number" id="room_number" placeholder="Enter Room Number" required>
            </div>
            <div class="form-group">
                <label for="parent_email">Parent Email</label>
                <input type="email" name="parent_email" id="parent_email" placeholder="Enter Parent Email" required>
            </div>
            <button type="submit" class="submit-button">Register</button>
        </form>
    </div>
</body>
</html>

<?php
$conn->close();
?>
