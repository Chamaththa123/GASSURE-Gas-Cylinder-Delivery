<?php
// Start the session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database configuration
if (!isset($conn)) {
    require_once '../config/config.php';
}

$user = null;
$user_id = null;

if (isset($_SESSION['user_email'])) {
    $user_email = $_SESSION['user_email'];

    // Prepare and execute the query to fetch user details
    $user_query = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $user_query->bind_param("s", $user_email);
    $user_query->execute();
    $user_result = $user_query->get_result();
    $user = $user_result->fetch_assoc();

    // Get the user ID
    if ($user) {
        $user_id = $user['id']; 
    }
}

// Get the current month's order count for each city
$currentMonth = date('Y-m');
$orderDataQuery = "
    SELECT cities.Name AS city_name, 
           COUNT(orders.id) AS order_count
    FROM cities
    LEFT JOIN orders 
        ON cities.id = orders.delivery_city 
        AND DATE_FORMAT(orders.order_date, '%Y-%m') = ?
    GROUP BY cities.id, cities.Name
    ORDER BY cities.Name ASC
";
$stmt = $conn->prepare($orderDataQuery);
$stmt->bind_param("s", $currentMonth);
$stmt->execute();
$result = $stmt->get_result();

$cities = [];
$orderCounts = [];

while ($row = $result->fetch_assoc()) {
    $cities[] = $row['city_name'];
    $orderCounts[] = (int)$row['order_count'];
}

$stmt->close();
?>

<!DOCTYPE html>
<html>

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src='https://kit.fontawesome.com/a076d05399.js' crossorigin='anonymous'></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/js/all.min.js" crossorigin="anonymous">
    </script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-zoom@1.2.1"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.5.0/Chart.min.js"></script>
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="../css/sidebar.css">
    <link rel="stylesheet" href="../css/inventory.css">
</head>

<body>

    <div class="sidebar">
        <div class="logo-container">
            <img src="../images/logo.png" class="logo" style="width:100px" alt="Logo">
        </div>
        <br />
        <a class="active" href="./dashboard.php">DashBoard</a>
        <a href="./inventory.php">Inventory</a>
        <a href="./admin-orders.php">Orders</a>
        <a href="./user-admin.php">Customers</a>
        <a href="../index.php">Back</a>
    </div>

    <div style='color: #546178'>
        <div class='header'>
            <div style='font-size:24px;font-weight:600'>DashBoard</div>
            <div><a href='./notification.php'><i class="fa fa-bell"
                        style="font-size:24px;margin-right:20px;color:#546178"></i></a></div>
        </div>
        <div class="content">
            <div class="content-one">

                <canvas id="myChart" style="width:10%"></canvas>

                <script>
                var cities = <?php echo json_encode($cities); ?>;
                var orderCounts = <?php echo json_encode($orderCounts); ?>;
                var barColors = Array(cities.length).fill("blue");

                new Chart("myChart", {
                    type: "bar",
                    data: {
                        labels: cities,
                        datasets: [{
                            backgroundColor: barColors,
                            data: orderCounts
                        }]
                    },
                    options: {
                        legend: {
                            display: false
                        },
                        title: {
                            display: true,
                            text: "Order Count by City for Current Month"
                        },
                        scales: {
                            x: {
                                title: {
                                    display: true,
                                    text: "Cities"
                                },
                                barPercentage: 0.1,
                                categoryPercentage: 0.1
                            },
                            y: {
                                title: {
                                    display: true,
                                    text: "Order Count"
                                },
                                beginAtZero: true
                            }
                        }
                    }
                });
                </script>
            </div>
        </div>
    </div>

</body>

</html>