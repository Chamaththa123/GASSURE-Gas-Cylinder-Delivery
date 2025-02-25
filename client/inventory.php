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
      "SELECT * FROM item"
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
        <a class="active" href="./inventory.php">Inventory</a>
        <a href="./admin-orders.php">Orders</a>
        <a href="./user-admin.php">Customers</a>
        <a href="../index.php">Back</a>
    </div>

    <div style='color: #546178'>
        <div class='header'>
            <div style='font-size:24px;font-weight:600'>Inventory</div>
            <div><a href='./notification.php'><i class="fa fa-bell"
                        style="font-size:24px;margin-right:20px;color:#546178"></i></a></div>
        </div>
        <div class="content">
            <div class="content-one">
                <div class="order-container">
                    <div class="items-grid">
                        <?php while ($row = $order_result->fetch_assoc()): ?>
                        <div class="item">
                            <h3 style='font-weight:700'><?php echo htmlspecialchars($row['description']); ?></h3>
                            <div><img src="<?= $row['img_url'] ?>" alt="<?= $row['description'] ?>"
                                    style="width:150px; height:170px;"></div>
                            <div style='text-align:center;font-size:14px; margin-bottom:5px'>
                                <?= $row['stock'] <= 0 ? '<span style="color:red;">Out of Stock</span>' : '<span style="color:green;">In Stock</span>' ?>
                            </div>
                            <div style='text-align:center;font-size:14px; margin-bottom:5px'>Available Stock:
                                <?php echo htmlspecialchars($row['stock']); ?></div>
                            <div style='text-align:center;font-size:14px; margin-bottom:5px'>
                                Unit Price: Rs. <?php echo number_format((float)$row['price'], 2, '.', ''); ?>
                            </div>

                            <div style='text-align:center;font-size:14px; margin-bottom:5px'>Low Stock Level:
                                <?php echo htmlspecialchars($row['low_stock_level']); ?></div>
                            <button class='edit-button'
                                onclick='openEditModal(<?php echo $row["id"]; ?>, "<?php echo addslashes($row["price"]); ?>", "<?php echo addslashes($row["stock"]); ?>")'>Manage
                                Cylinder</button>

                        </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>
        </div>

    </div>


    <?php
    // Handle membership update
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_item'])) {
        $id = $_POST['id'];
        $price = $_POST['price'];
        $stock = $_POST['stock'];
    
        $stmt = $conn->prepare("UPDATE item SET price = ?, stock = ? WHERE id = ?");
        $stmt->bind_param("sii", $price, $stock,$id);
    
        if ($stmt->execute()) {
            echo "<script>
                document.addEventListener('DOMContentLoaded', function () {
                Swal.fire('Success', 'Manage Cylinder successfully!', 'success').then(() => {
                        window.location.href = 'inventory.php';
                    });
                });
            </script>";
            exit;
        } else {
            echo "<script>
                document.addEventListener('DOMContentLoaded', function () {
                Swal.fire('Error', 'Fail to Manage Cylinder !', 'error').then(() => {
                        window.location.href = 'inventory.php';
                    });
                });
            </script>";
            exit;
        }
        exit;
    }

?>
    <div id="itemManager" class="w3-modal" style='color: #546178'>
        <div class="w3-modal-content w3-animate-top border-radius">
            <div>
                <h4 class="w3-display-topleft" style='margin-top:10px;margin-left:20px;font-weight:600;'>Manage Cylinder
                </h4>
                <span onclick="document.getElementById('itemManager').style.display='none'"
                    class="w3-button w3-display-topright"
                    style="border-radius: 20px;margin-top:10px;margin-right:10px">&times;</span>

                <form method="POST" action="" style='margin-top:10px;margin-bottom:-15px'>
                    <input type="hidden" id="editId" name="id">
                    <label for="editPrice"><b>Price </b></label>
                    <input type="number" id="editPrice" name="price" placeholder="Enter Price" required>
                    <label for="editStock"><b>Stock </b></label>
                    <input type="number" id="editStock" name="stock" placeholder="Enter Stock" required>
                    <button type="submit" name="update_item">Manage</button>
                </form>
            </div>
        </div>
    </div>

    <script>
    function openEditModal(id, price, stock) {
        document.getElementById('editId').value = id;
        document.getElementById('editPrice').value = price;
        document.getElementById('editStock').value = stock;
        document.getElementById('itemManager').style.display = 'block';
    }
    </script>
</body>

</html>