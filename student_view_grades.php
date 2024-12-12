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

// Fetch student details from the database
$student_query = "SELECT * FROM students WHERE email = '" . $_SESSION['username'] . "'";
$student_result = mysqli_query($db, $student_query);
$student = mysqli_fetch_assoc($student_result);

// Fetch grades for the student
$grades_query = "SELECT g.subject_name, g.grade FROM grades g
                 JOIN enrollments e ON g.subject_id = e.subject_id
                 WHERE e.student_id = '" . $student['id'] . "'";
$grades_result = mysqli_query($db, $grades_query);

// Close the database connection
mysqli_close($db);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Grades</title>
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
        <h2>Your Grades</h2>
        <?php if (mysqli_num_rows($grades_result) > 0): ?>
            <table>
                <tr>
                    <th>Subject</th>
                    <th>Grade</th>
                </tr>
                <?php while ($grade = mysqli_fetch_assoc($grades_result)): ?>
                    <tr>
                        <td><?php echo $grade['subject_name']; ?></td>
                        <td><?php echo $grade['grade']; ?></td>
                    </tr>
                <?php endwhile; ?>
            </table>
        <?php else: ?>
            <p>You have no grades available.</p>
        <?php endif; ?>
    </div>

</body>
</html>
            