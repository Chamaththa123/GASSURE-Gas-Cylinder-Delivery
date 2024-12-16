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

// Fetch orders for the logged-in user
$order_result = null;
if ($user_id !== null) {
    $order_query = $conn->prepare(
        "SELECT o.*, i.*, o.id AS orderId 
         FROM orders o 
         JOIN item i ON o.item_id = i.id 
         WHERE o.user_id = ?
          ORDER BY i.id DESC" 
    );
    $order_query->bind_param("i", $user_id);
    $order_query->execute();
    $order_result = $order_query->get_result();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
    <style>
    .order-container {
        width: 100%;
        padding-left: 100px;
        padding-right: 100px
    }

    @media (max-width: 768px) {

        .order-container {
            width: 100%;
            padding-left: 0px;
            padding-right: 0px
        }
    }
    </style>
</head>
<?php include './header.php'; ?>

<body>
    <div style='margin: 40px;color: #546178'>
        <div style='font-size: 15px; color: #cd0a0a; font-weight: medium;'>Home / My Orders</div>
        <div style='margin-top:50px;text-align:left;font-size:35px ;font-weight:700;margin-bottom:20px;color: #546178'>
            My Orders</div>

        <div class='order-container'>
            <hr style="height: 1px; background-color: #b0b0b1; border: none;" />

            <?php while ($row = $order_result->fetch_assoc()): ?>
            <div>

                <table style='width:100%'>
                    <tr>
                        <td>ORDER PLACED<br /><span
                                style='font-size:13px'><?php echo htmlspecialchars(date('Y-m-d', strtotime($row['order_date']))); ?></span>
                        </td>

                        <td>TOTAL<br /><span style='font-size:13px'>Rs.
                                <?php echo htmlspecialchars($row['totalAmount']); ?></span>
                        </td>
                        <td style='text-align:right'>ORDER :
                            OR#<?php echo htmlspecialchars($row['orderId']); ?><br /><span
                                style='font-size:13px'><?php echo htmlspecialchars($row['status']); ?></span></td>
                    </tr>
                    <tr>
                        <td style='width:20%'> <img src="<?php echo htmlspecialchars($row['img_url']); ?>"
                                alt="Item Image" style="width: 100px; height: auto;"></td>
                        <td style='width:40%'><span
                                style='font-weight:bold;font-size:17px'><?php echo htmlspecialchars($row['description']); ?></span>
                            <br /><span>Unit Price : Rs.<?php echo htmlspecialchars($row['price']); ?></span>
                            <br /><span>Qty : <?php echo htmlspecialchars($row['quantity']); ?></span>
                        </td>
                        <td style='width:40%;text-align:right'>
                            <?php echo htmlspecialchars($row['delivery_name']); ?><br />
                            <?php echo htmlspecialchars($row['delivery_address']); ?><br />
                            <?php echo htmlspecialchars($row['contact']); ?>
                        </td>
                    </tr>
                    </tbody>
                </table>
                <hr style="height: 1px; background-color: #b0b0b1; border: none;" />
            </div>


            <?php endwhile; ?>
        </div>
    </div>

</body>

</html>