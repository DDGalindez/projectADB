<?php
session_start();

// Check if the user is logged in as an admin
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header('location: login.php');
    exit();
}

// Database connection
$db = mysqli_connect('localhost', 'root', '', 'project');
if (!$db) {
    die("Connection failed: " . mysqli_connect_error());
}

// Check if student ID is provided in the URL
if (isset($_GET['id'])) {
    $student_id = mysqli_real_escape_string($db, $_GET['id']);

    // Step 1: Check if there are any dependent records (e.g., enrollments)
    $check_enrollments = "SELECT * FROM enrollments WHERE student_id = '$student_id'";
    $result = mysqli_query($db, $check_enrollments);

    if (!$result) {
        die("Error checking enrollments: " . mysqli_error($db));
    }

    if (mysqli_num_rows($result) > 0) {
        // Step 2: If dependent records exist, delete them first
        $delete_enrollments = "DELETE FROM enrollments WHERE student_id = '$student_id'";
        if (mysqli_query($db, $delete_enrollments)) {
            echo "Related enrollment records deleted successfully.<br>";
        } else {
            die("Error deleting related records: " . mysqli_error($db));
        }
    }

    // Step 3: Now, delete the student record
    $delete_student = "DELETE FROM students WHERE id = '$student_id'";
    if (mysqli_query($db, $delete_student)) {
        $_SESSION['message'] = "Student record deleted successfully.";
        header('Location: admin_dashboard.php');  // Redirect to the dashboard after successful deletion
        exit();
    } else {
        $_SESSION['error'] = "Error deleting student: " . mysqli_error($db);
    }
} else {
    $_SESSION['error'] = "No student ID provided.";
    header('Location: admin_dashboard.php'); // Redirect back to the dashboard if no ID is provided
    exit();
}

// Close the database connection
mysqli_close($db);
?>
