<?php
session_start();
include 'includes/db.php';

// Check if the user is logged in and has admin privileges
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$error_message = '';
$success_message = '';

// Handling Add User
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['add_user'])) {
    $full_name = $_POST['full_name'];
    $email = trim($_POST['email']);
    $dob = $_POST['dob'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = $_POST['role'];

    // Checking if the passwords match
    if ($password === $confirm_password) {

        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        if ($stmt) {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();

            // If email already exists, showing the error message
            if ($stmt->num_rows > 0) {
                $error_message = "This email is already registered!";
            } else {
                // Insert the new user if the email does not exist
                $stmt->close();
                $stmt = $conn->prepare("INSERT INTO users (full_name, email, dob, password, role) VALUES (?, ?, ?, ?, ?)");
                if ($stmt) {
                    $stmt->bind_param("sssss", $full_name, $email, $dob, $password, $role);
                    $stmt->execute();
                    $stmt->close();
                    $success_message = "User added successfully!";
                } else {
                    $error_message = "Error preparing insert statement.";
                }
            }
        } else {
            $error_message = "Error preparing email check statement.";
        }
    } else {
        $error_message = "Passwords do not match!";
    }
}

// Handle Delete User
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['delete_user'])) {
    $user_id = $_POST['user_id'];

    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->close();
        $success_message = "User deleted successfully!";
    } else {
        $error_message = "Error preparing delete statement.";
    }
}

// Handle Update User Role
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['update_role'])) {
    $user_id = $_POST['user_id'];
    $role = $_POST['role'];

    $stmt = $conn->prepare("UPDATE users SET role = ? WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("si", $role, $user_id);
        $stmt->execute();
        $stmt->close();
        $success_message = "User role updated successfully!";
    } else {
        $error_message = "Error preparing update statement.";
    }
}

// Handle Change Password
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['change_password'])) {
    $user_id = $_POST['user_id'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if ($new_password === $confirm_password) {
        // Directly save the password without hashing
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param("si", $new_password, $user_id);
            $stmt->execute();
            $stmt->close();
            $success_message = "Password changed successfully!";
        } else {
            $error_message = "Error preparing password update statement.";
        }
    } else {
        $error_message = "Passwords do not match!";
    }
}

// Handle Search User by Email
$user_data = null;
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['search_user'])) {
    $email = trim($_POST['email']); // Trim the email to remove extra spaces

    // Fetch user based on email
    $stmt = $conn->prepare("SELECT id, full_name, email, dob, role FROM users WHERE email = ?");
    if ($stmt) {
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user_data = $result->fetch_assoc();
        $stmt->close();
    } else {
        $error_message = "Error preparing search statement.";
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Management</title>
    <link rel="stylesheet" href="css/customer_management.css">

    <script>
        // Confirm delete action
        function confirmDelete() {
            return confirm("Are you sure you want to delete this user?");
        }
    </script>
</head>

<body>
    <header>
        <img src="css/Logo.png" alt="Company Logo" class="logo">
        <h1>Customer Management</h1>
    </header>

    <nav>
        <a href="welcome.php">Home</a>
        <a href="admin_panel.php">Dashboard</a>
    </nav>

    <div class="container">
        <h2 style='text-align: center;'>Manage Users</h2>

        <?php if (isset($success_message)) {
            echo "<p class='message success-message'>$success_message</p>";
        } ?>

        <?php if (isset($error_message)) {
            echo "<p class='message error-message'>$error_message</p>";
        } ?>

        <!-- Add User Form -->
        <h3>Add New User</h3>
        <form method="POST" class='add_user_container'>
            <div class="input-group">
                <label>Full Name</label><br>
                <input type="text" name="full_name" required>
            </div>
            <div class="input-group">
                <label>Date of Birth</label><br>
                <input type="date" name="dob" required>
            </div>
            <div class="input-group">
                <label>Email</label><br>
                <input type="email" name="email" required>
            </div>
            <div class="input-group">
                <label>Password</label><br>
                <input type="password" name="password" required>
            </div>
            <div class="input-group">
                <label>Confirm Password</label><br>
                <input type="password" name="confirm_password" required>
            </div>
            <div>
                <label for="role">Role</label><br>
                <select name="role" class="input-group">
                    <option value="" disabled selected>Select role</option>
                    <option value="customer">Customer</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
            <br>
            <button type="submit" name="add_user">Add User</button>
        </form>

        <hr>

        <!-- Search User by Email -->
        <h3>Search User by Email for Other Operations</h3>
        <form method="POST">
            <input type="email" name="email" placeholder="Enter User Email" required>
            <button type="submit" name="search_user">Search</button>
        </form>

        <?php if ($user_data): ?>
            <h3>User Details</h3>
            <p><strong>Name:</strong> <?php echo $user_data['full_name']; ?></p>
            <p><strong>Email:</strong> <?php echo $user_data['email']; ?></p>
            <p><strong>Date of Birth:</strong> <?php echo $user_data['dob']; ?></p>
            <p><strong>Role:</strong> <?php echo $user_data['role']; ?></p>

            <!-- Update Role Form -->
            <h4>Update Role</h4>
            <form method="POST">
                <input type="hidden" name="user_id" value="<?php echo $user_data['id']; ?>">
                <select name="role">
                    <option value="customer" <?php echo $user_data['role'] == 'customer' ? 'selected' : ''; ?>>Customer</option>
                    <option value="admin" <?php echo $user_data['role'] == 'admin' ? 'selected' : ''; ?>>Admin</option>
                </select>
                <button type="submit" name="update_role">Update Role</button>
            </form>

            <!-- Change Password Form -->
            <h4>Change Password</h4>
            <form method="POST">
                <input type="hidden" name="user_id" value="<?php echo $user_data['id']; ?>">
                <input type="password" name="new_password" placeholder="New Password" required>
                <input type="password" name="confirm_password" placeholder="Confirm Password" required><br>
                <button type="submit" name="change_password">Change Password</button>
            </form>

            <!-- Delete User Form -->
            <h4>Delete User</h4>
            <form method="POST" onsubmit="return confirmDelete();">
                <input type="hidden" name="user_id" value="<?php echo $user_data['id']; ?>">
                <button type="submit" name="delete_user">Delete User</button>
            </form>
        <?php endif; ?>
    </div>

    <footer class="footer">
        <div class="footer-container">
            <div class="footer-section about-us">
                <h2>About Us</h2>
                <p style="text-align:justify">RentalsBook is a platform dedicated to making rentals easy and accessible for everyone. We strive to offer the best service by providing a wide range of rental options and an easy booking experience.</p>
            </div>
            <div class="footer-section help">
                <h2>Help</h2>
                <ul>
                    <li><a href="#">How to Start Booking</a></li>
                    <li><a href="#">FAQs</a></li>
                    <li><a href="#">Contact Support</a></li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2024 RentalsBook. All Rights Reserved.</p>
        </div>
    </footer>

</body>

</html>