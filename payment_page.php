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
$query = "SELECT cb.car_id, cb.location_id, cb.start_date, cb.end_date, cb.total_price, c.car_model AS car_name, l.city AS location_name, cb.booking_id
          FROM car_bookings cb
          JOIN cars c ON cb.car_id = c.car_id
          JOIN Available_Locations l ON cb.location_id = l.location_id
          WHERE cb.user_id = ? AND cb.payment_flag = 'N'
          ORDER BY cb.booking_date DESC LIMIT 1";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $booking = $result->fetch_assoc();
    $car_name = $booking['car_name'];
    $location_name = $booking['location_name'];
    $start_date = date('Y-m-d', strtotime($booking['start_date']));
    $end_date = date('Y-m-d', strtotime($booking['end_date']));
    $total_price = $booking['total_price'];
    $booking_id = $booking['booking_id'];
} else {
    // If no booking found or payment already done, handle the error
    $_SESSION['error_message'] = "No booking found or payment already completed.";
    header("Location: booking_form.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Process payment (simulate successful payment)

    // Step 1: Generate unique transaction ID
    $transaction_id = generateUniqueTransactionID($conn);

    // Step 2: Insert transaction details into transactions table
    $query = "INSERT INTO transactions (transaction_id, user_id, booking_id, transaction_amount, transaction_status)
              VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $transaction_status = 'successful'; // Assuming the payment is successful
    $stmt->bind_param("siiss", $transaction_id, $user_id, $booking_id, $total_price, $transaction_status);
    $stmt->execute();

    // Step 3: Update car_booking table with payment_flag 'Y'
    $update_query = "UPDATE car_bookings SET payment_flag = 'Y' WHERE booking_id = ?";
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->bind_param("i", $booking_id);
    $update_stmt->execute();

    // Step 4: Redirect to payment success page
    header("Location: payment_success_page.php");
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

// Function to generate unique transaction ID
function generateUniqueTransactionID($conn)
{
    do {
        // Get the current timestamp in milliseconds
        $timestamp = microtime(true) * 1000; // Current timestamp in milliseconds
        $random_str = substr(str_shuffle("abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789"), 0, 6); // Random 6 characters
        $transaction_id = 'TXN_' . $timestamp . '_' . $random_str; // Combine timestamp with random string

        // Check if the transaction ID already exists in the database
        $query = "SELECT COUNT(*) FROM transactions WHERE transaction_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $transaction_id);
        $stmt->execute();

        // Get the result and fetch the count
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $count = $row['count']; // Fetch the count directly from the result

    } while ($count > 0); // Repeat if the ID already exists

    return $transaction_id;
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment</title>
    <link rel="stylesheet" href="css/payment_page_style.css">
</head>

<body>
    <header>
        <nav>
            <a href="welcome.php">Home</a>
            <img src="css/Logo.png" alt="Company Logo" class="logo">
            <a href="<?php echo $dashboard_link; ?>">Dashboard</a>
        </nav>
    </header>

    <div class="container">
        <div class="left-section">
            <h1>Booking Details</h1>
            <p><strong>Car Name:</strong> <?php echo htmlspecialchars($car_name); ?></p>
            <p><strong>Pickup Location:</strong> <?php echo htmlspecialchars($location_name); ?></p>
            <p><strong>From:</strong> <?php echo htmlspecialchars($start_date); ?></p>
            <p><strong>To:</strong> <?php echo htmlspecialchars($end_date); ?></p>
            <p><strong>Total Rent to Pay:</strong> $<?php echo number_format($total_price, 2); ?></p>
        </div>

        <div class="right-section">
            <div class="payment-box">
                <h1>Enter Your Card Details</h1>
                <form action="payment_page.php" method="POST">
                    <label for="card_number">Card Number</label>
                    <input type="text" id="card_number" name="card_number" placeholder="XXXX-XXXX-XXXX-XXXX" required pattern="\d{4}-\d{4}-\d{4}-\d{4}" title="Card number must be in XXXX-XXXX-XXXX-XXXX format">

                    <label for="card_name">Cardholder Name</label>
                    <input type="text" id="card_name" name="card_name" required pattern="[A-Za-z\s]+" title="Name should contain only letters and spaces">

                    <label for="expiry_date">Expiry Date</label>
                    <input type="text" id="expiry_date" name="expiry_date" placeholder="MM/YY" required pattern="(0[1-9]|1[0-2])\/[0-9]{2}" title="Expiry date must be in MM/YY format">

                    <label for="cvc">CVC</label>
                    <input type="text" id="cvc" name="cvc" placeholder="XXX" required pattern="\d{3}" title="CVC must be a 3-digit number">

                    <button type="submit" class="pay-button">Pay Now</button>
                </form>
            </div>
        </div>
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