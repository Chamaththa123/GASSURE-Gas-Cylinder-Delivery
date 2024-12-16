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
    <style>
    body {
        margin: 0;
        font-family: "Outfit", sans-serif;
    }

    .sidebar {
        margin: 0;
        padding: 0;
        width: 200px;
        background-color: #2a3577;
        position: fixed;
        height: 100%;
        overflow: auto;
        border-top-right-radius: 30px;
        border-bottom-right-radius: 30px;
        z-index: 1000;
    }

    .sidebar a {
        display: block;
        color: white;
        padding: 16px;
        text-decoration: none;
    }

    .sidebar a.active {
        background-color: rgb(255, 255, 255);
        color: #2a3577;
        margin: 10px;
        border-radius: 10px;
    }

    .sidebar a:hover:not(.active) {
        background-color: rgb(170, 170, 173);
        color: white;
        margin: 10px;
        border-radius: 10px;
    }

    div.content {
        margin-left: 160px;
        padding-left: 58px;
        height: 80vh;
    }

    .header {
        background-color: white;
        display: flex;
        justify-content: space-between;
        padding: 20px;
        padding-left: 16%;
        position: fixed;
        width: 100%;
        top: 0;
        z-index: 900;
    }

    .order-container {
        width: 100%;
    }

    .logo-container {
        display: flex;
        justify-content: center;
        align-items: center;
        width: 100%;
        text-align: center;
    }

    .items-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 30px;
        padding: 10px;
    }

    .item {
        background-color: #f8f9fa;
        border: 1px solid #ddd;
        border-radius: 10px;
        padding: 15px;
        text-align: center;
        box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.1);
    }

    .item h3 {
        font-size: 18px;
        margin-bottom: 10px;
    }

    .item p {
        font-size: 14px;
        margin-bottom: 8px;
    }

    .item .btn {
        background-color: #2a3577;
        color: white;
        padding: 10px 15px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        font-family: "Outfit", sans-serif;
        margin-top: 20px
    }

    .item .btn:hover {
        background-color: #1a2554;
    }

    #itemManager {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        display: none;
        justify-content: center;
        align-items: flex-start;
        padding-top: 10px;
        background-color: rgba(0, 0, 0, 0.4);
        z-index: 1000;
    }

    input[type=text],
    input[type=number] {
        width: 100%;
        padding: 6px 10px;
        margin: 8px 0;
        display: inline-block;
        border: 1px solid #ccc;
        box-sizing: border-box;
        border-radius: 5px;
    }

    button {
        background-color: #2a3577;
        color: white;
        padding: 7px 10px;
        margin: 8px 0;
        border: none;
        cursor: pointer;
        width: 100%;
        border-radius: 5px;
    }

    button:hover {
        opacity: 0.8;
    }

    .border-radius {
        border-radius: 10px;
        max-width: 500px;
        margin: auto;
    }

    input[type=text],
    input[type=password] {
        width: 100%;
        padding: 6px 10px;
        margin: 8px 0;
        display: inline-block;
        border: 1px solid #ccc;
        box-sizing: border-box;
        border-radius: 5px;
    }

    .edit-button {
        background-color: #2a3577;
        color: white;
        padding: 7px 10px;
        margin: 8px 0;
        border: none;
        cursor: pointer;
        width: 50%;
        border-radius: 5px;
    }

    button:hover {
        opacity: 0.8;
    }

    .input-group {
        display: flex;
        gap: 10px;
        margin-bottom: 15px;
    }

    .input-item {
        flex: 1;
    }

    @media screen and (max-width: 700px) {
        .sidebar {
            width: 100%;
            height: auto;
            position: relative;
            border-top-right-radius: 0px;
            border-bottom-right-radius: 0px;
        }

        .sidebar a {
            float: left;
        }

        div.content {
            margin-left: 0;
        }

        .items-grid {
            grid-template-columns: repeat(1, 1fr);
        }
    }

    @media screen and (max-width: 400px) {
        .sidebar a {
            text-align: center;
            float: none;
        }

        .order-container {
            width: 100%;
            padding-left: 0px;
            padding-right: 0px
        }

        .items-grid {
            grid-template-columns: 1fr;
        }
    }
    </style>
</head>

<body>

    <div class="sidebar">
        <div class="logo-container">
            <img src="../images/logo.png" class="logo" style="width:100px" alt="Logo">
        </div>
        <br />
        <a class="active" href="./inventory.php">Inventory</a>
        <a href="./admin-orders.php">Orders</a>
        <a href="#contact">Contact</a>
        <a href="#about">About</a>
    </div>

    <div style='color: #546178'>
        <div class='header'>
            <div style='font-size:24px;font-weight:600'>Inventory</div>
            <div><a href='./notification.php'><i class="fa fa-bell"
                        style="font-size:24px;margin-right:20px;color:#546178"></i></a></div>
        </div>
        <div class="content">
            <div style="background-color: white; border-radius: 10px; padding: 20px;margin-top:80px">
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
    <div id="itemManager" class="w3-modal">
        <div class="w3-modal-content w3-animate-top border-radius">
            <div class="w3-container">
                <span onclick="document.getElementById('itemManager').style.display='none'"
                    class="w3-button w3-display-topright"
                    style="border-radius: 20px;margin-top:10px;margin-right:10px">&times;</span>
                <h3>Manage Cylinder</h3>
                <form method="POST" action="">
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