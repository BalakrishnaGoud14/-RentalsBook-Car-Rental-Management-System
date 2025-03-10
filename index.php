<?php
session_start();
include 'includes/db.php';

$error_message = "";
$success_message = "";

// Checking if the form is submitted
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    if (isset($_POST['email']) && isset($_POST['password'])) {
        $email = $_POST['email'];
        $password = $_POST['password'];

        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        // Checking if user exists
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            // Verifying the password
            // if (password_verify($password, $user['password'])) {
            if ($password === $user['password']) {
                $_SESSION['user_id'] = $user['id']; // Settting session for the user
                $success_message = "Login successful!";
                header("Location: welcome.php"); // Redirecting to a welcome page after successful login
            } else {
                $error_message = "Invalid password!";
            }
        } else {
            $error_message = "User does not exist!";
        }

        $stmt->close();
    } else {
        $error_message = "User does not exist!";
    }
}


?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>
    <link rel="stylesheet" href="css/styles.css">
</head>

<body>
    <div>
        <header class="page-header">
            <h1>Welcome to RentalsBook! &nbsp; </h1>
        </header>
    </div>
    <div class="login-container">
        <h2>Login</h2>
        <?php if (!empty($error_message)): ?>
            <div class="error-message" style="color: red;"><?php echo $error_message; ?></div>
        <?php endif; ?>
        <form action="index.php" method="POST">
            <div class="input-group">
                <label>Username</label>
                <input type="text" name="email" required>
            </div>
            <div class="input-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>
            <div class="options">
                <label>

                </label>
                <a href="#" class="forgot">Forgot Password?</a>
            </div>
            <button type="submit">Login</button>
            <?php if (!empty($success_message)): ?>
                <div class="success-message" style="color: green;"><?php echo $success_message; ?></div>
            <?php endif; ?>
            <div>
                <br><a href="signup.php" class="signup">Register / Signup</a>
            </div>
        </form>
    </div>
</body>

</html>