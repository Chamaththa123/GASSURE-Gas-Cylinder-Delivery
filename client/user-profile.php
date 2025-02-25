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
$user_id = null; 

// Fetch logged-in user details
$user = null; // Initialize to null to avoid undefined variable errors
if (isset($_SESSION['user_email'])) {
    $user_email = $_SESSION['user_email'];

    // Prepare and execute the query to fetch user details
    $user_query = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $user_query->bind_param("s", $user_email);
    $user_query->execute();
    $user_result = $user_query->get_result();
    $user = $user_result->fetch_assoc();

    $user_id = $user ? $user['id'] : null;
}

$order_result = null;
if ($user_id !== null) {
    $order_query = $conn->prepare(
        "SELECT o.*, i.*, o.id AS orderId ,c.Name as cityName
         FROM orders o 
         JOIN item i ON o.item_id = i.id 
         JOIN cities c ON o.delivery_city = c.id 
         WHERE o.user_id = ? 
         ORDER BY o.id DESC LIMIT 3" 
    );
    $order_query->bind_param("i", $user_id);
    $order_query->execute();
    $order_result = $order_query->get_result();
}

// Handle logout functionality
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['logout'])) {
    session_unset();
    session_destroy();
    header('Location: ../index.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
    <link rel="stylesheet" href="public/assets/css/profile.css">
    <style>
    .card-content {
        padding: 20px;
    }

    .card-content h2 {
        margin: 0 0 10px;
        font-size: 24px;
        color: #333;
    }

    .card-content p {
        margin: 0;
        font-size: 16px;
        color: #666;
    }

    .content-wrapper {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
        padding: 20px;
        margin-top: 20px;
        color: #546178
    }

    .section {
        padding: 20px;
        /* background-color: #f4f4f4; */
        border-radius: 10px;
    }

    .left-section {
        flex: 2;
        /* Larger section */
        width: 40%;
    }

    .right-section {
        flex: 1;
        /* Smaller section */
        width: 60%;
    }

    .edit-button,
    .delete-button {
        background-color: #dc3545;
        color: white;
        border: none;
        border-radius: 5px;
        padding: 8px 10px;
        cursor: pointer;
        width: 100px;
        transition: background-color 0.3s ease;
    }

    .delete-button {
        background-color: #dc3545;
    }

    /* Stack sections vertically on smaller screens */
    @media (max-width: 768px) {

        .left-section,
        .right-section {
            flex-basis: 100%;
            /* Take full width */
        }
    }
    </style>
</head>

<body>
    <?php include './header.php'; ?>
    <div style='margin: 40px'>
        <div style='font-size: 15px; color: #cd0a0a; font-weight: medium;'>Home / User profile</div>

        <div style='margin-top:50px'>
            <div class="content-wrapper">
                <div class="right-section">
                    <img src="../images/user.png" style="width: 60%; display: block; margin: 0 auto;" alt="User Image">

                    <?php if ($user): ?>
                    <h2 style='text-align:center'>
                        <?php echo htmlspecialchars($user['first_name'] . " " . $user['last_name']); ?>
                    </h2>
                    <?php 
        $imageSrc = "../images/user.png";
        if ($user) {
            if ($user['type'] === 'Residential') {
                $imageSrc = "../images/house.png";
            } elseif ($user['type'] === 'Business') {
                $imageSrc = "../images/vendor.png";
            }
        }
    ?>
                    <p
                        style="display: flex; align-items: center; justify-content: left; gap: 20px;margin-top:40px;margin-bottom:40px">
                        <img src="<?php echo htmlspecialchars($imageSrc); ?>" style="width: 50px;" alt="User Image">
                        <span style="font-size:18px;font-weight:600"><?php echo htmlspecialchars($user['type']); ?>
                            Customer</span>
                    </p>

                    <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                    <p><strong>Address:</strong> <?php echo htmlspecialchars($user['address']); ?></p>
                    <?php else: ?>
                    <p>No user details available.</p>
                    <?php endif; ?>
                    <form method="post">
                        <button type="submit" name="logout" class="delete-button">Logout</button>
                    </form>
                </div>
                <div class="left-section">
                    <h2 style='text-align:left;font-size:18px;font-weight:600'>
                        Recent Orders
                    </h2>
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
                                            <?php echo htmlspecialchars($row['finalAmount']); ?></span>
                                    </td>
                                    <td style='text-align:right'>ORDER :
                                        OR#<?php echo htmlspecialchars($row['orderId']); ?><br /><?php
                    $color = '';
                    $status = htmlspecialchars($row['status']);
                    switch ($status) {
                        case 'Pending':
                            $color = 'font-weight:bold;color: orange;padding:2px;background-color:#fed7af;border-radius:10px;padding-left:15px;padding-right:15px';
                            break;
                        case 'In Progress':
                            $color = 'font-weight:bold;color: blue;padding:2px;background-color:#71afff;border-radius:10px;padding-left:15px;padding-right:15px';
                            break;
                        case 'Delivered':
                            $color = 'font-weight:bold;color: green;padding:2px;background-color:#6dff49;border-radius:10px;padding-left:15px;padding-right:15px';
                            break;
                        default:
                            $color = 'font-weight:bold;color: black;padding:2px;background-color:orange;border-radius:10px;padding-left:15px;padding-right:15px';
                            break;
                    }
                    ?>
                                        <span style="font-size:13px; <?php echo $color; ?>"> <?php echo $status; ?>
                                        </span>
                                    </td>
                                    </td>
                                </tr>
                                <tr>
                                    <td style='width:20%'> <img src="<?php echo htmlspecialchars($row['img_url']); ?>"
                                            alt="Item Image" style="width: 100px; height: auto;"></td>
                                    <td style='width:40%'><span
                                            style='font-weight:bold;font-size:17px'><?php echo htmlspecialchars($row['description']); ?></span>
                                        <br /><span>Unit Price :
                                            Rs.<?php echo htmlspecialchars($row['price']); ?></span>
                                        <br /><span>Qty : <?php echo htmlspecialchars($row['quantity']); ?></span>
                                        <br /><span>Sub Total : Rs.
                                            <?php echo htmlspecialchars($row['totalAmount']); ?></span><br />
                                        <span><span>Delivery Fee : Rs.
                                                <?php echo htmlspecialchars($row['delivery_fee']); ?></span>

                                    </td>
                                    <td style='width:40%;text-align:right'>
                                        <?php echo htmlspecialchars($row['delivery_name']); ?><br />
                                        <?php echo htmlspecialchars($row['delivery_address']); ?><br />
                                        <?php echo htmlspecialchars($row['cityName']); ?><br />
                                        <?php echo htmlspecialchars($row['contact']); ?>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                            <hr style="height: 1px; background-color: #b0b0b1; border: none;" />
                        </div>


                        <?php endwhile; ?>
                        <?php if ($order_result->num_rows == 0): ?>
                        <div style="text-align: center; font-size: 15px; margin-top: 50px; color: #546178;">
                            No orders available.
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include './footer.php'; ?>

</body>

</html>