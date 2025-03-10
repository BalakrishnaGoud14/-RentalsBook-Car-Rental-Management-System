<?php

include 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['car'])) {
        $selected_car = $_POST['car'];

        // Query to fetch rent_per_day for the selected car
        $query = "SELECT rental_rate FROM Cars WHERE car_model = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('s', $selected_car);
        $stmt->execute();
        $stmt->bind_result($rent);
        $stmt->fetch();
        $stmt->close();

        // Return the rent as a JSON response
        echo json_encode(['rent' => $rent]);
    }
}
