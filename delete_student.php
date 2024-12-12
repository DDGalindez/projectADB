<?php
session_start();

// Check if the user is logged in as an admin
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header('location: login.php');
    exit();
}

// Database connection
$db = mysqli_connect('localhost', 'root', '', 'project');

if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}

// Get the student ID from the URL parameter
if (isset($_GET['id'])) {
    $student_id = mysqli_real_escape_string($db, $_GET['id']);

    // First, delete the related records in the student_subjects table
    $delete_subjects_query = "DELETE FROM student_subjects WHERE student_id = '$student_id'";
    mysqli_query($db, $delete_subjects_query);

    // Now, delete the student record from the students table
    $delete_student_query = "DELETE FROM students WHERE id = '$student_id'";

    if (mysqli_query($db, $delete_student_query)) {
        // Redirect to the admin dashboard after successful deletion
        header('Location: admin_dashboard.php');
        exit();
    } else {
        echo "Error: " . mysqli_error($db); // Display the error message if deletion fails
    }
} else {
    echo "No student ID provided!";
}

// Close the database connection
mysqli_close($db);
?>
