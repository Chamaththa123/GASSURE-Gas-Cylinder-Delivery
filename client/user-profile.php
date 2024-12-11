<?php
// Start the session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database configuration
if (!isset($conn)) {
    require_once '../config/config.php';
}

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
}

// Handle logout functionality
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['logout'])) {
    session_unset();
    session_destroy();
    header('Location: index.php');
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
                    <h2 style='text-align:left'>
                        Order Details
                    </h2>
                </div>
            </div>
        </div>
    </div>

    <!-- <div class="hero-image">
        <div class="hero-text">
            <h1>
                <span style="color:#0074D9;background-color:white;">&nbsp;User&nbsp;</span>
                <span style="color:white;background-color:#0074D9;margin-left:-15px;">&nbsp;Profile&nbsp;</span>
            </h1>
        </div>
        </div>

        <div class="trainer-card">
            <div class="content-wrapper">
                <div class="right-section">
                    <img src="public/assets/images/user.png" alt="User Image">
                    <form method="post">
                        <button type="submit" name="logout" class="delete-button">Logout</button>
                    </form>
                </div>
                <div class="left-section">
                    <?php if ($user): ?>
                    <h2><?php echo htmlspecialchars($user['first_name'] . " " . $user['last_name']); ?></h2>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                    <p><strong>Address:</strong> <?php echo htmlspecialchars($user['address']); ?></p>
                    <?php else: ?>
                    <p>No user details available.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div> -->
</body>

</html>