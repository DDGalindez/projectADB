<?php
session_start();

// Check if the user is an admin
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header('location: login.php');
    exit();
}

// Database connection
$db = mysqli_connect('localhost', 'root', '', 'project');
if (!$db) {
    die("Connection failed: " . mysqli_connect_error());
}

// Handle form submission for adding a task to an existing subject
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_task'])) {
    // Get form data
    $subject_id = mysqli_real_escape_string($db, $_POST['subject_id']);

    // Handle file upload
    $file_name = $_FILES['file']['name'];
    $file_tmp = $_FILES['file']['tmp_name'];
    $file_size = $_FILES['file']['size'];
    $file_error = $_FILES['file']['error'];

    // Define the allowed file types
    $allowed_file_types = ['pdf', 'docx', 'txt'];
    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

    // Check if the file is of the allowed type
    if (!in_array($file_ext, $allowed_file_types)) {
        echo "Error: Only PDF, DOCX, and TXT files are allowed.";
    } elseif ($file_error !== 0) {
        echo "Error: There was an issue with the file upload.";
    } else {
        // Define the target directory to store the uploaded file
        $upload_dir = __DIR__ . '/uploads/';
        $file_path = $upload_dir . uniqid() . '.' . $file_ext;

        // Check if the uploads directory exists, create it if it doesn't
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true); // Create the directory if it doesn't exist
        }

        // Move the uploaded file to the target directory
        if (move_uploaded_file($file_tmp, $file_path)) {
            // Insert the task file for the selected subject into the database
            $insert_query = "INSERT INTO tasks (subject_id, file_path) 
                             VALUES ('$subject_id', '$file_path')";
            if (mysqli_query($db, $insert_query)) {
                echo "Task added successfully!";
            } else {
                echo "Error: " . mysqli_error($db);
            }
        } else {
            echo "Error: Could not upload the file.";
        }
    }
}

// Fetch all subjects for the dropdown
$subjects_query = "SELECT * FROM subjects";
$subjects_result = mysqli_query($db, $subjects_query);

// Fetch all tasks for display
$tasks_query = "SELECT tasks.*, subjects.subject_name FROM tasks
                JOIN subjects ON tasks.subject_id = subjects.id";
$tasks_result = mysqli_query($db, $tasks_query);

mysqli_close($db);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Manage Subjects and Tasks</title>
    <link rel="stylesheet" href="admin_dashboard.css">
</head>
<body>
    <!-- Navbar -->
    <div class="navbar">
        <a href="#" class="logo">Student Dashboard</a>
        <div class="links">
            <a href="admin_dashboard.php">Home</a>
            <a href="admin_pending_enrollments.php">Pending Enrollments</a>
            <a href="admin_manage_subject.php">Manage Subject</a>
            <a href="admin_manage_students.php">Manage Students</a> <!-- Added Manage Students link -->
            <a href="admin_manage_grades.php">Manage Grades</a>
            <a href="admin_manage_attendance.php">Manage Attendance</a>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </div>
    <h2>Manage Subjects and Add Tasks</h2>

    <!-- Form for adding a task to an existing subject -->
    <form method="POST" action="admin_manage_subject.php" enctype="multipart/form-data">
        <label for="subject_id">Select Subject:</label>
        <select id="subject_id" name="subject_id" required>
            <?php
            // Populate the dropdown with existing subjects
            if (mysqli_num_rows($subjects_result) > 0) {
                while ($row = mysqli_fetch_assoc($subjects_result)) {
                    echo "<option value='" . $row['id'] . "'>" . htmlspecialchars($row['subject_name']) . "</option>";
                }
            }
            ?>
        </select>

        <label for="file">Upload Task File:</label>
        <input type="file" id="file" name="file" required>

        <button type="submit" name="submit_task">Add Task</button>
    </form>

    <h3>Existing Tasks</h3>
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
                    echo "<td><a href='uploads/" . basename($row['file_path']) . "' target='_blank'>View Task File</a></td>";
                    echo "<td><a href='delete_task.php?id=" . $row['id'] . "'>Delete</a></td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='3'>No tasks found.</td></tr>";
            }
            ?>
        </tbody>
    </table>
</body>
</html>
