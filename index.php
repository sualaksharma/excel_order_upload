<?php
require 'vendor/autoload.php'; // Include the PhpSpreadsheet library

$successMessage = ''; // Initialize an empty success message
$recordsAdded = 0; // Initialize a variable to count the number of records added

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['excel-file'])) {
    $file = $_FILES['excel-file']['tmp_name'];

    try {
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file);
        $worksheet = $spreadsheet->getActiveSheet();
        $rows = $worksheet->toArray();

        // Initialize an array to store AWB statuses
        $awbStatuses = [];

        // Process the AWB numbers from the Excel file (assuming AWB numbers are in the first column)
        foreach ($rows as $row) {
            $awb_number = $row[0];
            // Make an API request for each AWB number to track its status
            $response = trackAwbStatus($awb_number);
            
            if (!empty($response)) {
                // Extract the AWB number, status, and amount from the JSON response
                $awbStatuses[] = [
                    'AWB' => $awb_number,
                    'Status' => $response['Status']['Status'], // Updated status extraction
                    'Amount' => $response['InvoiceAmount'],
                ];
                $recordsAdded++; // Increment the count of records added
            } else {
                // Handle errors or missing data
                $awbStatuses[] = [
                    'AWB' => $awb_number,
                    'Status' => 'Error fetching status',
                    'Amount' => 'N/A',
                ];
            }
        }
        
        // Insert data into the MySQL database and check if insertion was successful
        if (insertDataIntoDatabase($awbStatuses)) {
            $successMessage = "Data inserted successfully!";
        } else {
            $successMessage = "Data insertion failed!";
        }

    } catch (\PhpOffice\PhpSpreadsheet\Reader\Exception $e) {
        // Handle file read error
        $error = "Error reading the uploaded Excel file.";
    }
}

// Function to make Delhivery API call to track AWB status and return JSON response
function trackAwbStatus($awb_number) {
    $api_key = "51be8a7cb26effa3655a21005e025c385266838c"; // Replace with your Delhivery API key
    $url = "https://track.delhivery.com/api/v1/packages/json/?waybill=$awb_number";
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Token $api_key",
    ]);

    $response = curl_exec($ch);

    if ($response === false) {
        return null; // Return null if there's an error fetching status
    } else {
        // Parse the JSON response
        $data = json_decode($response, true);

        // Check if the JSON decoding was successful and if "ShipmentData" exists
        if (json_last_error() === JSON_ERROR_NONE && isset($data['ShipmentData'])) {
            return $data['ShipmentData'][0]['Shipment']; // Return the relevant part of the JSON response
        } else {
            return null; // Return null if there's an error parsing JSON or missing data
        }
    }
}

// Function to insert data into the MySQL database and return true if insertion is successful
function insertDataIntoDatabase($awbStatuses) {
    $dbHost = '184.168.97.210';
    $dbUsername = 'wk8divcqwwyu';
    $dbPassword = 'Sualaksharma@291100';
    $dbName = 'i7715383_wp2';

    // Create a new MySQLi connection
    $mysqli = new mysqli($dbHost, $dbUsername, $dbPassword, $dbName);

    // Check connection
    if ($mysqli->connect_error) {
        die("Database connection failed: " . $mysqli->connect_error);
    }

    date_default_timezone_set('IST');

    // Prepare and execute the SQL statement inside your loop
    foreach ($awbStatuses as $awbStatus) {
        $awb = $awbStatus['AWB'];
        $status = $awbStatus['Status'];
        $amount = $awbStatus['Amount'];
        
        // Prepare the SQL statement with placeholders
        $sql = "INSERT INTO tracking (date, awb, status, amount) VALUES (NOW(), ?, ?, ?)";
        
        // Prepare the SQL statement
        $stmt = $mysqli->prepare($sql);
        
        // Bind parameters and execute
        $stmt->bind_param("sss", $awb, $status, $amount);
        
        if ($stmt->execute()) {
            // Data inserted successfully
        } else {
            echo "Error inserting data: " . $stmt->error;
        }
        
        // Close the statement
        $stmt->close();
    }

    // Close the database connection
    $mysqli->close();

    return $recordsAdded; // Return the count of records added
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AWB Status Tracking</title>
    <!-- Include CSS frameworks or custom styles here -->
    <style>
        table {
            border-collapse: collapse;
            width: 100%;
        }

        table, th, td {
            border: 1px solid black;
        }

        th, td {
            padding: 8px;
            text-align: left;
        }
    </style>
</head>
<body>
    <!-- Your UI elements go here -->
    <center><h1>DBRL Shipment Status Tracking</h1></center>

    <!-- Display any errors if encountered -->
    <?php if (isset($error)): ?>
        <p>Error: <?php echo $error; ?></p>
    <?php endif; ?>

    <!-- Display the AWB statuses if available -->
    <?php if (isset($awbStatuses) && !empty($awbStatuses)): ?>
        
        <table>
            <thead>
                <tr>
                    <th>AWB Number</th>
                    <th>Status</th>
                    <th>Amount</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($awbStatuses as $awbStatus): ?>
                    <tr>
                        <td><?php echo $awbStatus['AWB']; ?></td>
                        <td><?php echo $awbStatus['Status']; ?></td>
                        <td><?php echo $awbStatus['Amount']; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <!-- Display the number of records added -->
<?php if ($recordsAdded > 0): ?>
    <p><?php echo $recordsAdded; ?> record(s) added</p>
<?php else: ?>
    <p>No records added</p>
<?php endif; ?>
