<?php
session_start();
include 'includes/db.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetching customer personal details
$stmt = $conn->prepare("SELECT full_name, email, dob FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($full_name, $email, $dob);
$stmt->fetch();
$stmt->close();

// Fetching recent bookings for the user
$bookings = [];
$stmt = $conn->prepare("
    SELECT b.booking_id, c.car_model, l.city, b.start_date, b.end_date, b.total_price
    FROM car_bookings b
    JOIN Cars c ON b.car_id = c.car_id
    JOIN Available_Locations l ON b.location_id = l.location_id
    WHERE b.user_id = ?
    ORDER BY b.booking_date DESC
    LIMIT 5
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($booking_id, $car_model, $city, $start_date, $end_date, $total_price);
while ($stmt->fetch()) {
    $bookings[] = [
        'booking_id' => $booking_id,
        'car_model' => $car_model,
        'city' => $city,
        'start_date' => $start_date,
        'end_date' => $end_date,
        'total_price' => $total_price,
    ];
}
$stmt->close();

// Handling logout
if (isset($_POST['logout'])) {
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
    <title>Customer Dashboard</title>
    <link rel="stylesheet" href="css/customer_dashboard_style.css">
</head>

<body>

    <header>
        <img src="css/Logo.png" alt="Company Logo" class="logo">
        <h1>Dashboard</h1>
    </header>

    <nav>
        <a href="welcome.php">Home</a>
        <a href="booking_form.php">Book Now</a>
    </nav>

    <h1>Welcome, <?php echo htmlspecialchars($full_name); ?>!</h1>

    <div class="container">
        <!-- Customer Personal Details -->
        <div class="user-info">
            <h2>Your Personal Details</h2>
            <p><strong>Name:</strong> <?php echo htmlspecialchars($full_name); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($email); ?></p>
            <p><strong>Date of Birth:</strong> <?php echo htmlspecialchars($dob); ?></p>
        </div>

        <!-- Recent Bookings -->
        <div class="recent-bookings">
            <h2>Your Recent Bookings</h2>
            <?php if (count($bookings) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Car Model</th>
                            <th>Location</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Total Price (In $)</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach ($bookings as $booking): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($booking['car_model']); ?></td>
                                <td><?php echo htmlspecialchars($booking['city']); ?></td>
                                <td><?php echo htmlspecialchars(date('Y-m-d', strtotime($booking['start_date']))); ?></td>
                                <td><?php echo htmlspecialchars(date('Y-m-d', strtotime($booking['end_date']))); ?></td>
                                <td><?php echo htmlspecialchars($booking['total_price']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>You have no recent bookings.</p>
            <?php endif; ?>
        </div>

        <!-- Logout Button -->
        <form method="POST">
            <button type="submit" name="logout" class="logout-button">Logout</button>
        </form>
    </div>

    <br><br><br><br>

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