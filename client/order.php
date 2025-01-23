<?php
// Start the session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database configuration
if (!isset($conn)) {
    require_once '../config/config.php';
}

// Fetch items for the first page
$item_query = $conn->prepare("SELECT id, description, img_url, stock, price, low_stock_level FROM item");
$item_query->execute();
$items_result = $item_query->get_result();

$is_logged_in = false; // Initialize as false by default

//fetch item details for 4th page according to item id
$item_query = $conn->prepare("SELECT id, description, price, stock, img_url FROM item WHERE id = ?");
$item_query->bind_param("i", $item_id);
$item_query->execute();
$item_result = $item_query->get_result();
$selected_item = $item_result->fetch_assoc();


// Check if user is logged in
if (isset($_SESSION['user_email'])) {
    $user_email = $_SESSION['user_email'];

    // Fetch user ID from the database
    $user_query = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $user_query->bind_param("s", $user_email);
    $user_query->execute();
    $user_result = $user_query->get_result();
    $user1 = $user_result->fetch_assoc();

    if ($user1) {
        $is_logged_in = true;  // User is logged in
    } else {
        echo "<script>Swal.fire('Error', 'User not found!', 'error');</script>";
        exit;
    }
}

function getCities($conn) {
    $query = "SELECT id, Name, Fee FROM cities";
    $result = $conn->query($query);
    $cities = [];

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $cities[] = $row;
        }
    }
    return $cities;
}

// Call the function with $conn as an argument
$cities = getCities($conn);


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Place Order</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <!-- Include jsPDF -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

    <!-- Include jsPDF AutoTable Plugin -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.21/jspdf.plugin.autotable.min.js"></script>



    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
    <style>
    .form-page {
        display: none;
        width: 100%;
    }

    .form-page.active {
        display: block;
    }

    .quantity-controls {
        display: flex;
        justify-content: center;
        align-items: center;
        margin: 50px 0;
    }

    .quantity-controls button {
        width: 40px;
        height: 40px;
        text-align: center;
        font-size: 20px;
    }

    .quantity-controls input {
        width: 60px;
        text-align: center;
        font-size: 18px;
        margin: 0 10px;
    }

    #items-container {
        display: flex;
        flex-wrap: nowrap;

        gap: 125px;

        overflow-x: auto;

        cursor: pointer;
    }

    .item-container {
        text-align: center;
        flex: 0 0 auto;
        width: 50%;
        cursor: pointer;
    }

    .form-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin: 50px 0;
        gap: 65%
    }

    .form-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
    }

    .form-group {
        display: flex;
        flex-direction: column;
    }

    label {
        margin-bottom: 5px;
        font-weight: 500;
    }

    input,
    select {
        padding: 8px;
        font-size: 16px;
        border: 1px solid #ccc;
        border-radius: 4px;
    }

    

    @media (max-width: 768px) {
        #items-container {
            flex-wrap: wrap;
           
            justify-content: center;
            
            gap: 20px;
            
        }

        .item-container {
            width: 100%;
            
            max-width: 300px;
            
            margin: 0 auto 10px;
            
        }

    }
    </style>
</head>

