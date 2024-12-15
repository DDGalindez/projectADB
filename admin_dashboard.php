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

// Fetch subjects for automatic association
$subjects_query = "SELECT id FROM subjects";
$subjects_result = mysqli_query($db, $subjects_query);

// Handle form submission to add a new student
if (isset($_POST['add_student'])) {
    $student_id = mysqli_real_escape_string($db, $_POST['student_id']);
    $first_name = mysqli_real_escape_string($db, $_POST['first_name']);
    $last_name = mysqli_real_escape_string($db, $_POST['last_name']);
    $email = mysqli_real_escape_string($db, $_POST['email']);
    $username = mysqli_real_escape_string($db, $_POST['username']); // Get the manually added username
    $section = mysqli_real_escape_string($db, $_POST['section']); // Get the selected section

    // Ensure the student fields are not empty
    if (empty($student_id) || empty($first_name) || empty($last_name) || empty($email) || empty($username) || empty($section)) {
        echo "All fields are required!";
    } else {
        // Insert the new student into the database
        $insert_student_query = "INSERT INTO students (id, first_name, last_name, email, username, section) 
                                  VALUES ('$student_id', '$first_name', '$last_name', '$email', '$username', '$section')";

        if (mysqli_query($db, $insert_student_query)) {
            // Get the student ID of the newly inserted student
            $student_id = mysqli_insert_id($db);

            // Insert the student into the 5 subjects
            while ($subject_row = mysqli_fetch_assoc($subjects_result)) {
                $subject_id = $subject_row['id'];
                $insert_subject_query = "INSERT INTO student_subjects (student_id, subject_id) 
                                          VALUES ('$student_id', '$subject_id')";
                mysqli_query($db, $insert_subject_query);
            }

            // Redirect to the dashboard after successful insertion
            header('Location: admin_dashboard.php');
            exit();
        } else {
            echo "Error: " . mysqli_error($db); // Display the error message if insertion fails
        }
    }
}

// Handle search functionality
$search_query = "";
if (isset($_POST['search'])) {
    $search_input = mysqli_real_escape_string($db, $_POST['search_input']);
    $search_query = "WHERE id LIKE '%$search_input%' OR first_name LIKE '%$search_input%' OR last_name LIKE '%$search_input%'";
}

// Fetch students for display based on search
$students_query = "SELECT * FROM students $search_query";
$students_result = mysqli_query($db, $students_query);

// Close the database connection
mysqli_close($db);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="admin_dashboard.css">
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
        <h2>Welcome, Admin!</h2>
        <p>This is your dashboard where you can manage students, grades, and attendance records.</p>

        <!-- Add Student Form -->
        <h3>Add New Student</h3>
        <form method="POST" action="admin_dashboard.php">
            <label for="student_id">Student ID:</label>
            <input type="text" id="student_id" name="student_id" required>

            <label for="first_name">First Name:</label>
            <input type="text" id="first_name" name="first_name" required>

            <label for="last_name">Last Name:</label>
            <input type="text" id="last_name" name="last_name" required>

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>

            <!-- New Fields for Username and Section -->
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required>

            <label for="section">Section:</label>
            <select id="section" name="section" required>
                <option value="">Select Section</option>
                <option value="1">Section 1</option>
                <option value="2">Section 2</option>
                <option value="3">Section 3</option>
                <option value="4">Section 4</option>
                <option value="5">Section 5</option>
            </select>

            <button type="submit" name="add_student">Add Student</button>
        </form>

        <!-- Search Bar -->
        <h3>Search Students</h3>
        <form method="POST" action="admin_dashboard.php">
            <input type="text" name="search_input" placeholder="Search by ID, First Name, or Last Name">
            <button type="submit" name="search">Search</button>
        </form>

        <!-- Students Section -->
        <h3>Student Records</h3>
        <?php if (mysqli_num_rows($students_result) > 0): ?>
            <table>
                <tr>
                    <th>Student ID</th>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Email</th>
                    <th>Username</th>
                    <th>Section</th>
                    <th>Actions</th>
                </tr>
                <?php while($row = mysqli_fetch_assoc($students_result)): ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo $row['first_name']; ?></td>
                        <td><?php echo $row['last_name']; ?></td>
                        <td><?php echo $row['email']; ?></td>
                        <td><?php echo $row['username']; ?></td>
                        <td><?php echo $row['section']; ?></td> <!-- Display Section -->
                        <td>
                            <a href="edit_student.php?id=<?php echo $row['id']; ?>">Edit</a> |
                            <a href="delete_student.php?id=<?php echo $row['id']; ?>">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </table>
        <?php else: ?>
            <p>No students found.</p>
        <?php endif; ?>

    </div>

</body>
</html>
