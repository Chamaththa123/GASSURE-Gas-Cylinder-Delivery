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

if (isset($_SESSION['user_email'])) {
    $user_email = $_SESSION['user_email'];

    // Prepare and execute the query to fetch user details
    $user_query = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $user_query->bind_param("s", $user_email);
    $user_query->execute();
    $user_result = $user_query->get_result();
    $user = $user_result->fetch_assoc();
}

// Handle status change requests
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['change_status'])) {
    $user_id = $_POST['id'];
    $new_status = $_POST['status'] == 1 ? 0 : 1;

    $status_query = $conn->prepare("UPDATE users SET status = ? WHERE id = ?");
    $status_query->bind_param("ii", $new_status, $user_id);

    if ($status_query->execute()) {
        $_SESSION['message'] = "User status updated successfully!";
    } else {
        $_SESSION['error_message'] = "Failed to update user status: " . $status_query->error;
    }

    header('Location: user-admin.php');
    exit;
}

// Fetch all users with role = 0 (customers)
$user_query = $conn->prepare("SELECT id, first_name, last_name, email, address, status FROM users WHERE role = 0 ORDER BY id DESC");
if ($user_query) {
    $user_query->execute();
    $user_result = $user_query->get_result();
    if ($user_result === false) {
        die("Query execution failed: " . $conn->error);
    }
} else {
    die("Failed to prepare statement: " . $conn->error);
}
?>

<!DOCTYPE html>
<html>

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="../css/sidebar.css">
    <link rel="stylesheet" href="../css/inventory.css">
    <link rel="stylesheet" href="../css/table.css">
    <style>
        .table-container {
            margin: 20px;
        }
    </style>
</head>

<body>

    <div class="sidebar">
        <div class="logo-container">
            <img src="../images/logo.png" class="logo" style="width:100px" alt="Logo">
        </div>
        <br />
        <a href="./inventory.php">Inventory</a>
        <a href="./admin-orders.php">Orders</a>
        <a class="active" href="./user-admin.php">Customers</a>
        <a href="../index.php">Back</a>
    </div>

    <div style='color: #546178'>
    <div class='header'>
            <div style='font-size:24px;font-weight:600'>Customers</div>
            <div><a href='./notification.php'><i class="fa fa-bell"
                        style="font-size:24px;margin-right:20px;color:#546178"></i></a></div>
        </div>
        <div class="content">
            <div class="content-one">
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>User ID</th>
                                <th>First Name</th>
                                <th>Last Name</th>
                                <th>Email</th>
                                <th>Address</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if ($user_result->num_rows > 0) {
                                while ($user = $user_result->fetch_assoc()) {
                                    echo "<tr>";
                                    echo "<td data-label='User ID'>" . htmlspecialchars($user['id']) . "</td>";
                                    echo "<td data-label='First Name'>" . htmlspecialchars($user['first_name']) . "</td>";
                                    echo "<td data-label='Last Name'>" . htmlspecialchars($user['last_name']) . "</td>";
                                    echo "<td data-label='Email'>" . htmlspecialchars($user['email']) . "</td>";
                                    echo "<td data-label='Address'>" . htmlspecialchars($user['address']) . "</td>";
                                    echo "<td data-label='Status'>" . ($user['status'] == 1 ? 'Active' : 'Inactive') . "</td>";
                                    echo "<td data-label='Action'>";
                                    echo "<form method='POST' action='' style='display:inline;'>";
                                    echo "<input type='hidden' name='id' value='" . $user['id'] . "'>";
                                    echo "<input type='hidden' name='status' value='" . $user['status'] . "'>";
                                    if ($user['status'] == 1) {
                                        echo "<button type='submit' name='change_status' class='reply-button' style='background-color:#ffc107'>Deactivate</button>";
                                    } else {
                                        echo "<button type='submit' name='change_status' class='delete-button' style='background-color:#0d6efd'>Activate</button>";
                                    }
                                    echo "</form>";
                                    echo "</td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='7'>No customers found.</td></tr>";
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
