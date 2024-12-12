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

// Fetch the student to be edited
if (isset($_GET['id'])) {
    $student_id = $_GET['id'];
    $query = "SELECT * FROM students WHERE id = $student_id";
    $result = mysqli_query($db, $query);
    $student = mysqli_fetch_assoc($result);
}

// Handle form submission for updating student
if (isset($_POST['update_student'])) {
    // Get the student data from the form
    $first_name = mysqli_real_escape_string($db, $_POST['first_name']);
    $last_name = mysqli_real_escape_string($db, $_POST['last_name']);
    $email = mysqli_real_escape_string($db, $_POST['email']);
    $section = mysqli_real_escape_string($db, $_POST['section']);  // Get the selected section

    // Update the student in the database
    $update_query = "UPDATE students SET first_name = '$first_name', last_name = '$last_name', email = '$email', section = '$section' WHERE id = $student_id";

    if (mysqli_query($db, $update_query)) {
        header('Location: admin_dashboard.php');
        exit();
    } else {
        echo "Error: " . mysqli_error($db);
    }
}

mysqli_close($db);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Student</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 50%;
            margin: 50px auto;
            padding: 20px;
            background-color: white;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }

        h2 {
            text-align: center;
            color: #333;
        }

        form {
            display: flex;
            flex-direction: column;
        }

        label {
            font-weight: bold;
            margin-bottom: 5px;
            color: #333;
        }

        input, select {
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 14px;
        }

        button {
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }

        button:hover {
            background-color: #45a049;
        }

        .form-group {
            margin-bottom: 15px;
        }
    </style>
</head>
<body>

    <div class="container">
        <h2>Edit Student</h2>
        <form method="POST" action="edit_student.php?id=<?php echo $student['id']; ?>">
            <div class="form-group">
                <label for="first_name">First Name:</label>
                <input type="text" name="first_name" value="<?php echo $student['first_name']; ?>" required>
            </div>

            <div class="form-group">
                <label for="last_name">Last Name:</label>
                <input type="text" name="last_name" value="<?php echo $student['last_name']; ?>" required>
            </div>

            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" name="email" value="<?php echo $student['email']; ?>" required>
            </div>

            <div class="form-group">
                <label for="section">Section:</label>
                <select name="section" required>
                    <option value="1" <?php echo ($student['section'] == 1) ? 'selected' : ''; ?>>Section 1</option>
                    <option value="2" <?php echo ($student['section'] == 2) ? 'selected' : ''; ?>>Section 2</option>
                    <option value="3" <?php echo ($student['section'] == 3) ? 'selected' : ''; ?>>Section 3</option>
                    <option value="4" <?php echo ($student['section'] == 4) ? 'selected' : ''; ?>>Section 4</option>
                    <option value="5" <?php echo ($student['section'] == 5) ? 'selected' : ''; ?>>Section 5</option>
                </select>
            </div>

            <button type="submit" name="update_student">Update Student</button>
        </form>
    </div>

</body>
</html>
