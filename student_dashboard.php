<?php
session_start();

// Check if the user is logged in as a student
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'student') {
    header('location: login.php');
    exit();
}

// Database connection
$db = mysqli_connect('localhost', 'root', '', 'project');
if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}

// Handle the enrollment form submission
if (isset($_POST['enroll'])) {
    $first_name = mysqli_real_escape_string($db, $_POST['first_name']);
    $last_name = mysqli_real_escape_string($db, $_POST['last_name']);
    $email = mysqli_real_escape_string($db, $_POST['email']);

    // Insert into pending enrollments table
    $insert_enrollment = "INSERT INTO pending_enrollments (first_name, last_name, email, status) 
                          VALUES ('$first_name', '$last_name', '$email', 'pending')";

    if (mysqli_query($db, $insert_enrollment)) {
        echo "Enrollment request submitted successfully!";
    } else {
        echo "Error: " . mysqli_error($db);
    }
}

mysqli_close($db);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
    <link rel="stylesheet" href="student_dashboard.css">
</head>
<body>

    <!-- Navbar -->
    <div class="navbar">
        <a href="#" class="logo">Student Dashboard</a>
        <div class="links">
            <a href="student_dashboard.php">Home</a>
            <a href="student_profile.php">View Profile</a>
            <a href="student_manage_subjects.php">Manage Subjects</a>
            <a href="student_grades.php">View Grades</a>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="content">
        <h2>Welcome, <?php echo $_SESSION['username']; ?>!</h2>

        <h3>Enrollment Form</h3>
        <p>Fill in your details to request enrollment:</p>

        <form method="POST" action="student_dashboard.php">
            <label for="first_name">First Name:</label>
            <input type="text" id="first_name" name="first_name" required>

            <label for="last_name">Last Name:</label>
            <input type="text" id="last_name" name="last_name" required>

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>

            <button type="submit" name="enroll">Submit Enrollment Request</button>
        </form>
    </div>

</body>
</html>
