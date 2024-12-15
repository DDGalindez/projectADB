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

// Fetch sections
$sections_query = "SELECT DISTINCT section FROM students ORDER BY section ASC";
$sections_result = mysqli_query($db, $sections_query);
if (!$sections_result) {
    die("Error fetching sections: " . mysqli_error($db));
}

// Initialize variables for student data
$students_result = null;
$selected_section = null;

// Fetch students only if a section is selected
if (isset($_GET['section'])) {
    $selected_section = mysqli_real_escape_string($db, $_GET['section']);
    $students_query = "SELECT * FROM students WHERE section = '$selected_section' ORDER BY first_name ASC";
    $students_result = mysqli_query($db, $students_query);
    if (!$students_result) {
        die("Error fetching students: " . mysqli_error($db));
    }
}
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
            <a href="admin_pending_enrollments.php">Pending Enrollments</a>
            <a href="admin_manage_students.php">Manage Students</a> <!-- Added Manage Students link -->
            <a href="admin_manage_grades.php">Manage Grades</a>
            <a href="admin_manage_attendance.php">Manage Attendance</a>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="content">
        <h2>Manage Students</h2>

        <!-- Display Sections -->
        <div class="section-container">
            <h3>Sections:</h3>
            <?php if (mysqli_num_rows($sections_result) > 0): ?>
                <ul>
                    <?php while ($section = mysqli_fetch_assoc($sections_result)): ?>
                        <li>
                            <a href="?section=<?php echo htmlspecialchars($section['section']); ?>">
                                Section <?php echo htmlspecialchars($section['section']); ?>
                            </a>
                        </li>
                    <?php endwhile; ?>
                </ul>
            <?php else: ?>
                <p>No sections found.</p>
            <?php endif; ?>
        </div>

        <!-- Display Students in the Selected Section -->
        <?php if ($selected_section): ?>
            <div class="student-list">
                <h3>Students in Section <?php echo htmlspecialchars($selected_section); ?>:</h3>
                <?php if ($students_result && mysqli_num_rows($students_result) > 0): ?>
                    <table>
                        <tr>
                            <th>Student ID</th>
                            <th>First Name</th>
                            <th>Last Name</th>
                            <th>Email</th>
                        </tr>
                        <?php while ($student = mysqli_fetch_assoc($students_result)): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($student['id']); ?></td>
                                <td><?php echo htmlspecialchars($student['first_name']); ?></td>
                                <td><?php echo htmlspecialchars($student['last_name']); ?></td>
                                <td><?php echo htmlspecialchars($student['email']); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </table>
                <?php else: ?>
                    <p>No students found in this section.</p>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <p>Please select a section to view students.</p>
        <?php endif; ?>
    </div>

</body>
</html>

<?php
// Close the database connection
mysqli_close($db);
?>
