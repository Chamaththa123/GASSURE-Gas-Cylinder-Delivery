<?php
// Start the session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database configuration
if (!isset($conn)) {
    require_once '../config/config.php';
}

$user = null; // Initialize to null to avoid undefined variable errors
$user_id = null; // Initialize user_id

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
      "SELECT * FROM notification order by id DESC" // Make sure the SQL query is correct
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

?>
<!DOCTYPE html>
<html>

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src='https://kit.fontawesome.com/a076d05399.js' crossorigin='anonymous'></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="../css/sidebar.css">
    <link rel="stylesheet" href="../css/inventory.css">
    <link rel="stylesheet" href="../css/header.css">
    <style>

    </style>
</head>

<body>
<div class="sidebar">
        <div class="logo-container">
            <img src="../images/logo.png" class="logo" style="width:100px" alt="Logo">
        </div>
        <br />
        <a href="./dashboard.php">DashBoard</a>
        <a href="./inventory.php">Inventory</a>
        <a href="./admin-orders.php">Orders</a>
        <a href="./user-admin.php">Customers</a>
        <a href="../index.php">Back</a>
    </div>

    <div style='color: #546178'>
        <div class='header'>
            <div style='font-size:24px;font-weight:600'>Notifications</div>
            <div><a href='./notification.php'><i class="fa fa-bell"
                        style="font-size:24px;margin-right:20px;color:#546178"></i></a></div>
        </div>
        <div class="content">
            <div class="content-one">

            <?php while ($row = $order_result->fetch_assoc()): ?>
                    <div>

                        <table style='width:100%;padding:15px'>
                            <tr>
                                <td style='width:60%'><span style='font-weight:normal;font-size:15px'><i
                                            class="fa fa-angle-double-right"
                                            style="font-size:18px"></i>&nbsp;&nbsp;&nbsp;&nbsp;<?php echo htmlspecialchars($row['description']); ?></span>
                                </td>
                                <td style='width:40%;text-align:right;font-size:13px'>
                                    <?php echo htmlspecialchars($row['created_at']); ?><br />
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>


                    <?php endwhile; ?>
                    <?php if ($order_result->num_rows == 0): ?>
                <div style="text-align: center; font-size: 15px; margin-top: 50px; color: #546178;">
            No Notification available.
        </div>
                <?php endif; ?>
            </div>
        </div>

    </div>

</body>

</html>