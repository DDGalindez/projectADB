<?php
session_start();

// Check if user is an admin
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header('location: login.php');
    exit();
}

// Database connection
$db = mysqli_connect('localhost', 'root', '', 'project');
if (!$db) {
    die("Connection failed: " . mysqli_connect_error());
}

// Check if enrollment ID is provided
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    // Fetch the pending enrollment details
    $pending_query = "SELECT * FROM pending_enrollments WHERE id = $id";
    $pending_result = mysqli_query($db, $pending_query);

    if (!$pending_result || mysqli_num_rows($pending_result) == 0) {
        die("Error: Enrollment record not found.");
    }

    $pending_data = mysqli_fetch_assoc($pending_result);

    // If form is submitted
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $section = mysqli_real_escape_string($db, $_POST['section']);

        // Validate that the section is selected
        if (empty($section)) {
            echo "<p style='color:red;'>Error: Section is required.</p>";
        } else {
            // Check if username already exists in the students table
            $username = $pending_data['username'];  // Use the username from the pending enrollment data

            $check_query = "SELECT * FROM students WHERE username = '$username'";
            $check_result = mysqli_query($db, $check_query);
            if (mysqli_num_rows($check_result) > 0) {
                echo "<p style='color:red;'>Error: Username already exists. Please use a unique username.</p>";
            } else {
                // Insert the approved student into the students table
                $insert_query = "INSERT INTO students (username, first_name, last_name, email, section) 
                                 VALUES ('$username', '{$pending_data['first_name']}', '{$pending_data['last_name']}', '{$pending_data['email']}', '$section')";
                if (mysqli_query($db, $insert_query)) {
                    // Delete the pending enrollment record
                    $delete_query = "DELETE FROM pending_enrollments WHERE id = $id";
                    mysqli_query($db, $delete_query);

                    // Redirect to pending enrollments page with success message
                    header('location: admin_pending_enrollments.php?success=1');
                    exit();
                } else {
                    echo "<p style='color:red;'>Error inserting student: " . mysqli_error($db) . "</p>";
                }
            }
        }
    }
} else {
    die("Error: No enrollment ID provided.");
}

// Close the database connection
mysqli_close($db);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Approve Enrollment</title>
    <link rel="stylesheet" href="admin_styles.css">
</head>
<body>
    <h2>Approve Enrollment</h2>

    <form method="POST" action="">
        <p><strong>First Name:</strong> <?php echo htmlspecialchars($pending_data['first_name']); ?></p>
        <p><strong>Last Name:</strong> <?php echo htmlspecialchars($pending_data['last_name']); ?></p>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($pending_data['email']); ?></p>

        <!-- Display the username automatically from the pending enrollment -->
        <p><strong>Username:</strong> <?php echo htmlspecialchars($pending_data['username']); ?></p>

        <label for="section">Section:</label><br>
        <select id="section" name="section" required>
            <option value="" disabled selected>Select a Section</option>
            <option value="Section 1">Section 1</option>
            <option value="Section 2">Section 2</option>
            <option value="Section 3">Section 3</option>
            <option value="Section 4">Section 4</option>
            <option value="Section 5">Section 5</option>
        </select><br><br>

        <button type="submit">Approve</button>
        <a href="admin_pending_enrollments.php">Cancel</a>
    </form>
</body>
</html>
