<?php
session_start();

// Check if the user is logged in as a student
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'student') {
    header('location: login.php');
    exit();
}

// Database connection
$db = mysqli_connect('localhost', 'root', '', 'project');
if (!$db) {
    die("Connection failed: " . mysqli_connect_error());
}

// Fetch student data
$student_id = $_SESSION['student_id'];  // Assuming student_id is stored in session

var_dump($student_id);  // Debugging: Check if student_id is set in session

if (isset($student_id)) {
    $query = "SELECT * FROM students WHERE id = '$student_id'";
    var_dump($query);  // Debugging: Check if query is correct
    $result = mysqli_query($db, $query);

    if ($result) {
        $student = mysqli_fetch_assoc($result);
        if ($student) {
            var_dump($student);  // Debugging: Check if student data is fetched
        } else {
            echo "Student not found.";
            exit();
        }
    } else {
        echo "Error: " . mysqli_error($db);
    }
} else {
    echo "Student ID not set.";
    exit();
}

mysqli_close($db);
?>
