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

// Fetch all pending enrollments
$query = "SELECT * FROM pending_enrollments WHERE status = 'pending'";
$result = mysqli_query($db, $query);

// Check if there are any pending enrollments
$pending_enrollments = [];
if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $pending_enrollments[] = $row;
    }
} else {
    $message = "No pending enrollments found.";
}

mysqli_close($db);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="admin_dashboard.css">
    <style>
        /* Center the content */
        .approval-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            margin-top: 50px;
        }
        .approval-container p {
            text-align: center;
        }
        .approval-container a {
            display: inline-block;
            margin-top: 10px;
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
        .approval-container a:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>

    <!-- Navbar -->
    <div class="navbar">
        <a href="#" class="logo">Admin Dashboard</a>
        <div class="links">
            <a href="admin_dashboard.php">Home</a>
            <a href="admin_pending_enrollments.php">Pending Enrollments</a>
            <a href="admin_manage_subject.php">Manage Subject</a>
            <a href="admin_manage_students.php">Manage Students</a> 
            <a href="admin_manage_grades.php">Manage Grades</a>
            <a href="admin_manage_attendance.php">Manage Attendance</a>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="approval-container">
        <h2>Pending Enrollments</h2>

        <?php
        if (isset($message)) {
            echo "<p>$message</p>";
        } else {
            foreach ($pending_enrollments as $row) {
                echo "<p>";
                echo "First Name: " . $row['first_name'] . "<br>";
                echo "Last Name: " . $row['last_name'] . "<br>";
                echo "Email: " . $row['email'] . "<br>";
                echo "<a href='admin_approval_enrollment.php?id=" . $row['id'] . "'>Approve Enrollment</a>";
                echo "</p><hr>";
            }
        }
        ?>
    </div>

</body>
</html>
