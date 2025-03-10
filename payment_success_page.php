<?php
session_start();
include 'includes/db.php';

// Check if the user is logged in, otherwise redirect to the login page
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch the most recent booking details for the logged-in user
$query = "SELECT cb.car_id, cb.location_id, cb.start_date, cb.end_date, cb.total_price, c.car_model AS car_name, l.city AS location_name, t.transaction_id
          FROM car_bookings cb
          JOIN cars c ON cb.car_id = c.car_id
          JOIN Available_Locations l ON cb.location_id = l.location_id
          JOIN transactions t on cb.booking_id = t.booking_id
          WHERE cb.user_id = ?
          ORDER BY cb.booking_date DESC LIMIT 1";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$query1 = "SELECT full_name FROM users WHERE id = ?";
$stmt1 = $conn->prepare($query1);
$stmt1->bind_param("i", $user_id);
$stmt1->execute();
$result1 = $stmt1->get_result();

if ($result->num_rows > 0 and $result1->num_rows > 0) {
    $booking = $result->fetch_assoc();
    $booking1 = $result1->fetch_assoc();
    $car_name = $booking['car_name'];
    $location_name = $booking['location_name'];
    $start_date = date('Y-m-d', strtotime($booking['start_date']));
    $end_date = date('Y-m-d', strtotime($booking['end_date']));
    $total_price = $booking['total_price'];
    $transaction_id = $booking['transaction_id'];
    $name = $booking1['full_name'];
} else {
    // If no booking found, handle the error
    $_SESSION['error_message'] = "No booking found to proceed with payment.";
    header("Location: booking_form.php");
    exit();
}


// Set the role dynamically
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $user_role = getUserRole($conn, $user_id);

    // Redirect to appropriate dashboard
    if ($user_role === 'admin') {
        $dashboard_link = "admin_panel.php";
    } elseif ($user_role === 'customer') {
        $dashboard_link = "customer_dashboard.php";
    }
}

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

    return null;
}


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Successful</title>
    <link rel="stylesheet" href="css/payment_success_style.css">
    <link rel="stylesheet" href="css/print.css" media="print">
    <script>
        function printReceipt() {
            window.print();
        }
    </script>
</head>

<body>
    <header>
        <nav>
            <a href="welcome.php">Home</a>
            <img src="css/Logo.png" alt="Company Logo" class="logo">
            <a href="<?php echo $dashboard_link; ?>">Dashboard</a>
        </nav>
    </header>

    <div class="receipt-container-company">
        <img src="css/Logo.png" alt="Company Logo" style="display:block; margin: 0 auto; width: 120px; height: auto;">
        <div class="company-name">RentalsBook</div>
        <!-- Other receipt content -->
    </div>


    <div class="receipt-container">

        <div class="receipt-container-company">
            <img src="css/Logo.png" alt="Company Logo" style="display:block; margin: 0 auto; width: 120px; height: auto;">
            <div class="company-name">RentalsBook</div>
            <!-- Other receipt content -->
        </div>

        <h1>Payment Successful!</h1>

        <p><strong>Transaction ID:</strong> <?php echo $transaction_id; ?></p>

        <h2>Booking Details</h2>
        <div class="details">
            <p><strong>Name:</strong> <?php echo htmlspecialchars($name); ?></p>
            <p><strong>Car Name:</strong> <?php echo htmlspecialchars($car_name); ?></p>
            <p><strong>Location:</strong> <?php echo htmlspecialchars($location_name); ?></p>
            <p><strong>From:</strong> <?php echo htmlspecialchars($start_date); ?></p>
            <p><strong>To:</strong> <?php echo htmlspecialchars($end_date); ?></p>
            <p><strong>Total Car Rent Paid:</strong> $<?php echo number_format($total_price, 2); ?></p>
        </div>
        <button onclick="printReceipt()">Print Receipt</button>

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