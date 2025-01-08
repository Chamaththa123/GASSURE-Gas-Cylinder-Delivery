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
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Place Order</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>


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
        /* Ensures items stay in the same row */
        gap: 125px;
        /* Adds space between items */
        overflow-x: auto;
        /* Allows horizontal scrolling if items exceed the container width */
        cursor: pointer;
    }

    .item-container {
        text-align: center;
        flex: 0 0 auto;
        /* Prevents items from shrinking or growing */
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

    /* Existing styles remain unchanged */

/* Existing styles remain unchanged */

@media (max-width: 768px) {
    #items-container {
        flex-wrap: wrap;
        /* Allow items to wrap to the next row */
        justify-content: center;
        /* Center items horizontally */
        gap: 20px;
        /* Adjust gap for smaller screens */
    }

    .item-container {
        width: 100%;
        /* Take full width of the container */
        max-width: 300px;
        /* Optional: Limit the width of the item for better alignment */
        margin: 0 auto 10px;
        /* Center the item and add spacing between rows */
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
                                    data-stock="<?= $item['stock'] ?>" required>
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
    <p style="font-weight:600; font-size:20px; margin-bottom:30px;">Invoice</p>
    <div id="invoice-details" style="text-align: left; font-size: 16px;">
        <!-- Invoice details will be populated dynamically -->
    </div>
    <button type="button" id="downloadInvoice" style="margin-top: 20px; background-color:#0d6efd;">Download Invoice</button>
</div>

                <div class="form-footer">
    <?php if ($is_logged_in): ?>
        <button type="button" id="prevButton" disabled style='background-color:#dc3545'>
            < Previous
        </button>
        <button type="button" id="nextButton" style='background-color:#0d6efd'>Next ></button>
        <button type="submit" id="submitButton" style="display: none;">Place Order</button>
    <?php else: ?>
        <p style="color: red; text-align: center;">Please log in to continue.</p>
    <?php endif; ?>
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
                icon: 'warning',
                title: 'Select an Item',
                text: 'Please select an item to proceed.',
            });
            return;
        }
        const maxQuantity = parseInt(selectedItem.dataset.stock);
        if (parseInt(quantityInput.value) > maxQuantity) {
            Swal.fire({
                icon: 'error',
                title: 'Insufficient Stock',
                text: `Only ${maxQuantity} items are available in stock.`,
            });
            return;
        }
        selectedPriceInput.value = selectedItem.dataset.price;
    } else if (currentPage === 1) {
        const deliveryName = document.getElementById('delivery_name').value.trim();
        const deliveryAddress = document.getElementById('delivery_address').value.trim();
        const contact = document.getElementById('contact').value.trim();
        if (!deliveryName || !deliveryAddress || !contact) {
            Swal.fire({
                icon: 'error',
                title: 'Missing Details',
                text: 'Please fill out all required fields.',
            });
            return;
        }
    } else if (currentPage === 2) {
        const cardNo = document.getElementById('card_no').value;
        const expMonth = document.getElementById('exp_month').value;
        const expYear = document.getElementById('exp_year').value;
        const cvv = document.getElementById('cvv').value;
        if (!cardNo || !expMonth || !expYear || !cvv) {
            Swal.fire({
                icon: 'error',
                title: 'Missing Payment Details',
                text: 'Please fill out all payment details.',
            });
            return;
        }

        // Populate the Invoice Section on Page 4
        const selectedItem = document.querySelector('input[name="item_id"]:checked');
        const itemPrice = parseFloat(selectedItem.dataset.price);
        const quantity = parseInt(quantityInput.value);
        const totalPrice = (itemPrice * quantity).toFixed(2);
        const deliveryName = document.getElementById('delivery_name').value;
        const deliveryAddress = document.getElementById('delivery_address').value;
        const contact = document.getElementById('contact').value;

        const invoiceDetails = `
            <p><strong>Order Summary</strong></p>
            <p>Price per Unit: Rs. ${itemPrice.toFixed(2)}</p>
            <p>Quantity: ${quantity}</p>
            <p>Total Price: Rs. ${totalPrice}</p>
            <hr>
            <p><strong>Delivery Details</strong></p>
            <p>Name: ${deliveryName}</p>
            <p>Address: ${deliveryAddress}</p>
            <p>Contact: ${contact}</p>
            <hr>
            <p><strong>Payment Summary</strong></p>
            <p>Card Type: ${document.getElementById('card_type').value}</p>
            <p>Card Number: **** **** **** ${document.getElementById('card_no').value.slice(-4)}</p>
        `;

        document.getElementById('invoice-details').innerHTML = invoiceDetails;
    }

    currentPage++;
    updatePage();
});


        prevButton.addEventListener('click', () => {
            currentPage--;
            updatePage();
        });

        decrementButton.addEventListener('click', () => {
            const currentValue = parseInt(quantityInput.value, 10);
            if (currentValue > 1) {
                quantityInput.value = currentValue - 1;
            }
        });

        incrementButton.addEventListener('click', () => {
            const currentValue = parseInt(quantityInput.value, 10);
            const selectedItem = document.querySelector('input[name="item_id"]:checked');
            const maxQuantity = selectedItem ? parseInt(selectedItem.dataset.stock) : Infinity;
            if (currentValue < maxQuantity) {
                quantityInput.value = currentValue + 1;
            }
        });

       
        updatePage();
    </script>
<script>
     document.getElementById('downloadInvoice').addEventListener('click', () => {
    const { jsPDF } = window.jspdf;

    // Create a new jsPDF instance
    const doc = new jsPDF();

    // Get the content of the invoice section
    const invoiceContent = document.getElementById('invoice-details');

    // Ensure content exists before proceeding
    if (!invoiceContent) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Invoice details are missing!',
        });
        return;
    }

    // Add content to the PDF
    doc.html(invoiceContent, {
        callback: function (doc) {
            // Save the PDF with a custom name
            doc.save('invoice.pdf');
        },
        x: 10,
        y: 10,
    });
});

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
    $contact = $_POST['contact'];
    $order_date = date("Y-m-d H:i:s");
    $card_type = $_POST['card_type'];
    $card_no = $_POST['card_no'];
    $exp_month = $_POST['exp_month'];
    $exp_year = $_POST['exp_year'];
    $cvv = $_POST['cvv'];

    // Start transaction
    $conn->begin_transaction();

    try {
        // Save order details
        $order_query = $conn->prepare("INSERT INTO orders (user_id, item_id,unit_price, quantity, totalAmount, delivery_name, delivery_address, contact, order_date) VALUES (?, ?,?, ?, ?, ?, ?, ?, ?)");
        $order_query->bind_param("iididssss", $user_id, $item_id, $unit_price,$quantity, $totalAmount, $delivery_name, $delivery_address, $contact, $order_date);
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

        echo "<script>Swal.fire('Success', 'Order placed and payment processed successfully!', 'success');</script>";
        echo "<script>setTimeout(() => { window.location.href = 'order-history.php'; }, 2000);</script>";
        exit;

    } catch (Exception $e) {
        $conn->rollback();
        echo "<script>Swal.fire('Error', 'Failed to place order.', 'error');</script>";
    }
}

    ?>
     <?php include '../includes/footer.php'; ?>
</body>

</html>