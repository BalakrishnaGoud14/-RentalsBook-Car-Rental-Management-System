<?php
require 'vendor/autoload.php';


use Dompdf\Dompdf;
use Dompdf\Options;

// Get the booking details from the POST request
$car_name = $_POST['car_name'];
$location_name = $_POST['location_name'];
$start_date = $_POST['start_date'];
$end_date = $_POST['end_date'];
$total_price = $_POST['total_price'];
$transaction_id = $_POST['transaction_id'];


// Initialize Dompdf
$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isPhpEnabled', true);
$dompdf = new Dompdf($options);

// HTML content for the PDF
$html = '
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; }
        .receipt-container { padding: 20px; }
        h1 { color: green; }
        .receipt-container p { font-size: 16px; }
        .receipt-container .details { font-weight: bold; }
    </style>
</head>
<body>
    <div class="receipt-container">
        <h1>Payment Receipt</h1>
        <p><span class="details">Transaction ID:</span> ' . $transaction_id . '</p>
        <p><span class="details">Car Name:</span> ' . $car_name . '</p>
        <p><span class="details">Location:</span> ' . $location_name . '</p>
        <p><span class="details">From:</span> ' . $start_date . '</p>
        <p><span class="details">To:</span> ' . $end_date . '</p>
        <p><span class="details">Total Rent Paid:</span> $' . number_format($total_price, 2) . '</p>
    </div>
</body>
</html>';

// Load the HTML content
$dompdf->loadHtml($html);

// Set paper size (A4)
$dompdf->setPaper('A4', 'portrait');

// Render the PDF (first pass)
$dompdf->render();

// Output the generated PDF (force download)
$dompdf->stream('receipt_' . $transaction_id . '.pdf', array('Attachment' => 1));
