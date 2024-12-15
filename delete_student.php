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

    // Step 1: Check for dependent records in the 'enrollments' table
    $check_enrollments = "SELECT * FROM enrollments WHERE student_id = '$student_id'";
    $result_enrollments = mysqli_query($db, $check_enrollments);

    if (!$result_enrollments) {
        die("Error checking enrollments: " . mysqli_error($db));
    }

    if (mysqli_num_rows($result_enrollments) > 0) {
        // If you had cascading deletes set up, you could skip this manual delete step.
        $delete_enrollments = "DELETE FROM enrollments WHERE student_id = '$student_id'";
        if (!mysqli_query($db, $delete_enrollments)) {
            $_SESSION['error'] = "Error deleting enrollment records: " . mysqli_error($db);
            header('Location: admin_dashboard.php');
            exit();
        }
    }

    // Step 2: Check for dependent records in the 'grades' table (if applicable)
    $check_grades = "SELECT * FROM grades WHERE student_id = '$student_id'";
    $result_grades = mysqli_query($db, $check_grades);

    if (!$result_grades) {
        die("Error checking grades: " . mysqli_error($db));
    }

    if (mysqli_num_rows($result_grades) > 0) {
        // If you had cascading deletes set up, you could skip this manual delete step.
        $delete_grades = "DELETE FROM grades WHERE student_id = '$student_id'";
        if (!mysqli_query($db, $delete_grades)) {
            $_SESSION['error'] = "Error deleting grade records: " . mysqli_error($db);
            header('Location: admin_dashboard.php');
            exit();
        }
    }

    // Step 3: Delete the student record
    $delete_student = "DELETE FROM students WHERE id = '$student_id'";
    if (mysqli_query($db, $delete_student)) {
        $_SESSION['message'] = "Student record deleted successfully.";
    } else {
        $_SESSION['error'] = "Error deleting student: " . mysqli_error($db);
    }

    header('Location: admin_dashboard.php'); // Redirect to the dashboard after deletion
    exit();
} else {
    $_SESSION['error'] = "No student ID provided.";
    header('Location: admin_dashboard.php'); // Redirect back to the dashboard if no ID is provided
    exit();
}

// Close the database connection
mysqli_close($db);
?>
