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

// Fetch the logged-in student's username
$student_username = $_SESSION['username'];

// Query to fetch subjects and grades for the logged-in student
$query = "
    SELECT 
        subjects.subject_name, 
        grades.grade 
    FROM 
        grades 
    JOIN 
        subjects 
    ON 
        grades.subject_id = subjects.id 
    JOIN 
        students 
    ON 
        grades.student_id = students.id 
    WHERE 
        students.username = '$student_username'
";

$result = mysqli_query($db, $query);

if (!$result) {
    die("Error in query: " . mysqli_error($db));
}

// Initialize variables for calculating the overall percentage
$total_grades = 0;
$grades_count = 0;

// Loop through results to calculate totals for percentage
while ($row = mysqli_fetch_assoc($result)) {
    $total_grades += $row['grade']; // Sum all grades
    $grades_count++; // Count the number of grades
}

// Reset the pointer for displaying rows
mysqli_data_seek($result, 0);

// Calculate overall percentage
$overall_percentage = ($grades_count > 0) ? ($total_grades / $grades_count) : 0;

mysqli_close($db);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student - View Grades</title>
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

    <h2>Your Grades</h2>

    <!-- Grades Table -->
    <table border="1">
        <thead>
            <tr>
                <th>Subject</th>
                <th>Grade</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if (mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row['subject_name']) . "</td>"; // Display subject name
                    echo "<td>" . htmlspecialchars($row['grade']) . "</td>"; // Display grade
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='2'>No grades available.</td></tr>";
            }
            ?>
        </tbody>
    </table>

    <!-- Overall Percentage -->
    <h3>Overall Percentage: <?php echo number_format($overall_percentage, 2); ?>%</h3>
</body>
</html>
