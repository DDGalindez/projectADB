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

// Fetch students grouped by sections
$sections_query = "SELECT DISTINCT section FROM students ORDER BY section ASC";
$sections_result = mysqli_query($db, $sections_query);

// Close the database connection
mysqli_close($db);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Students</title>
    <link rel="stylesheet" href="admin_manage_students.css">
</head>
<body>

    <!-- Navbar -->
    <div class="navbar">
        <a href="#" class="logo">Admin Dashboard</a>
        <div class="links">
            <a href="admin_dashboard.php">Home</a>
            <a href="admin_manage_students.php">Manage Students</a>
            <a href="admin_manage_grades.php">Manage Grades</a>
            <a href="admin_manage_attendance.php">Manage Attendance</a>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="content">
        <h2>Manage Students</h2>

        <!-- Display Sections and Students -->
        <div class="section-container">
            <?php while ($section = mysqli_fetch_assoc($sections_result)): ?>
                <div class="section">
                    <h3>Section <?php echo htmlspecialchars($section['section']); ?></h3>

                    <?php
                        // Fetch students in the current section
                        $db = mysqli_connect('localhost', 'root', '', 'project');
                        $section_students_query = "SELECT * FROM students WHERE section = " . $section['section'];
                        $students_result = mysqli_query($db, $section_students_query);
                    ?>

                    <?php if (mysqli_num_rows($students_result) > 0): ?>
                        <div class="student-list">
                            <table>
                                <tr>
                                    <th>Student ID</th>
                                    <th>First Name</th>
                                    <th>Last Name</th>
                                    <th>Email</th>
                                </tr>
                                <?php while ($row = mysqli_fetch_assoc($students_result)): ?>
                                    <tr>
                                        <td><?php echo $row['id']; ?></td>
                                        <td><?php echo $row['first_name']; ?></td>
                                        <td><?php echo $row['last_name']; ?></td>
                                        <td><?php echo $row['email']; ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            </table>
                        </div>
                    <?php else: ?>
                        <p>No students found in this section.</p>
                    <?php endif; ?>

                    <?php mysqli_close($db); ?>
                </div>
            <?php endwhile; ?>
        </div>
    </div>

</body>
</html>