<body>
    <?php include './header.php'; ?>
    <div style='margin: 40px;color: #546178'>
        <div style='font-size: 15px; color: #cd0a0a; font-weight: medium;'>Home / Order Now</div>
        <div
            style='margin-top:50px;text-align:center;font-size:35px ;font-weight:700;margin-bottom:20px;color: #546178'>
            Place Your Order</div>
        <div class="form-container" style="display: flex; justify-content: center; align-items: center; height:auto;">
            <form id="multiPageForm" method="POST" action="" style='width:700px'>
            <input type="hidden" id="userType" value="<?= isset($_SESSION['type']) ? htmlspecialchars($_SESSION['type'], ENT_QUOTES, 'UTF-8') : '' ?>">

                <!-- Page 1: Select Item -->
                <div class="form-page active" id="page1">
                    <p style='font-weight:600;font-size:20px;margin-bottom:60px'>Select Cylinder Type</p>
                    <div id="items-container">
                        <?php while ($item = $items_result->fetch_assoc()) { ?>
                        <div id="item_<?= $item['id'] ?>" style='cursor: pointer;'>
                            <?php if ($item['stock'] > 0): // Only show radio button if in stock ?>
                            <div style="display: flex; justify-content: center; align-items: center;">
                                <input type="radio" name="item_id" id="radio_<?= $item['id'] ?>"
                                    value="<?= $item['id'] ?>" data-price="<?= $item['price'] ?>"
                                    data-stock="<?= $item['stock'] ?>"
                                    data-description="<?= htmlspecialchars($item['description'], ENT_QUOTES, 'UTF-8') ?>"
                                    required>
                            </div>
                            <?php endif; ?>
                            <label for="radio_<?= $item['id'] ?>">
                                <div><img src="<?= $item['img_url'] ?>" alt="<?= $item['description'] ?>"
                                        style="width:150px; height:170px;"></div>
                                <div><strong><?= $item['description'] ?></strong></div>
                                <div style='text-align:center;font-size:13px'>
                                    <?= $item['stock'] <= 0 ? '<span style="color:red;">Out of Stock</span>' : '<span style="color:green;">In Stock</span>' ?>
                                </div>
                                <div style='text-align:center'><span>Rs. <?= number_format($item['price'], 2) ?></span>
                                </div>
                            </label>
                        </div>
                        <?php } ?>
                    </div>
                    <div class="quantity-controls">
                        <button type="button" id="decrement" style='background-color:#dc3545'>-</button>
                        <input type="number" id="quantity" name="selected_quantity" value="1" min="1" readonly>
                        <button type="button" id="increment" style='background-color:#0d6efd'>+</button>
                    </div>
                    <div>
                    <?php 
                    $msg = "";
        if ($_SESSION) {
            if ($_SESSION['type'] === 'Residential') {
                $msg = "As a Residential Customer, you can only purchase up to 2 cylinders at once.";
            } elseif ($_SESSION['type'] === 'Business') {
                $msg = "As a Business Customer, you can only purchase up to 5 cylinders at once.";
            }
        }
    ?>
    <span style="font-size:14px;font-weight:400;color:red"><?php echo htmlspecialchars($msg); ?></span>
                    </div>
                </div>

                <!-- Page 2: Order Details -->
                <div class="form-page" id="page2">
                    <p style='font-weight:600;font-size:20px;margin-bottom:30px'>Add Your Delivery Details</p>
                    <input type="hidden" name="price" id="selected_price">
                    <label for="delivery_name">Delivery Name:</label>
                    <input type="text" id="delivery_name" name="delivery_name" required><br>

                    <label for="delivery_address">Delivery Address:</label>
                    <input type="text" id="delivery_address" name="delivery_address" required><br>

                    <select id="delivery_city" name="delivery_city" required>
    <option value="">Select City</option>
    <?php foreach ($cities as $city): ?>
        <option value="<?= $city['id'] ?>" data-fee="<?= $city['Fee'] ?>">
            <?= htmlspecialchars($city['Name']) ?> (Fee: Rs. <?= number_format($city['Fee'], 2) ?>)
        </option>
    <?php endforeach; ?>
</select>
<input type="hidden" id="delivery_fee" name="delivery_fee">
<input type="hidden" id="final_amount" name="final_amount">


                    <label for="contact">Contact:</label>
                    <input type="text" id="contact" name="contact" required>
                </div>

                <!-- Page 3: Payment Details -->
                <div class="form-page" id="page3">
                    <p style="font-weight:600; font-size:20px; margin-bottom:30px;">Add Payment Details</p>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="card_type">Card Type:</label>
                            <select id="card_type" name="card_type" required>
                                <option value="Visa">Visa</option>
                                <option value="MasterCard">MasterCard</option>
                                <option value="American Express">American Express</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="card_no">Card Number:</label>
                            <input type="text" id="card_no" name="card_no" pattern="\d{16}"
                                placeholder="16-digit card number" required>
                        </div>
                        <div class="form-group">
                            <label for="exp_month">Expiry Month:</label>
                            <input type="number" id="exp_month" name="exp_month" min="1" max="12" required>
                        </div>
                        <div class="form-group">
                            <label for="exp_year">Expiry Year:</label>
                            <input type="number" id="exp_year" name="exp_year" min="<?= date('Y') ?>"
                                max="<?= date('Y') + 20 ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="cvv">CVV:</label>
                            <input type="password" id="cvv" name="cvv" pattern="\d{3}" placeholder="3-digit CVV"
                                required>
                        </div>
                    </div>
                </div>
                <!-- Add the new invoice page to the form -->
                <div class="form-page" id="page4">
                    <div id="invoice-details" style="text-align: left; font-size: 16px;">
                        <!-- Invoice details will be populated dynamically -->
                    </div>
                    <button type="button" id="downloadInvoice"
                        style="margin-top: 50px; background-color:#0d6efd; display:none; width:150px;">
                        Download Invoice
                    </button>

                </div>

                <div class="form-footer">
                    <?php if ($is_logged_in): ?>
                    <button type="button" id="prevButton" disabled style='background-color:#dc3545'>
                        < Previous </button>
                            <button type="button" id="nextButton" style='background-color:#0d6efd'>Next ></button>
                            <button type="submit" id="submitButton" style="display: none;">Place Order</button>
                            <?php else: ?>
                            <p style="color: red; text-align: center;">Please log in to continue.</p>
                            <?php endif; ?>
                </div>

            </form>
        </div>
    </div>

    <script src="./order.js">

    </script>
    <script src="./invoice.js">
    
    </script>


    <?php
   if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Prevent form resubmission on refresh
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Include database configuration
    if (!isset($conn)) {
        require_once '../config/config.php';
    }

    // Check if user is logged in
    if (isset($_SESSION['user_email'])) {
        $user_email = $_SESSION['user_email'];

        // Fetch user ID from the database
        $user_query = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $user_query->bind_param("s", $user_email);
        $user_query->execute();
        $user_result = $user_query->get_result();
        $user = $user_result->fetch_assoc();

        if ($user) {
            $user_id = $user['id']; // Get user ID
        } else {
            echo "<script>Swal.fire('Error', 'User not found!', 'error');</script>";
            exit;
        }
    } else {
        echo "<script>Swal.fire('Error', 'You must be logged in to place an order.', 'error');</script>";
        exit;
    }

    // Get form data
    $item_id = $_POST['item_id'];
    $unit_price = $_POST['price'];
    $quantity = $_POST['selected_quantity'];
    $totalAmount = $unit_price * $quantity;
    $delivery_name = $_POST['delivery_name'];
    $delivery_address = $_POST['delivery_address'];
    $deliveryCity = $_POST['delivery_city'];
    $contact = $_POST['contact'];
    $order_date = date("Y-m-d H:i:s");
    $card_type = $_POST['card_type'];
    $card_no = $_POST['card_no'];
    $exp_month = $_POST['exp_month'];
    $exp_year = $_POST['exp_year'];
    $cvv = $_POST['cvv'];
    $item_description = $item['description'];

    // Fetch delivery fee for the selected city
