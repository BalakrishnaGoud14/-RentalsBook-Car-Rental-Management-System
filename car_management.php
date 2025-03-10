<?php
session_start();
include 'includes/db.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Handle Add Car
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['add_car'])) {
    $car_model = $_POST['car_model'];
    $car_type = $_POST['car_type'];
    $city = strtolower(trim($_POST['city']));
    $rental_rate = $_POST['rental_rate'];
    $last_serviced = $_POST['last_serviced'];
    $availability = isset($_POST['availability']) ? 1 : 0;

    // Find the location_id based on the city
    $stmt = $conn->prepare("SELECT location_id FROM Available_Locations WHERE LOWER(city) = ?");
    $stmt->bind_param("s", $city);
    $stmt->execute();
    $stmt->bind_result($location_id);
    $stmt->fetch();
    $stmt->close();

    if ($location_id) {
        // Insert car into the database
        $stmt = $conn->prepare("INSERT INTO Cars (car_model, car_type, location_id, rental_rate, availability, last_serviced) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssidis", $car_model, $car_type, $location_id, $rental_rate, $availability, $last_serviced);

        if ($stmt->execute()) {
            $success_message = "Car added successfully.";
        } else {
            $error_message = "Failed to add car.";
        }
        $stmt->close();
    } else {
        $error_message = "City not found in the database. Please add the city first.";
    }
}

// Handle Delete Car
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['delete_car'])) {
    $car_id = $_POST['car_id'];

    $stmt = $conn->prepare("DELETE FROM Cars WHERE car_id = ?");
    $stmt->bind_param("i", $car_id);

    if ($stmt->execute()) {
        $success_message = "Car deleted successfully.";
    } else {
        $error_message = "Failed to delete car.";
    }
    $stmt->close();
}

// Variables for filters and sorting
$filters = [];
$sorting = "c.last_serviced DESC";
$limit = 10;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $limit;

// Handle Filters
if ($_SERVER["REQUEST_METHOD"] === "GET") {
    if (!empty($_GET['filter_type']) && $_GET['filter_type'] !== "") {
        $filters[] = "c.car_type = '" . $conn->real_escape_string($_GET['filter_type']) . "'";
    }
    if (!empty($_GET['filter_city']) && $_GET['filter_city'] !== "") {
        $filters[] = "l.city = '" . $conn->real_escape_string($_GET['filter_city']) . "'";
    }
    if (isset($_GET['filter_availability'])) {
        $filters[] = "c.availability = " . intval($_GET['filter_availability']);
    }
    if (!empty($_GET['sort_by']) && $_GET['sort_by'] !== "") {
        $sorting = $conn->real_escape_string($_GET['sort_by']);
    }
}


// Construct SQL WHERE clause for filters
$where_clause = count($filters) > 0 ? "WHERE " . implode(" AND ", $filters) : "";

// Fetch filtered and sorted cars
$cars = [];
$total_entries = 0;

// Fetch total count for pagination
$count_query = "
    SELECT COUNT(*) as total
    FROM Cars c
    JOIN Available_Locations l ON c.location_id = l.location_id
    $where_clause
";
$result = $conn->query($count_query);
if ($result) {
    $total_entries = $result->fetch_assoc()['total'];
}

// Fetch cars with applied filters, sorting, and pagination
$query = "
    SELECT c.car_id, c.car_model, c.car_type, l.city, c.rental_rate, c.availability, c.last_serviced
    FROM Cars c
    JOIN Available_Locations l ON c.location_id = l.location_id
    $where_clause
    ORDER BY $sorting
    LIMIT $limit OFFSET $offset ";

$result = $conn->query($query);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $cars[] = $row;
    }
}

// Fetch all distinct car types and cities for filters
$types = ["SUV", "Sedan", "Hatchback", "Convertible", "Coupe", "Minivan", "Electric", "Hybrid", "Truck"]; // Example car types
$cities = [];

$type_result = $conn->query("SELECT DISTINCT car_type FROM Cars");
if ($type_result) {
    while ($row = $type_result->fetch_assoc()) {
        $types[] = $row['car_type'];
    }
}

