<?php
session_start();
include 'includes/db.php';

// Check if the user is logged in, otherwise redirect to the login page
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Function to get the number of available cars
function getAvailableCars($conn)
{
    $sql = "SELECT COUNT(*) AS available FROM Cars WHERE availability = 1";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    return $row['available'];
}

// Function to get the number of rented cars
function getRentedCars($conn)
{
    $sql = "SELECT COUNT(*) AS rented FROM Cars WHERE availability = 0";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    return $row['rented'];
}

// Function to get the number of upcoming bookings
function getUpcomingBookings($conn)
{
    $sql = "SELECT COUNT(*) AS upcoming FROM car_bookings WHERE start_date > NOW()";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    return $row['upcoming'];
}

// Function to fetch recent bookings
function getRecentBookings($conn)
{
    $sql = "
        SELECT 
            cb.booking_id, 
            u.full_name AS customer_name, 
            c.car_model, 
            al.city AS pickup_location
        FROM car_bookings cb
        JOIN users u ON cb.user_id = u.id
        JOIN Cars c ON cb.car_id = c.car_id
        JOIN Available_Locations al ON cb.location_id = al.location_id
        ORDER BY cb.booking_date DESC LIMIT 5";

    return $conn->query($sql);
}

// Fetch data from the database
$available_cars = getAvailableCars($conn);
$rented_cars = getRentedCars($conn);
$upcoming_bookings = getUpcomingBookings($conn);
$recent_bookings = getRecentBookings($conn);


// Handle logout
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit();
}


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - RentalsBook</title>
    <link rel="stylesheet" href="css/admin.css">
</head>

<body>
    <header>
        <img src="css/Logo.png" alt="Company Logo" class="logo">
        <h1>Admin Panel</h1>
    </header>

    <nav>
        <a href="welcome.php">Home</a>
        <a href="car_management.php">Car Management</a>
        <!-- <a href="booking_management.php">Booking Management</a> -->
        <a href="admin_customer_management.php">Customer Management</a>
        <a href="#" onclick="document.getElementById('logout-form').submit();">Logout</a>

    </nav>

    <form id="logout-form" method="POST" style="display: none;">
        <input type="hidden" name="logout" value="1">
    </form>

    <div class="container">
        <h2>Dashboard</h2>
        <br>
        <div class="stats">
            <div class="stat-box">
                <h3>Available Cars</h3>
                <p><?php echo $available_cars; ?></p>
            </div>
            <div class="stat-box">
                <h3>Rented Cars</h3>
                <p><?php echo $rented_cars; ?></p>
            </div>
            <div class="stat-box">
                <h3>Upcoming Bookings</h3>
                <p><?php echo $upcoming_bookings; ?></p>
            </div>
        </div>
        <br>

        <h2>Recent Bookings</h2>
        <table>
            <tr>
                <th>Booking ID</th>
                <th>Customer Name</th>
                <th>Car Model</th>
                <th>Pickup Location</th>
            </tr>
            <?php while ($booking = $recent_bookings->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $booking['booking_id']; ?></td>
                    <td><?php echo $booking['customer_name']; ?></td>
                    <td><?php echo $booking['car_model']; ?></td>
                    <td><?php echo $booking['pickup_location']; ?></td>
                </tr>
            <?php endwhile; ?>
        </table>
    </div><br><br><br><br>

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