$city_query = $conn->prepare("SELECT fee FROM cities WHERE id = ?");
$city_query->bind_param("i", $deliveryCity);
$city_query->execute();
$city_result = $city_query->get_result();

if ($city_result->num_rows > 0) {
    $city_row = $city_result->fetch_assoc();
    $delivery_fee = floatval($city_row['fee']); // Assign the delivery fee
} else {
    $delivery_fee = 0; // Default to 0 if the city is not found
}
$city_query->close();

$final_amount = $totalAmount + $delivery_fee; // Calculate final amount

    // Start transaction
    $conn->begin_transaction();

    try {
        // Save order details
        $order_query = $conn->prepare("INSERT INTO orders (user_id, item_id,unit_price, quantity, totalAmount, delivery_name, delivery_address, contact, order_date,delivery_city,delivery_fee,finalAmount) VALUES (?, ?,?, ?, ?, ?, ?, ?, ?,?,?,?)");
        $order_query->bind_param("iididsssssdd", $user_id, $item_id, $unit_price,$quantity, $totalAmount, $delivery_name, $delivery_address, $contact, $order_date,$deliveryCity,$delivery_fee, $final_amount);
        $order_query->execute();
        $order_id = $conn->insert_id; // Get the last inserted order ID

        // Save payment details
        $payment_status = 'Paid';
        $payment_query = $conn->prepare("INSERT INTO payment (order_id, card_type, card_no, exp_month, exp_year, cvv, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $payment_query->bind_param("issiiis", $order_id, $card_type, $card_no, $exp_month, $exp_year, $cvv, $payment_status);
        $payment_query->execute();

        // Update stock in the item table
        $stock_query = $conn->prepare("UPDATE item SET stock = stock - ? WHERE id = ?");
        $stock_query->bind_param("ii", $quantity, $item_id);
        $stock_query->execute();

        // Check stock level after update
        $stock_check_query = $conn->prepare("SELECT stock, low_stock_level, description FROM item WHERE id = ?");
        $stock_check_query->bind_param("i", $item_id);
        $stock_check_query->execute();
        $stock_result = $stock_check_query->get_result();
        $item_data = $stock_result->fetch_assoc();

        if ($item_data['stock'] < 0) {
            echo "<script>Swal.fire('Error', 'Item is out of stock!', 'error');</script>";
            $conn->rollback();
            exit;
        }

        // Save a notification if stock is low
        if ($item_data['stock'] <= $item_data['low_stock_level']) {
            $notification_description = "Low stock alert: Item '{$item_data['description']}' is at {$item_data['stock']} units.";
            $notification_query = $conn->prepare("INSERT INTO notification (description) VALUES (?)");
            $notification_query->bind_param("s", $notification_description);
            $notification_query->execute();
        }

        // Commit transaction
        $conn->commit();

        echo "<script>
            Swal.fire('Success', 'Order placed and payment processed successfully!', 'success');
            document.getElementById('downloadInvoice').style.display = 'block';
            document.getElementById('submitButton').style.display = 'none';  
            window.orderId = {$order_id};
            </script>";
        exit;

    } catch (Exception $e) {
        $conn->rollback();
        echo "<script>Swal.fire('Error', 'Failed to place order.', 'error');</script>";
    }
}

    ?>
    <?php include './footer.php'; ?>
</body>

</html>