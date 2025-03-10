<?php

session_start();

include 'includes/db.php';

// Check if the user is logged in, otherwise redirect to the login page
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
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

// Setting the role dynamically
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $user_role = getUserRole($conn, $user_id);

    // Redirecting to appropriate dashboard
    if ($user_role === 'admin') {
        $dashboard_link = "admin_panel.php";
    } elseif ($user_role === 'customer') {
        $dashboard_link = "customer_dashboard.php";
    }
}

$error_message = "";
$success_message = "";
$cars = [];

// Fetch available locations
$location_query = "SELECT city FROM Available_Locations";
$location_result = $conn->query($location_query);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $from_date = $_POST['from_date'] ?? '';
    $to_date = $_POST['to_date'] ?? '';
    $selected_location = $_POST['location'] ?? '';
    $selected_car = $_POST['car'] ?? '';
    $today = date("Y-m-d");

    if (empty($from_date) || empty($to_date) || empty($selected_location) || empty($selected_car)) {
        $error_message = "All fields are mandatory.";
    } else {
        // Fetch location ID
        $location_id_query = "SELECT location_id FROM Available_Locations WHERE city = ?";
        $stmt = $conn->prepare($location_id_query);
        $stmt->bind_param('s', $selected_location);
        $stmt->execute();
        $location_result = $stmt->get_result();
        $location_id_row = $location_result->fetch_assoc();
        $location_id = $location_id_row['location_id'] ?? null;

        // Fetch car ID
        $car_id_query = "SELECT car_id FROM Cars WHERE car_model = ?";
        $stmt = $conn->prepare($car_id_query);
        $stmt->bind_param('s', $selected_car);
        $stmt->execute();
        $car_result = $stmt->get_result();
        $car_id_row = $car_result->fetch_assoc();
        $car_id = $car_id_row['car_id'] ?? null;

        // Validate dates
        if ($from_date < $today || $to_date < $today) {
            $error_message = "Dates cannot be in the past.";
        } elseif ($from_date > $to_date) {
            $error_message = "The 'From Date' must be before or the same as the 'To Date'.";
        } elseif (!$location_id || !$car_id) {
            $error_message = "Invalid location or car selected.";
        } else {

            $rent_query = "SELECT rental_rate FROM Cars WHERE car_id = ?";
            $stmt = $conn->prepare($rent_query);
            $stmt->bind_param('i', $car_id);
            $stmt->execute();
            $rent_result = $stmt->get_result();
            $rent_row = $rent_result->fetch_assoc();
            $rent_per_day = $rent_row['rental_rate'];

            // Calculate number of days
            $start_date = new DateTime($from_date);
            $end_date = new DateTime($to_date);
            $days = $start_date->diff($end_date)->days + 1;

            $total_price = $days * $rent_per_day;

            $payment_flag = 'N';
            // Insert booking details into the database
            $query = "INSERT INTO car_bookings (user_id, car_id, location_id, booking_date, start_date, end_date, total_price, payment_flag) 
                      VALUES (?, ?, ?, NOW(), ?, ?, ?, ?)";
            $update_query = "UPDATE Cars SET availability = 0 WHERE car_id = ?";

            $stmt = $conn->prepare($query);
            $stmt->bind_param("iiissds", $user_id, $car_id, $location_id, $from_date, $to_date, $total_price, $payment_flag);

            $stmt2 = $conn->prepare($update_query);
            $stmt2->bind_param("i", $car_id);

            if ($stmt->execute() && $stmt2->execute()) {
                $_SESSION['booking_success'] = "Booking successful!";
                header("Location: payment_page.php");
                exit();
            } else {
                $error_message = "Error occurred during booking. Please try again.";
            }
        }
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
        function fetchCars() {
            const locationSelect = document.getElementById("location");
            const selectedLocation = locationSelect.value;
            const carSelect = document.getElementById("car");

            carSelect.innerHTML = "<option value='' disabled selected>Select car</option>";

            if (selectedLocation) {
                fetch("fetch_cars.php", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/x-www-form-urlencoded"
                        },
                        body: "location=" + encodeURIComponent(selectedLocation)
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.cars.length > 0) {
                            data.cars.forEach(car => {
                                const option = document.createElement("option");
                                option.value = car;
                                option.textContent = car;
                                carSelect.appendChild(option);
                            });
                        } else {
                            const option = document.createElement("option");
                            option.value = '';
                            option.textContent = "No cars available for this location";
                            carSelect.appendChild(option);
                        }
                    })
                    .catch(error => console.error('Error fetching cars:', error));
            }
        }

        function fetchRent() {
            const carSelect = document.getElementById("car");
            const selectedCar = carSelect.value;
            const rentInput = document.querySelector('input[name="rent"]');

            rentInput.value = '';

            if (selectedCar) {
                fetch("fetch_rent.php", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/x-www-form-urlencoded"
                        },
                        body: "car=" + encodeURIComponent(selectedCar)
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.rent) {
                            rentInput.value = data.rent;
                        } else {
                            rentInput.value = '';
                        }
                    })
                    .catch(error => console.error('Error fetching rent:', error));
            }
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

    <div class="container">
        <form action="booking_form.php" method="POST">
            <h1>Book a Car</h1>

            <?php if ($error_message) : ?>
                <p class="error"><?php echo $error_message; ?></p>
            <?php endif; ?>
            <br>
            <div class="form-group">
                <label for="from_date">From Date:</label>
                <input type="date" id="from_date" name="from_date" required>
            </div>

            <div class="form-group">
                <label for="to_date">To Date:</label>
                <input type="date" id="to_date" name="to_date" required>
            </div>

            <div class="form-group">
                <label for="location">Location:</label>
                <select id="location" name="location" required onchange="fetchCars()">
                    <option value="" disabled selected>Select location</option>
                    <?php
                    if ($location_result->num_rows > 0) {
                        while ($row = $location_result->fetch_assoc()) {
                            echo "<option value='" . $row['city'] . "'>" . $row['city'] . "</option>";
                        }
                    } else {
                        echo "<option value=''>No locations available</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="form-group">
                <label for="car">Available Cars:</label>
                <select id="car" name="car" required onchange="fetchRent()">
                    <option value="" disabled selected>Select car</option>
                </select>
            </div>

            <div class="form-group">
                <label>Rent Per Day ($):</label>
                <input type="text" name="rent" required readonly>
            </div>

            <div class="form-group">
                <button type="submit" class="btn">Pay Now</button>
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