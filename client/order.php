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
    }

    .form-page.active {
        display: block;
    }

    .quantity-controls {
        display: inline-flex;
        align-items: center;
    }

    .quantity-controls button {
        width: 25px;
        height: 25px;
        text-align: center;
    }

    .quantity-controls input {
        width: 40px;
        text-align: center;
        margin: 0 5px;
    }
    </style>
</head>

<body>
    <?php include './header.php'; ?>
    <div class="form-container">
        <form id="multiPageForm" method="POST" action="">
            <!-- Page 1: Select Item -->
            <div class="form-page active" id="page1">
                <h3>Select Item</h3>
                <?php while ($item = $items_result->fetch_assoc()) { ?>
                <div>
                    <input type="radio" name="item_id" id="item_<?= $item['id'] ?>" value="<?= $item['id'] ?>"
                        data-price="<?= $item['price'] ?>" required>
                    <label for="item_<?= $item['id'] ?>">
                        <img src="<?= $item['img_url'] ?>" alt="<?= $item['description'] ?>"
                            style="width:50px; height:50px;">
                        <strong><?= $item['description'] ?></strong>
                        <span>Price: <?= $item['price'] ?></span>
                    </label>
                    <div class="quantity-controls">
                        <button type="button" class="decrement" data-target="quantity_<?= $item['id'] ?>">-</button>
                        <input type="number" name="quantity_<?= $item['id'] ?>" id="quantity_<?= $item['id'] ?>" value="1" min="1" max="<?= $item['stock'] ?>">
                        <button type="button" class="increment" data-target="quantity_<?= $item['id'] ?>">+</button>
                    </div>
                </div>
                <?php } ?>
            </div>

            <!-- Page 2: Order Details -->
            <div class="form-page" id="page2">
                <h3>Order Details</h3>
                <input type="hidden" name="price" id="selected_price">
                <input type="hidden" name="selected_quantity" id="selected_quantity">
                <label for="delivery_name">Delivery Name:</label>
                <input type="text" id="delivery_name" name="delivery_name" required><br>

                <label for="delivery_address">Delivery Address:</label>
                <textarea id="delivery_address" name="delivery_address" required></textarea><br>

                <label for="contact">Contact:</label>
                <input type="text" id="contact" name="contact" required>
            </div>

            <div class="form-footer">
                <button type="button" id="prevButton" disabled>Previous</button>
                <button type="button" id="nextButton">Next</button>
                <button type="submit" id="submitButton" style="display: none;">Submit</button>
            </div>
        </form>
    </div>

    <script>
    const pages = document.querySelectorAll('.form-page');
    const nextButton = document.getElementById('nextButton');
    const prevButton = document.getElementById('prevButton');
    const submitButton = document.getElementById('submitButton');
    const selectedPriceInput = document.getElementById('selected_price');
    const selectedQuantityInput = document.getElementById('selected_quantity');
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
                alert('Please select an item!');
                return;
            }
            const quantityField = document.getElementById(`quantity_${selectedItem.value}`);
            selectedPriceInput.value = selectedItem.dataset.price;
            selectedQuantityInput.value = quantityField.value;
        }
        currentPage++;
        updatePage();
    });

    prevButton.addEventListener('click', () => {
        currentPage--;
        updatePage();
    });

    document.querySelectorAll('.decrement').forEach(button => {
        button.addEventListener('click', (e) => {
            const targetId = e.target.dataset.target;
            const input = document.getElementById(targetId);
            input.value = Math.max(1, parseInt(input.value) - 1);
        });
    });

    document.querySelectorAll('.increment').forEach(button => {
        button.addEventListener('click', (e) => {
            const targetId = e.target.dataset.target;
            const input = document.getElementById(targetId);
            const max = parseInt(input.max);
            input.value = Math.min(max, parseInt(input.value) + 1);
        });
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
        $price = $_POST['price'];
        $quantity = $_POST['selected_quantity'];
        $delivery_name = $_POST['delivery_name'];
        $delivery_address = $_POST['delivery_address'];
        $contact = $_POST['contact'];
        $order_date = date('Y-m-d H:i:s');
        $status = 'Pending';

        // Calculate total amount
        $totalAmount = $price * $quantity;

        // Insert order into the database
        $order_query = $conn->prepare("INSERT INTO orders (user_id, order_date, item_id, quantity, totalAmount, delivery_name, delivery_address, contact, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $order_query->bind_param("isidsssss", $user_id, $order_date, $item_id, $quantity, $totalAmount, $delivery_name, $delivery_address, $contact, $status);

        if ($order_query->execute()) {
            echo "<script>Swal.fire('Success', 'Order placed successfully!', 'success');</script>";
            echo "<script>setTimeout(() => { window.location.href = 'user-profile.php'; }, 2000);</script>";
            exit;
        } else {
            echo "<script>Swal.fire('Error', 'Failed to place the order.', 'error');</script>";
        }
    }
    ?>

</body>

</html>
