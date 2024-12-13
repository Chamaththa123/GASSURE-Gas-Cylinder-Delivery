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
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Form</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
        flex-wrap: nowrap; /* Ensures items stay in the same row */
        gap: 125px; /* Adds space between items */
        overflow-x: auto; /* Allows horizontal scrolling if items exceed the container width */
        cursor: pointer;
    }
        
    .item-container {
        text-align: center;
        flex: 0 0 auto; /* Prevents items from shrinking or growing */
        width: 50%;
        cursor: pointer;
    }

    .form-footer{
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin: 50px 0;
        gap:65%
    }
    </style>
</head>

<body >
    <?php include './header.php'; ?>
    <div style='margin: 40px;color: #546178'>
    <div style='font-size: 15px; color: #cd0a0a; font-weight: medium;'>Home / Order Now</div>
    <div style='margin-top:50px;text-align:center;font-size:35px ;font-weight:700;margin-bottom:20px;color: #546178'>
    Place Your Order</div>
    <div class="form-container" style="display: flex; justify-content: center; align-items: center; height:auto;">
        <form id="multiPageForm" method="POST" action="" style='width:700px'>
            <!-- Page 1: Select Item -->
            <div class="form-page active" id="page1">
                <p style='font-weight:600;font-size:20px;margin-bottom:60px'>Select Cylinder Type</p>
                <div id="items-container">
                    <?php while ($item = $items_result->fetch_assoc()) { ?>
                        <div id="item_<?= $item['id'] ?>" style='cursor: pointer;'>
                           <div style="display: flex; justify-content: center; align-items: center;"> <input type="radio" name="item_id" id="radio_<?= $item['id'] ?>" value="<?= $item['id'] ?>"
                           data-price="<?= $item['price'] ?>" data-stock="<?= $item['stock'] ?>" required></div>
                            <label for="radio_<?= $item['id'] ?>">
                                <div><img src="<?= $item['img_url'] ?>" alt="<?= $item['description'] ?>" style="width:150px; height:170px;"></div>
                                <div><strong><?= $item['description'] ?></strong></div>
                                <div style='text-align:center'><span>Rs. <?= number_format($item['price'], 2) ?></span></div>
                            </label>
                        </div>
                    <?php } ?>
                </div>
                <div class="quantity-controls">
                    <button type="button" id="decrement" style='background-color:#dc3545'>-</button>
                    <input type="number" id="quantity" name="selected_quantity" value="1" min="1" readonly>
                    <button type="button" id="increment" style='background-color:#0d6efd'>+</button>
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

                <label for="contact">Contact:</label>
                <input type="text" id="contact" name="contact" required>
            </div>

            <!-- Page 3: Payment Details -->
            <div class="form-page" id="page3">
            <p style='font-weight:600;font-size:20px;margin-bottom:30px'>Add Payment Details</p>
                <label for="card_type">Card Type:</label>
                <select id="card_type" name="card_type" required>
                    <option value="Visa">Visa</option>
                    <option value="MasterCard">MasterCard</option>
                    <option value="American Express">American Express</option>
                </select><br>

                <label for="card_no">Card Number:</label>
                <input type="text" id="card_no" name="card_no" pattern="\d{16}" placeholder="16-digit card number" required><br>

                <label for="exp_month">Expiry Month:</label>
                <input type="number" id="exp_month" name="exp_month" min="1" max="12" required><br>

                <label for="exp_year">Expiry Year:</label>
                <input type="number" id="exp_year" name="exp_year" min="<?= date('Y') ?>" max="<?= date('Y') + 20 ?>" required><br>

                <label for="cvv">CVV:</label>
                <input type="password" id="cvv" name="cvv" pattern="\d{3}" placeholder="3-digit CVV" required><br>
            </div>

            <div class="form-footer">
                <button type="button" id="prevButton" disabled style='background-color:#dc3545'>< Previous</button>
                <button type="button" id="nextButton" style='background-color:#0d6efd'>Next ></button>
                <button type="submit" id="submitButton" style="display: none;">Place Order</button>
            </div>
        </form>
    </div>
    </div>

    <script>
        const pages = document.querySelectorAll('.form-page');
        const nextButton = document.getElementById('nextButton');
        const prevButton = document.getElementById('prevButton');
        const submitButton = document.getElementById('submitButton');
        const quantityInput = document.getElementById('quantity');
        const decrementButton = document.getElementById('decrement');
        const incrementButton = document.getElementById('increment');
        const selectedPriceInput = document.getElementById('selected_price');

        let currentPage = 0;

        function updatePage() {
            pages.forEach((page, index) => {
                page.classList.toggle('active', index === currentPage);
            });
            prevButton.disabled = currentPage === 0;
            nextButton.style.display = currentPage === pages.length - 1 ? 'none' : 'inline-block';
            submitButton.style.display = currentPage === pages.length - 1 ? 'inline-block' : 'none';
        }

        nextButton.addEventListener('click', () => {
            if (currentPage === 0) {
                const selectedItem = document.querySelector('input[name="item_id"]:checked');
                if (!selectedItem) {
                    Swal.fire({
            icon: 'warning', // Use 'warning' for this scenario
            title: 'Warning',
            text: 'Select an item!',
        });
                    return;
                }
                const maxQuantity = parseInt(selectedItem.dataset.stock);
                if (parseInt(quantityInput.value) > maxQuantity) {
                    alert(`Maximum available stock is ${maxQuantity}.`);
                    return;
                }
                selectedPriceInput.value = selectedItem.dataset.price;
            }
            currentPage++;
            updatePage();
        });

        prevButton.addEventListener('click', () => {
            currentPage--;
            updatePage();
        });

        decrementButton.addEventListener('click', () => {
            const currentQuantity = parseInt(quantityInput.value);
            quantityInput.value = Math.max(1, currentQuantity - 1);
        });

        incrementButton.addEventListener('click', () => {
            const selectedItem = document.querySelector('input[name="item_id"]:checked');
            if (selectedItem) {
                const maxQuantity = parseInt(selectedItem.dataset.stock);
                const currentQuantity = parseInt(quantityInput.value);
                quantityInput.value = Math.min(maxQuantity, currentQuantity + 1);
            } else {
                Swal.fire({
            icon: 'warning', // Use 'warning' for this scenario
            title: 'Warning',
            text: 'Please select an item first!',
        });
            }
        });

        updatePage();
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
        $totalAmount = $_POST['price'];
        $quantity = $_POST['selected_quantity'];
        $delivery_name = $_POST['delivery_name'];
        $delivery_address = $_POST['delivery_address'];
        $contact = $_POST['contact'];
        $order_date         = date("Y-m-d H:i:s");
        $card_type = $_POST['card_type'];
        $card_no = $_POST['card_no'];
        $exp_month = $_POST['exp_month'];
        $exp_year = $_POST['exp_year'];
        $cvv = $_POST['cvv'];

        // Save order details
        $order_query = $conn->prepare("INSERT INTO orders (user_id, item_id, quantity, totalAmount, delivery_name, delivery_address, contact, order_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $order_query->bind_param("iiidssss", $user_id, $item_id, $quantity, $totalAmount, $delivery_name, $delivery_address, $contact, $order_date);

        if ($order_query->execute()) {
            $order_id = $conn->insert_id; // Get the last inserted order ID

            // Save payment details
            $payment_status = 'Paid';
            $payment_query = $conn->prepare("INSERT INTO payment (order_id, card_type, card_no, exp_month, exp_year, cvv, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $payment_query->bind_param("issiiis", $order_id, $card_type, $card_no, $exp_month, $exp_year, $cvv, $payment_status);

            if ($payment_query->execute()) {
                echo "<script>Swal.fire('Success', 'Order placed and payment processed successfully!', 'success');</script>";
                echo "<script>setTimeout(() => { window.location.href = 'user-profile.php'; }, 2000);</script>";
                exit;
            } else {
                echo "<script>Swal.fire('Error', 'Failed to process payment.', 'error');</script>";
            }
        } else {
            echo "<script>Swal.fire('Error', 'Failed to save order details.', 'error');</script>";
        }
    }
    ?>
</body>

</html>

