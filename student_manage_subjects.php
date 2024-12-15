<?php
session_start();

// Check if the user is a student
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'student') {
    header('location: login.php');
    exit();
}

// Database connection
$db = mysqli_connect('localhost', 'root', '', 'project');
if (!$db) {
    die("Connection failed: " . mysqli_connect_error());
}

// Fetch all tasks along with their associated subjects
$tasks_query = "SELECT tasks.id, tasks.file_path, subjects.subject_name 
                FROM tasks 
                JOIN subjects ON tasks.subject_id = subjects.id";
$tasks_result = mysqli_query($db, $tasks_query);

mysqli_close($db);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student - Manage Subjects</title>
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
            <a href="student_view_grades.php">View Grades</a>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </div>

    <h2>View Tasks</h2>

    <h3>Available Tasks</h3>
    <table border="1">
        <thead>
            <tr>
                <th>Subject Name</th>
                <th>Task File</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if (mysqli_num_rows($tasks_result) > 0) {
                while ($row = mysqli_fetch_assoc($tasks_result)) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row['subject_name']) . "</td>";
                    echo "<td><a href='uploads/" . basename($row['file_path']) . "' target='_blank'>View File</a></td>";
                    echo "<td><a href='uploads/" . basename($row['file_path']) . "' download>Download</a></td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='3'>No tasks available.</td></tr>";
            }
            ?>
        </tbody>
    </table>
</body>
</html>
