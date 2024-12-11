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

// Fetch all students
$students_query = "SELECT * FROM students";
$students_result = mysqli_query($db, $students_query);

// Fetch all subjects
$subjects_query = "SELECT id, subject_name FROM subjects"; // Correct column name used here
$subjects_result = mysqli_query($db, $subjects_query);
$subjects = [];
while ($row = mysqli_fetch_assoc($subjects_result)) {
    $subjects[] = $row;
}

// Handle form submission to save grades
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($_POST['grades'] as $student_id => $grades) {
        foreach ($grades as $subject_id => $grade) {
            $grade = mysqli_real_escape_string($db, $grade);

            // Check if the grade already exists
            $check_query = "SELECT * FROM grades WHERE student_id = '$student_id' AND subject_id = '$subject_id'";
            $check_result = mysqli_query($db, $check_query);

            if (mysqli_num_rows($check_result) > 0) {
                // Update existing grade
                $update_query = "UPDATE grades SET grade = '$grade' WHERE student_id = '$student_id' AND subject_id = '$subject_id'";
                mysqli_query($db, $update_query);
            } else {
                // Insert new grade
                $insert_query = "INSERT INTO grades (student_id, subject_id, grade) VALUES ('$student_id', '$subject_id', '$grade')";
                mysqli_query($db, $insert_query);
            }
        }
    }

    // Redirect to the same page to prevent form resubmission
    header("Location: admin_manage_grades.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Grades</title>
    <link rel="stylesheet" href="admin_dashboard.css">
    <style>
        /* Table Styling */
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px; /* Smaller font size */
        }

        table th, table td {
            padding: 5px; /* Smaller padding */
            text-align: left;
            border: 1px solid #ccc;
        }

        table th {
            background-color: #f4f4f4;
            font-weight: bold;
        }

        h2, h3 {
            margin-top: 10px;
        }

        button {
            margin-top: 10px;
            padding: 5px 10px;
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
        }

        button:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
<div class="navbar">
    <a href="admin_dashboard.php">Dashboard</a>
    <a href="admin_manage_students.php">Manage Students</a>
    <a href="admin_manage_grades.php">Manage Grades</a>
    <a href="logout.php">Logout</a>
</div>

<h2>Manage Grades</h2>
<form method="POST" action="admin_manage_grades.php">
    <table>
        <tr>
            <th>Student ID</th>
            <th>First Name</th>
            <th>Last Name</th>
            <?php foreach ($subjects as $subject): ?>
                <th><?= htmlspecialchars($subject['subject_name']); ?></th>
            <?php endforeach; ?>
            <th>Total Grade</th>
            <th>Percentage</th>
        </tr>
        <?php while ($student = mysqli_fetch_assoc($students_result)): ?>
            <tr>
                <td><?= htmlspecialchars($student['id']); ?></td>
                <td><?= htmlspecialchars($student['first_name']); ?></td>
                <td><?= htmlspecialchars($student['last_name']); ?></td>
                <?php
                $total_grade = 0;
                $max_grade_per_subject = 100; // Change this if your max grade per subject is different
                $num_subjects = count($subjects);

                foreach ($subjects as $subject): 
                    $grade_query = "SELECT grade FROM grades WHERE student_id = '{$student['id']}' AND subject_id = '{$subject['id']}'";
                    $grade_result = mysqli_query($db, $grade_query);
                    $grade_row = mysqli_fetch_assoc($grade_result);
                    $grade = $grade_row['grade'] ?? '';
                    $total_grade += is_numeric($grade) ? $grade : 0;
                ?>
                    <td>
                        <input type="text" name="grades[<?= $student['id']; ?>][<?= $subject['id']; ?>]" 
                               value="<?= htmlspecialchars($grade); ?>">
                    </td>
                <?php endforeach; ?>
                <td><?= $total_grade; ?></td>
                <td>
                    <?php
                    $max_total_grade = $max_grade_per_subject * $num_subjects;
                    $percentage = $max_total_grade > 0 ? ($total_grade / $max_total_grade) * 100 : 0;
                    echo number_format($percentage, 2) . '%';
                    ?>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>
    <button type="submit">Save Grades</button>
</form>
</body>
</html>