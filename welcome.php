<?php
// Start the session
session_start();
include 'includes/db.php';

// Function to get the user's role from the database
function getUserRole($conn, $user_id)
{
    $sql = "SELECT role FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['role'];
    }

    return null; // Return null if user not found
}

// Check if the user is logged in, otherwise redirect to the login page
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Set the role dynamically
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $user_role = getUserRole($conn, $user_id);

    // Redirect to appropriate dashboard
    if ($user_role === 'admin') {
        $_SESSION['user_id'] = $user_id;
        $dashboard_link = "admin_panel.php";
    } elseif ($user_role === 'customer') {
        $_SESSION['user_id'] = $user_id;
        $dashboard_link = "customer_dashboard.php";
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome Page</title>
    <link rel="stylesheet" href="css/welcome_style.css">
</head>

<body>

    <header>
        <nav>
            <a href="welcome.php">Home</a>
            <a href="<?php echo $dashboard_link; ?>">Dashboard</a>
        </nav>
    </header>

    <div class="container">
        <h1>Welcome to </h1>
    </div>
    <div class="Logo-img">
        <img src="css/Logo.png" alt="Logo Of RentalsBook">
    </div>
    <div class="steps-img">
        <img src="css/steps.png" alt="Steps to rent a car">
    </div>
    <div class="button-container">
        <form action="booking_form.php" method="POST">
            <button type="submit" class="start-booking-button">Click here to start booking</button>
        </form>
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