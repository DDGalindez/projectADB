<?php 
include('server.php'); 

// Check if registration is successful and redirect
if (isset($_POST['reg_user'])) {
    // Gather form data
    $first_name = mysqli_real_escape_string($db, $_POST['first_name']);
    $last_name = mysqli_real_escape_string($db, $_POST['last_name']);
    $username = mysqli_real_escape_string($db, $_POST['username']);
    $email = mysqli_real_escape_string($db, $_POST['email']);
    $password_1 = mysqli_real_escape_string($db, $_POST['password_1']);
    $password_2 = mysqli_real_escape_string($db, $_POST['password_2']);
    $role = mysqli_real_escape_string($db, $_POST['role']);

    // Password validation
    if ($password_1 !== $password_2) {
        echo "Passwords do not match!";
        exit();
    }

    // Hash the password
    $password = password_hash($password_1, PASSWORD_DEFAULT);

    // Check if role is 'student' or 'admin'
    if ($role === 'student') {
        // Insert student into pending_enrollments table
        $query = "INSERT INTO pending_enrollments (first_name, last_name, username, email, password, status) 
                  VALUES ('$first_name', '$last_name', '$username', '$email', '$password', 'pending')";
        
        if (mysqli_query($db, $query)) {
            // Redirect to the pending enrollments page after successful registration
            header('Location: admin_pending_enrollments.php');
            exit();
        } else {
            echo "Error: " . mysqli_error($db);
        }
    } else if ($role === 'admin') {
        // Insert admin into users table (or whatever table you use for admin)
        $query = "INSERT INTO users (first_name, last_name, username, email, password, role) 
                  VALUES ('$first_name', '$last_name', '$username', '$email', '$password', 'admin')";
        
        if (mysqli_query($db, $query)) {
            // Redirect to the login page after successful registration
            header('Location: login.php');
            exit();
        } else {
            echo "Error: " . mysqli_error($db);
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Registration system PHP and MySQL</title>
  <link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>
  <div class="header">
    <h2>Register</h2>
  </div>
  
  <form method="post" action="register.php">
    <?php include('errors.php'); ?>
    
    <!-- First Name Input -->
    <div class="input-group">
      <label>First Name</label>
      <input type="text" name="first_name" value="<?php echo isset($first_name) ? $first_name : ''; ?>" required>
    </div>
    
    <!-- Last Name Input -->
    <div class="input-group">
      <label>Last Name</label>
      <input type="text" name="last_name" value="<?php echo isset($last_name) ? $last_name : ''; ?>" required>
    </div>
    
    <!-- Username Input -->
    <div class="input-group">
      <label>Username</label>
      <input type="text" name="username" value="<?php echo isset($username) ? $username : ''; ?>" required>
    </div>
    
    <!-- Email Input -->
    <div class="input-group">
      <label>Email</label>
      <input type="email" name="email" value="<?php echo isset($email) ? $email : ''; ?>" required>
    </div>
    
    <!-- Password Input -->
    <div class="input-group">
      <label>Password</label>
      <input type="password" name="password_1" required>
    </div>
    
    <!-- Confirm Password Input -->
    <div class="input-group">
      <label>Confirm password</label>
      <input type="password" name="password_2" required>
    </div>
    
    <!-- Role Selection -->
    <div class="input-group">
      <label>Select your role:</label><br>
      <input type="radio" name="role" value="admin" required> Admin<br>
      <input type="radio" name="role" value="student" required> Student
    </div>
    
    <!-- Submit Button -->
    <div class="input-group">
      <button type="submit" class="btn" name="reg_user">Register</button>
    </div>
    
    <p>
      Already a member? <a href="login.php">Sign in</a>
    </p>
  </form>
</body>
</html>
