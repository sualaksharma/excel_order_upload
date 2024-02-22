<?php
// Start or resume a session
session_start();

// Check if the user is not authenticated, redirect to login page
if (!isset($_SESSION['user_authenticated']) || $_SESSION['user_authenticated'] !== true) {
    header('Location: login.php');
    exit();
}

// Database credentials
$dbHost = '184.168.97.210';
$dbUsername = 'wk8divcqwwyu';
$dbPassword = 'Sualaksharma@291100';
$dbName = 'i7715383_wp2';

// Create a database connection
$mysqli = new mysqli($dbHost, $dbUsername, $dbPassword, $dbName);

// Check the connection
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Check if the user submitted the date range
if (isset($_POST['start_date']) && isset($_POST['end_date'])) {
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];

    // Query to retrieve daily order reports within the specified date range
    $query = "SELECT DATE(date) AS order_date, COUNT(*) AS order_count, SUM(amount) AS total_amount 
              FROM tracking 
              WHERE DATE(date) BETWEEN '$start_date' AND '$end_date'
              GROUP BY order_date";

    $result = $mysqli->query($query);

    if ($result) {
        echo "<div class='result-container'>";
        
        // Display daily order reports
        echo "<table>";
        echo "<tr><th>Date</th><th>Order Count</th><th>Total Amount</th></tr>";

        $totalOrderCount = 0;
        $totalAmount = 0;
        
        // Arrays to store chart data
        $dates = [];
        $orderCounts = [];

        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['order_date'] . "</td>";
            echo "<td>" . $row['order_count'] . "</td>";
            echo "<td>Rs. " . number_format($row['total_amount'], 2) . "</td>";
            echo "</tr>";

            $totalOrderCount += $row['order_count'];
            $totalAmount += $row['total_amount'];

            // Populate chart data arrays
            $dates[] = $row['order_date'];
            $orderCounts[] = (int)$row['order_count'];
        }

        echo "</table>";

        // Display total order count and total sum of orders
        echo "<p>Total Order Count: " . $totalOrderCount . "</p>";
        echo "<p>Total Sum of Orders: Rs. " . number_format($totalAmount, 2) . "</p>";

        echo "</div>";
    } else {
        echo "Error: " . $mysqli->error;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <style>
        body {
            font-family: Arial, sans-serif;
        }

        table {
            width: 80%;
            margin: 20px auto;
            border-collapse: collapse;
        }

        th, td {
            border: 1px solid #dddddd;
            text-align: left;
            padding: 8px;
        }

        th {
            background-color: #f2f2f2;
        }

        h1 {
            text-align: center;
        }

        .result-container {
            text-align: center;
        }

        /* Style the chart container */
        .chart-container {
            width: 80%;
            margin: 20px auto;
            text-align: center;
        }

        /* Style the chart canvas */
        canvas {
            max-width: 600px; /* Adjust the maximum width as needed */
        }
    </style>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.7.0/chart.min.js"></script>
</head>
<body>
    <h1>Daily Order Reports</h1>

    <!-- Date range input form -->
    <div class="result-container">
        <form method="POST" action="">
            <label for="start_date">Start Date:</label>
            <input type="date" id="start_date" name="start_date" required>
            
            <label for="end_date">End Date:</label>
            <input type="date" id="end_date" name="end_date" required>

            <input type="submit" value="Submit">
        </form>
    </div>

    <!-- Display bar chart -->
    <div class="chart-container">
        <canvas id="orderChart" height="150"></canvas>
    </div>

    <script>
        // JavaScript to create the bar chart
        var ctx = document.getElementById('orderChart').getContext('2d');
        var orderChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($dates); ?>,
                datasets: [{
                    label: 'Order Count',
                    data: <?php echo json_encode($orderCounts); ?>,
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
</body>
</html>
