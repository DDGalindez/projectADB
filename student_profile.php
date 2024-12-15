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
$username = $_SESSION['username'];
$query = "SELECT * FROM students WHERE username = '$username'";
$result = mysqli_query($db, $query);

if ($result && mysqli_num_rows($result) > 0) {
    $student = mysqli_fetch_assoc($result);
} else {
    die("Error: Student details not found.");
}

mysqli_close($db);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Profile</title>
    <link rel="stylesheet" href="student_profile.css">
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
        <h2>Welcome, <?php echo $_SESSION['username']; ?>!</h2>
        <h3>Your Profile Details</h3>

        <table>
            <tr>
                <th>First Name</th>
                <td><?php echo htmlspecialchars($student['first_name']); ?></td>
            </tr>
            <tr>
                <th>Last Name</th>
                <td><?php echo htmlspecialchars($student['last_name']); ?></td>
            </tr>
            <tr>
                <th>Email</th>
                <td><?php echo htmlspecialchars($student['email']); ?></td>
            </tr>
            <tr>
                <th>Username</th>
                <td><?php echo htmlspecialchars($student['username']); ?></td>
            </tr>
            <tr>
                <th>Section</th>
                <td><?php echo htmlspecialchars($student['section']); ?></td>
            </tr>
        </table>

    </div>

</body>
</html>
