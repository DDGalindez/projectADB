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

// Fetch subjects for the student
$student_query = "SELECT * FROM students WHERE email = '" . $_SESSION['username'] . "'";
$student_result = mysqli_query($db, $student_query);
$student = mysqli_fetch_assoc($student_result);

// Fetch subjects the student is enrolled in
$subjects_query = "SELECT s.subject_name FROM subjects s 
                   JOIN enrollments e ON s.id = e.subject_id
                   WHERE e.student_id = '" . $student['id'] . "'";
$subjects_result = mysqli_query($db, $subjects_query);

// Close the database connection
mysqli_close($db);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Subjects</title>
    <link rel="stylesheet" href="admin_dashboard.css">
</head>
<body>

    <!-- Navbar -->
    <div class="navbar">
        <a href="#" class="logo">Student Dashboard</a>
        <div class="links">
            <a href="student_dashboard.php">Home</a>
            <a href="student_profile.php">View Profile</a>
            <a href="student_manage_subjects.php">Manage Subjects</a>
            <a href="student_view_grades.php">View Grades</a>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="content">
        <h2>Your Subjects</h2>
        <?php if (mysqli_num_rows($subjects_result) > 0): ?>
            <ul>
                <?php while ($subject = mysqli_fetch_assoc($subjects_result)): ?>
                    <li><?php echo $subject['subject_name']; ?></li>
                <?php endwhile; ?>
            </ul>
        <?php else: ?>
            <p>You are not enrolled in any subjects.</p>
        <?php endif; ?>
    </div>

</body>
</html>