$city_result = $conn->query("SELECT DISTINCT city FROM Available_Locations");
if ($city_result) {
    while ($row = $city_result->fetch_assoc()) {
        $cities[] = $row['city'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Car Management</title>
    <style>
        <?php include 'css/car_management.css'; ?>
    </style>
    <script>
        function confirmDelete() {
            return confirm("Are you sure you want to delete this car?");
        }
    </script>
</head>

<body>
    <header>
        <img src="css/Logo.png" alt="Logo" class="logo">
        <h1>Admin Car Management</h1>
    </header>
    <nav>
        <a href="welcome.php">Home</a>
        <a href="admin_panel.php">Dashboard</a>
    </nav>
    <div class="container">
        <h2 style='text-align: center;'>Manage Cars</h2>
        <?php if (!empty($success_message)) echo "<p style='color: green;'>$success_message</p>"; ?>
        <?php if (!empty($error_message)) echo "<p style='color: red;'>$error_message</p>"; ?>

        <h3>Add a New Car</h3>
        <form method="POST" class='add_car_container'>
            <div class="input-group">
                <label>Car Model</label><br>
                <input type="text" name="car_model" required>
            </div>
            <div class="input-group">
                <label>Car Type</label><br>
                <select name="car_type" required>
                    <option value="" disabled selected>Select car type</option>
                    <?php foreach ($types as $type): ?>
                        <option value="<?php echo htmlspecialchars($type); ?>"><?php echo htmlspecialchars($type); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="city">Location</label><br>
                <select name="city" class="input-group" required>
                    <option value="" disabled selected>Select location</option>
                    <?php foreach ($cities as $city): ?>
                        <option value="<?php echo htmlspecialchars($city); ?>"><?php echo htmlspecialchars(ucwords($city)); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="input-group">
                <label>Rental Rate</label><br>
                <input type="number" name="rental_rate" required>
            </div>
            <div class="input-group">
                <label>Last Serviced</label><br>
                <input type="date" name="last_serviced" required>
            </div>
            <div class="input-group_check">
                <label>
                    <input type="checkbox" name="availability" checked>
                    Available
                </label>
            </div><br>

            <div class="input-group">
                <button type="submit" name="add_car" class="start-booking-button">Add Car</button>
            </div>

        </form>

        <hr>


        <h3>Filters and Sorting</h3>
        <form method="GET" class="filter-form">
            <div class="multicolumn">
                <select name="filter_type">
                    <option value="">Filter by Type</option>
                    <?php foreach ($types as $type): ?>
                        <option value="<?php echo htmlspecialchars($type); ?>" <?php echo (isset($_GET['filter_type']) && $_GET['filter_type'] === $type) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($type); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <select name="filter_city">
                    <option value="">Filter by City</option>
                    <?php foreach ($cities as $city): ?>
                        <option value="<?php echo htmlspecialchars($city); ?>" <?php echo (isset($_GET['filter_city']) && $_GET['filter_city'] === $city) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($city); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <select name="filter_availability">
                    <option value="">Filter by Availability</option>
                    <option value="1" <?php echo (isset($_GET['filter_availability']) && $_GET['filter_availability'] == 1) ? 'selected' : ''; ?>>Available</option>
                    <option value="0" <?php echo (isset($_GET['filter_availability']) && $_GET['filter_availability'] == 0) ? 'selected' : ''; ?>>Unavailable</option>
                </select>

                <select name="sort_by">
                    <option value="c.last_serviced DESC">Sort by Last Serviced (Newest)</option>
                    <option value="c.last_serviced ASC" <?php echo (isset($_GET['sort_by']) && $_GET['sort_by'] === 'c.last_serviced ASC') ? 'selected' : ''; ?>>Sort by Last Serviced (Oldest)</option>
                </select>
            </div>
            <button type="submit" class="filter-button">Apply Filters</button>
        </form>

        <h3>Cars List</h3>
        <table>
            <thead>
                <tr>
                    <th>Car Model</th>
                    <th>Car Type</th>
                    <th>Location</th>
                    <th>Rental Rate</th>
                    <th>Availability</th>
                    <th>Last Serviced</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody>
                <?php foreach ($cars as $car): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($car['car_model']); ?></td>
                        <td><?php echo htmlspecialchars($car['car_type']); ?></td>
                        <td><?php echo htmlspecialchars(ucwords($car['city'])); ?></td>
                        <td><?php echo htmlspecialchars($car['rental_rate']); ?></td>
                        <td><?php echo $car['availability'] ? 'Available' : 'Unavailable'; ?></td>
                        <td><?php echo htmlspecialchars(date('Y-m-d', strtotime($car['last_serviced']))); ?></td>
                        <td>
                            <form method="POST" class="delete-form" onsubmit="return confirmDelete();">
                                <input type="hidden" name="car_id" value="<?php echo $car['car_id']; ?>">
                                <button type="submit" name="delete_car" class="delete-button">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Pagination -->
        <?php if ($total_entries > $limit): ?>
            <div class="pagination">
                <a href="?page=<?php echo max(1, $page - 1); ?>&<?php echo http_build_query(array_merge($_GET, ['page' => max(1, $page - 1)])); ?>">Prev</a>
                <a href="?page=<?php echo min($page + 1, ceil($total_entries / $limit)); ?>&<?php echo http_build_query(array_merge($_GET, ['page' => min($page + 1, ceil($total_entries / $limit))])); ?>">Next</a>
            </div>
        <?php endif; ?>

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