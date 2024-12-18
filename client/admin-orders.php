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

if ($user_id !== null) {
  $order_query = $conn->prepare(
      "SELECT o.*, i.*, u.*, o.id AS orderId , p.status as payment_Status,o.status AS order_Status
         FROM orders o 
         JOIN item i ON o.item_id = i.id 
         JOIN users u ON o.user_id = u.id 
         JOIN payment p ON o.id = p.order_id 
          ORDER BY o.id DESC" 
  );

  if ($order_query) {
      $order_query->execute();
      $order_result = $order_query->get_result();

      if ($order_result === false) {
          die("Query execution failed: " . $conn->error);
      }
  } else {
      die("Failed to prepare statement: " . $conn->error);
  }
} else {
  die("User ID is null; unable to fetch notifications.");
}


if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['progress_order'])) {
    $order_id = $_POST['id'];
    $stmt = $conn->prepare("UPDATE orders SET status = 'In Progress' WHERE id = ?");
    $stmt->bind_param("i", $order_id);

    if ($stmt->execute()) {
        $_SESSION['message'] = "succes processing!";
    } else {
        $_SESSION['error_message'] = "Failed to processing: " . $stmt->error;
    }

    header('Location: admin-orders.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delivered_order'])) {
    $order_id = $_POST['id'];
    $stmt = $conn->prepare("UPDATE orders SET status = 'Delivered' WHERE id = ?");
    $stmt->bind_param("i", $order_id);

    if ($stmt->execute()) {
        $_SESSION['message'] = "Appointment rejected successfully!";
    } else {
        $_SESSION['error_message'] = "Failed to reject appointment: " . $stmt->error;
    }

    header('Location: admin-orders.php');
    exit;
}

?>

<!DOCTYPE html>
<html>

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src='https://kit.fontawesome.com/a076d05399.js' crossorigin='anonymous'></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/js/all.min.js" crossorigin="anonymous">
    </script>
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="../css/sidebar.css">
    <link rel="stylesheet" href="../css/inventory.css">
    <link rel="stylesheet" href="../css/table.css">
    <style>

    </style>
</head>

<body>

    <div class="sidebar">
        <div class="logo-container">
            <img src="../images/logo.png" class="logo" style="width:100px" alt="Logo">
        </div>
        <br />
        <a href="./inventory.php">Inventory</a>
        <a class="active" href="./admin-orders.php">Orders</a>
        <a href="#contact">Contact</a>
        <a href="#about">About</a>
    </div>

    <div style='color: #546178'>
        <div class='header'>
            <div style='font-size:24px;font-weight:600'>Orders</div>
            <div><a href='./notification.php'><i class="fa fa-bell"
                        style="font-size:24px;margin-right:20px;color:#546178"></i></a></div>
        </div>
        <div class="content">
            <div class="content-one">

                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Order Id</th>
                                <th>Customer</th>
                                <th>Date</th>
                                <th>Item</th>
                                <th>Unit Price</th>
                                <th>Qty</th>
                                <th>Total</th>
                                <th>Delivery Details</th>
                                <th>Payment</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                        if ($order_result->num_rows > 0) {
                            while ($order = $order_result->fetch_assoc()) {
                                echo "<tr>";
                                echo "<td data-label='Order Id'>OR#" . htmlspecialchars($order['orderId']) . "</td>";

                                echo "<td data-label='Customer'>" . htmlspecialchars($order['first_name']) . " " . htmlspecialchars($order['last_name']) . "</td>";

                                echo "<td data-label='Date'>" . date('Y-m-d', strtotime($order['order_date'])) . "</td>";

                                echo "<td data-label='Item'>" . htmlspecialchars($order['description']) . "</td>";
                                echo "<td data-label='Unit Price'>" . "Rs."  . htmlspecialchars($order['price']) . "</td>";
                                echo "<td data-label='Qty'>" . htmlspecialchars($order['quantity']) . "</td>";
                                echo "<td data-label='Total'>" . "Rs." . htmlspecialchars($order['totalAmount']) . "</td>";
                                echo "<td data-label='Delivery'>" . htmlspecialchars($order['delivery_name']) . ", " . htmlspecialchars($order['delivery_address']) .  ", " . htmlspecialchars($order['contact']) . "</td>";
                               
                                echo "<td data-label='Payment'>" . htmlspecialchars($order['payment_Status']) . "</td>";
                                echo "<td data-label='Status'>" . htmlspecialchars($order['order_Status']) . "</td>";
                                echo "<td data-label=''>
                                        <div class='action-buttons'>";
                                
                                if ($order['order_Status'] == 'Pending') {
                                    echo "<form method='POST' action='' style='display:inline;'>
                                            <input type='hidden' name='id' value='" . $order['orderId'] . "'>
                                            <button type='submit' name='progress_order' class='reply-button' style='background-color:#ffc107'>Mark as Progress</button>
                                          </form>";
                                } elseif ($order['order_Status'] == 'In Progress') {
                                    echo "<form method='POST' action='' style='display:inline;'>
                                            <input type='hidden' name='id' value='" . $order['orderId'] . "'>
                                            <button type='submit' name='delivered_order' class='delete-button' style='background-color:#0d6efd'>Mark as Delivered</button>
                                          </form>";
                                } elseif ($order['order_Status'] == 'Delivered') {
                                    echo "<span class='complete-text' style=''><i class='fas fa-truck' style='font-size:20px;color:#28ca00;text-align:center'></i></span>";
                                }
                                
                                echo "</div>
                                      </td>";
                                
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='9'>No order found.</td></tr>";
                        }
                        ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

</body>

</html>