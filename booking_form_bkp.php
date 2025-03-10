<?php
// Start the session
session_start();
include 'includes/db.php';


$error_message = "";
$success_message = "";
$car_prices = [
    "Tesla Model 3" => 100, // Price per day
    "BMW Series 5" => 150,
    "Audi A6" => 120,
];

$selected_car_price = 0; // Initialize price variable

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['from_date']) && isset($_POST['to_date']) && isset($_POST['location']) && isset($_POST['car'])) {

        // Retrieve the form data
        $from_date = $_POST['from_date'];
        $to_date = $_POST['to_date'];
        $location = $_POST['location'];
        $car = $_POST['car'];

        // Get today's date for validation
        $today = date("d-m-Y");
        $from_date = DateTime::createFromFormat('d-m-Y', $from_date);
        $to_date = DateTime::createFromFormat('d-m-Y', $to_date);
        echo  $today;
        // Basic validation
        if (!empty($from_date) && !empty($to_date) && !empty($location) && !empty($car)) {
            // Check if dates are valid
            if ($from_date < $today || $to_date < $today) {
                $error_message = "Dates cannot be in the past.";
            } elseif ($from_date > $to_date) {
                $error_message = "The 'From Date' must be before or the same as the 'To Date'.";
            } else {
                // Prepare the SQL query
                $query = "INSERT INTO bookings (from_date, to_date, location, car) VALUES (:from_date, :to_date, :location, :car)";
                $stmt = $pdo->prepare($query);
                $stmt->execute([
                    'from_date' => $from_date,
                    'to_date' => $to_date,
                    'location' => $location,
                    'car' => $car
                ]);

                $success_message = "Booking successful!";
            }
        } else {
            $error_message = "Please fill out all fields.";
        }
    } else {
        $error_message = "Invalid data submitted.";
    }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Car Rental Booking</title>
    <link rel="stylesheet" href="css/booking_page.css">
    <script>
        function updatePrice() {
            const carSelect = document.getElementById("car");
            const priceInput = document.getElementById("price");
            const selectedCar = carSelect.value;

            // Prices per day for each car
            const prices = {
                "Tesla Model 3": 100,
                "BMW Series 5": 150,
                "Audi A6": 120
            };

            // Update price input based on selected car
            priceInput.value = prices[selectedCar] || 0; // Default to 0 if no car is selected
        }
    </script>
</head>

<body>
    <header>
        <nav>
            <a href="welcome.php">Home</a>
            <img src="css/Logo.png" alt="Company Logo" class="logo">
            <a href="dashboard.php">Dashboard</a>
        </nav>
    </header>

    <div class="container">
        <form action="booking_page.php" method="POST">
            <h1>Book a Car</h1>

            <?php if ($success_message) : ?>
                <p class="success"><?php echo $success_message; ?></p>
            <?php endif; ?>

            <?php if ($error_message) : ?>
                <p class="error"><?php echo $error_message; ?></p>
            <?php endif; ?>

            <div class="form-group">
                <label for="from_date">From Date:</label>
                <input type="date" id="from_date" name="from_date" min="<?php echo $today; ?>" required>
            </div>

            <div class="form-group">
                <label for="to_date">To Date:</label>
                <input type="date" id="to_date" name="to_date" min="<?php echo $today; ?>" required>
            </div>

            <div class="form-group">
                <label for="location">Location:</label>
                <select id="location" name="location" required>
                    <option value="" disabled selected>Select location</option>
                    <option value="New York">New York</option>
                    <option value="Los Angeles">Los Angeles</option>
                    <option value="Chicago">Chicago</option>
                </select>
            </div>

            <div class="form-group">
                <label for="car">Available Cars:</label>
                <select id="car" name="car" required onchange="updatePrice()">
                    <option value="" disabled selected>Select car</option>
                    <option value="Tesla Model 3">Tesla Model 3</option>
                    <option value="BMW Series 5">BMW Series 5</option>
                    <option value="Audi A6">Audi A6</option>
                </select>
            </div>

            <div class="form-group">
                <label for="price">Price per Day:</label>
                <input type="text" id="price" name="price" value="0" readonly>
            </div>

            <div class="form-group">
                <button type="submit" class="btn">Book Now</button>
            </div>
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