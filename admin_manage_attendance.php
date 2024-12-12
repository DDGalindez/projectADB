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

// Get current month and year
$month = date('m');
$year = date('Y');
$days_in_month = cal_days_in_month(CAL_GREGORIAN, $month, $year); // Number of days in the current month

// Fetch students
$students_query = "SELECT * FROM students";
$students_result = mysqli_query($db, $students_query);

// Handle attendance form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $attendance_date = mysqli_real_escape_string($db, $_POST['attendance_date']);
    $student_id = mysqli_real_escape_string($db, $_POST['student_id']);
    $status = mysqli_real_escape_string($db, $_POST['status']); // 1 for present, 0 for absent

    // Check if attendance already exists for the day
    $check_query = "SELECT * FROM attendance WHERE student_id = '$student_id' AND attendance_date = '$attendance_date'";
    $check_result = mysqli_query($db, $check_query);

    if (mysqli_num_rows($check_result) > 0) {
        // Update attendance if it already exists
        $update_query = "UPDATE attendance SET status = '$status' WHERE student_id = '$student_id' AND attendance_date = '$attendance_date'";
        mysqli_query($db, $update_query);
    } else {
        // Insert new attendance if it doesn't exist
        $insert_query = "INSERT INTO attendance (student_id, attendance_date, status) VALUES ('$student_id', '$attendance_date', '$status')";
        mysqli_query($db, $insert_query);
    }
}

// Fetch attendance for the current month
$attendance_data = [];
$attendance_query = "SELECT * FROM attendance WHERE MONTH(attendance_date) = '$month' AND YEAR(attendance_date) = '$year'";
$attendance_result = mysqli_query($db, $attendance_query);

while ($row = mysqli_fetch_assoc($attendance_result)) {
    $attendance_data[$row['student_id']][$row['attendance_date']] = $row['status'];
}

mysqli_close($db);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Attendance</title>
    <link rel="stylesheet" href="admin_dashboard.css">
    <style>
        .attendance-table {
            width: 100%;
            border-collapse: collapse;
        }

        .attendance-table th, .attendance-table td {
            padding: 10px;
            text-align: center;
            border: 1px solid #ccc;
        }

        .attendance-table th {
            background-color: #f4f4f4;
        }

        .present {
            background-color: green;
            color: white;
        }

        .absent {
            background-color: red;
            color: white;
        }

        .attendance-table td {
            cursor: pointer;
        }

        .attendance-table td:hover {
            background-color: #f0f0f0;
        }
    </style>
</head>
<body>

    <!-- Navbar -->
    <div class="navbar">
        <a href="#" class="logo">Admin Dashboard</a>
        <div class="links">
            <a href="admin_dashboard.php">Home</a>
            <a href="admin_manage_students.php">Manage Students</a>
            <a href="admin_manage_grades.php">Manage Grades</a>
            <a href="admin_attendance.php">Manage Attendance</a>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="content">
        <h2>Attendance for <?php echo date('F Y'); ?></h2>

        <form method="POST" action="admin_manage_attendance.php">
            <table class="attendance-table">
                <tr>
                    <th>Student Name</th>
                    <?php
                    // Display day headers for the current month
                    for ($i = 1; $i <= $days_in_month; $i++) {
                        echo "<th>$i</th>";
                    }
                    ?>
                </tr>
                <?php while ($student = mysqli_fetch_assoc($students_result)): ?>
                    <tr>
                        <td><?php echo $student['first_name'] . ' ' . $student['last_name']; ?></td>

                        <?php
                        for ($i = 1; $i <= $days_in_month; $i++) {
                            $date = "$year-$month-" . str_pad($i, 2, "0", STR_PAD_LEFT);
                            $status = isset($attendance_data[$student['id']][$date]) ? $attendance_data[$student['id']][$date] : null;

                            // Determine the class based on the attendance status
                            $class = $status === "1" ? "present" : ($status === "0" ? "absent" : "");

                            echo "<td class='$class' data-student-id='{$student['id']}' data-date='$date' onclick='markAttendance(this)'></td>";
                        }
                        ?>
                    </tr>
                <?php endwhile; ?>
            </table>
        </form>
    </div>

    <script>
        // Mark attendance when clicking on a day
        function markAttendance(cell) {
            let studentId = cell.dataset.studentId;
            let date = cell.dataset.date;
            let status = cell.classList.contains('present') ? '0' : '1'; // Toggle between present (1) and absent (0)

            // Send an AJAX request to update the attendance
            let formData = new FormData();
            formData.append('student_id', studentId);
            formData.append('attendance_date', date);
            formData.append('status', status);

            fetch('admin_manage_attendance.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(() => {
                // Update the cell's class
                if (status === '1') {
                    cell.classList.add('present');
                    cell.classList.remove('absent');
                } else {
                    cell.classList.add('absent');
                    cell.classList.remove('present');
                }
            })
            .catch(error => console.error('Error:', error));
        }
    </script>

</body>
</html>
