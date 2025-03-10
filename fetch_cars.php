<?php
session_start();
include 'includes/db.php';

$cars = [];
if (isset($_POST['location'])) {
    $selected_location = $_POST['location'];

    $location_id_query = "SELECT location_id FROM Available_Locations WHERE city = ?";
    $stmt = $conn->prepare($location_id_query);
    $stmt->bind_param('s', $selected_location);
    $stmt->execute();
    $location_result = $stmt->get_result();

    if ($location_row = $location_result->fetch_assoc()) {
        $location_id = $location_row['location_id'];

        $car_query = "SELECT car_model FROM Cars WHERE location_id = ?";
        $stmt = $conn->prepare($car_query);
        $stmt->bind_param('i', $location_id);
        $stmt->execute();
        $car_result = $stmt->get_result();

        while ($row = $car_result->fetch_assoc()) {
            $cars[] = $row['car_model'];
        }
    }
}

// Returning the car models as a JSON response
header('Content-Type: application/json'); // Setting the content type to JSON
echo json_encode(['cars' => $cars]);
