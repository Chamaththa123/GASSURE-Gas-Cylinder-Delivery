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
      "SELECT * FROM notification" // Make sure the SQL query is correct
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
        background-color:rgb(255, 255, 255);
        color: #2a3577;
        margin:10px;
        border-radius: 10px;
    }

    .sidebar a:hover:not(.active) {
        background-color:rgb(170, 170, 173);
        color: white;
        margin:10px;
        border-radius: 10px;
    }

    div.content {
        margin-left: 160px;
        padding-left: 58px;
        height: 1000px;
    }

    .header {
        background-color: white;
        display: flex;
        justify-content: space-between;
        padding: 20px;
        padding-left: 16%;
        position: fixed;
        /* Makes the header fixed */
        width: 84%;
        /* Ensures the header spans the full width of the viewport */
        top: 0;
        /* Fixes the header to the top of the viewport */
        z-index: 900;
        /* Ensures the header stays above other elements */
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
    }
    </style>
</head>

<body>

    <div class="sidebar">
    <div class="logo-container">
    <img src="../images/logo.png" class="logo" style="width:100px" alt="Logo">
</div>
<br />
        <a class="active" href="#home">Home</a>
        <a href="#news">News</a>
        <a href="#contact">Contact</a>
        <a href="#about">About</a>
    </div>

    <div style='color: #546178'>
        <div class='header'>
            <div style='font-size:24px;font-weight:600'>Notification</div>
            <div><a href='./notification.php'><i class="fa fa-bell"
                        style="font-size:24px;margin-right:20px;color:#546178"></i></a></div>
        </div>
        <div class="content">
            <h2>Notification</h2>
            <div style='background-color:white;border-radius:10px'>
                <div class='order-container'>
                    <!-- <hr style="height: 1px; background-color: #b0b0b1; border: none;" /> -->

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
                </div>
            </div>
        </div>
    </div>

</body>

</html>