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

// Handle the subject addition form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_subject'])) {
    $subject_name = mysqli_real_escape_string($db, $_POST['subject_name']);
    $description = mysqli_real_escape_string($db, $_POST['description']);
    $file_path = "";

    // Handle file upload
    if (isset($_FILES['subject_file']) && $_FILES['subject_file']['error'] == 0) {
        $file_name = $_FILES['subject_file']['name'];
        $file_tmp = $_FILES['subject_file']['tmp_name'];
        $file_path = 'uploads/' . basename($file_name);

        // Move the uploaded file to the 'uploads' directory
        if (move_uploaded_file($file_tmp, $file_path)) {
            echo "File uploaded successfully.";
        } else {
            echo "Failed to upload file.";
        }
    }

    // Insert subject data into the database
    $insert_query = "INSERT INTO subjects (subject_name, description, file_path) 
                     VALUES ('$subject_name', '$description', '$file_path')";

    if (mysqli_query($db, $insert_query)) {
        echo "Subject added successfully.";
    } else {
        echo "Error: " . mysqli_error($db);
    }
}

// Fetch existing subjects from the database
$subjects_query = "SELECT * FROM subjects";
$subjects_result = mysqli_query($db, $subjects_query);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Manage Subjects</title>
    <link rel="stylesheet" href="admin_styles.css">
</head>
<body>
    <div class="navbar">
        <a href="#" class="logo">Admin Dashboard</a>
        <div class="links">
            <a href="admin_dashboard.php">Home</a>
            <a href="admin_manage_subjects.php">Manage Subjects</a>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </div>

    <div class="content">
        <h2>Manage Subjects</h2>

        <!-- Add Subject Form -->
        <h3>Add New Subject</h3>
        <form method="POST" enctype="multipart/form-data">
            <label for="subject_name">Subject Name:</label>
            <input type="text" name="subject_name" required>

            <label for="description">Description:</label>
            <textarea name="description" required></textarea>

            <label for="subject_file">Upload File:</label>
            <input type="file" name="subject_file" accept=".pdf, .docx, .pptx">

            <button type="submit" name="add_subject">Add Subject</button>
        </form>

        <!-- List of Existing Subjects -->
        <h3>Existing Subjects</h3>
        <table>
            <thead>
                <tr>
                    <th>Subject Name</th>
                    <th>Description</th>
                    <th>File</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($subjects_result)): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['subject_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['description']); ?></td>
                        <td>
                            <?php if ($row['file_path']): ?>
                                <a href="<?php echo $row['file_path']; ?>" target="_blank">View File</a>
                            <?php else: ?>
                                No file uploaded
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="edit_subject.php?id=<?php echo $row['id']; ?>">Edit</a> |
                            <a href="delete_subject.php?id=<?php echo $row['id']; ?>">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>

<?php
// Close the database connection
mysqli_close($db);
?>
