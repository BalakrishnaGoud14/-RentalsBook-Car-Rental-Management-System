<?php
include 'includes/db.php';

$error_message = "";
$success_message = "";

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $full_name = $_POST['full_name'];
    $dob = $_POST['dob'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validate if passwords match
    if ($password !== $confirm_password) {
        $error_message = "Passwords do not match!";
    } else {
        // // Hash the password before storing it
        // $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Prepare the SQL query
        $stmt = $conn->prepare("INSERT INTO users (full_name, dob, email, password, role) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $full_name, $dob, $email, $password, 'customer');

        // Execute the query and check if insertion is successful
        if ($stmt->execute()) {
            $success_message = "Signup successful!";
        } else {
            $error_message = "Error: " . $stmt->error;
        }

        // Close the statement
        $stmt->close();
    }
}


?>




<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signup Page</title>
    <link rel="stylesheet" href="css/signup_styles.css">
</head>

<body>
    <div>
        <header class="page-header">
            <h1>Welcome to RentalsBook! &nbsp; </h1>
        </header>
    </div>
    <div class="signup-container">
        <h2>Signup</h2>
        <?php if (!empty($error_message)): ?>
            <div class="error-message" style="color: red;"><?php echo $error_message; ?></div>
        <?php endif; ?>
        <form action="Signup_Demo.php" method="POST">
            <div class="input-group">
                <label>Full Name</label>
                <input type="text" name="full_name" required>
            </div>
            <div class="input-group">
                <label>Date of Birth</label>
                <input type="date" name="dob" required>
            </div>
            <div class="input-group">
                <label>Email</label>
                <input type="email" name="email" required>
            </div>
            <div class="input-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>
            <div class="input-group">
                <label>Confirm Password</label>
                <input type="password" name="confirm_password" required>
            </div>
            <button type="submit">Sign Up</button>
        </form>
        <?php if (!empty($success_message)): ?>
            <div class="success-message" style="color: green;"><?php echo $success_message; ?></div>
        <?php endif; ?>
        <p class="login-redirect">Already have an account? <a href="index.php">Login</a></p>
    </div>
</body>

</html>