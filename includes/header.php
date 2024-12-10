<?php
// Only start a session if one doesn't already exist
if (session_status() === PHP_SESSION_NONE) {
    session_start();  // Start the session to access session variables
}

// Check if the database connection is not already established to avoid re-requiring in other files
if (!isset($conn)) {
    require './config/config.php';  // Database connection
}

// Handle user login
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['login'])) {
        $email = $_POST['email'];
        $password = $_POST['password'];

        // Fetch user from the database
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();

            // Verify password
            if (password_verify($password, $user['password'])) {
                // Set session variables
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_name'] = $user['first_name'];
                $_SESSION['id'] = $user['id'];
                $_SESSION['role'] = $user['role'];

                // Redirect to the same page to refresh and hide login/signup buttons
                header('Location: ' . $_SERVER['PHP_SELF']);
                exit();
            } else {
                $error_message = "Invalid password!";
            }
        } else {
            $error_message = "No user found with this email!";
        }
    }

    // Handle user registration
// Handle user registration
if (isset($_POST['register'])) {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $address = $_POST['address'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);  // Hash the password

    // Check if the user already exists
    $checkEmail = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $checkEmail->bind_param("s", $email);
    $checkEmail->execute();
    $result = $checkEmail->get_result();

    if ($result->num_rows > 0) {
        $error_message = "User already exists!";
    } else {
        // Insert new user
        $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, address, password) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $first_name, $last_name, $email, $address, $password);

        if ($stmt->execute()) {
            // Retrieve the newly created user ID
            $user_id = $stmt->insert_id;

            // Set session variables
            $_SESSION['user_email'] = $email;
            $_SESSION['user_name'] = $first_name;
            $_SESSION['id'] = $user_id;
            $_SESSION['role'] = 0;  // Set default role or fetch from the database if needed

            // Redirect to the same page after successful registration
            header('Location: ' . $_SERVER['PHP_SELF']);
            exit();
        } else {
            $error_message = "Error during registration: " . $stmt->error;
        }
    }
}

}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['logout'])) {
    session_unset();
    session_destroy();
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit();
}
?>


<!DOCTYPE html>
<html>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<link rel="stylesheet" href="public/assets/css/header.css">
<style>
.custom-container {
    background-color: white;
    padding: 5px 20px;
    border-radius: 10px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    margin-top: 20px;
    padding-bottom: 20px;
}

#login {
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

#register {
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

.border-radius {
    border-radius: 10px;
    max-width: 800px;
    margin: auto;
}

.input-group {
    display: flex;
    gap: 10px;
    margin-bottom: 15px;
}

.input-item {
    flex: 1;
}


.closebtn {
    margin-left: 15px;
    color: white;
    font-weight: bold;
    float: right;
    font-size: 22px;
    line-height: 20px;
    cursor: pointer;
    transition: 0.3s;
}

.closebtn:hover {
    color: black;
}

.btn {
    padding: 3px;
    width: 80px;
    background-color: #0074D9;
    font-size: 12px;
    color: white;
    border: none;
    cursor: pointer;
    border-radius: 10px;
    margin-top: 10px;
    border-color: #0074D9;
    border: 1px solid #0074D9;
    font-family: 'Poppins', Arial, Helvetica, sans-serif;
}

.alert {
    padding: 20px;
    background-color: #f44336;
    color: white;
}

.closebtn {
    margin-left: 15px;
    color: white;
    font-weight: bold;
    float: right;
    font-size: 22px;
    line-height: 20px;
    cursor: pointer;
    transition: 0.3s;
}

.closebtn:hover {
    color: black;
}
</style>

<body>
    <div class="topnav">
        <img src="public/assets/images/logo.jpg" class='logo' style='width:80px' alt="Logo">
        <div class="split">
            <a href="./../index.php">Home</a>
            <a href="./client/abc.php">Membership</a>
            <a href="trainer.php">Our Trainers</a>
            <a href="class.php">Classes</a>
            <a href="appointment.php">Appointments</a>
            <a href="contactus.php">Contact Us</a>
            <a href="about.php">About Us</a>
            <a href="blog.php">Blog</a>
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 1): ?>
            <a href="src/admin/membership-admin.php">Admin</a>
            <?php endif; ?>


            <?php if (isset($_SESSION['user_name'])): ?>
            <a class="user-email" style='margin-left:20px'>
                <?php echo htmlspecialchars($_SESSION['user_name']); ?>
            </a>

            <a style="padding:0px" href='user-profile.php'><img src="public/assets/images/user.png" class='logo'
                    style='width:40px' alt="Logo"></a>

            <?php else: ?>
            <button class="btn" onclick="document.getElementById('login').style.display='block'">SIGN
                IN</button>&nbsp;&nbsp;&nbsp;
            <button class="btn" onclick="document.getElementById('register').style.display='block'">SIGN UP</button>
            <?php endif; ?>
        </div>
    </div>

    <!-- Display error message, if any -->
    <?php if (isset($error_message)): ?>
    <div class="alert" style="background-color: #f44336;">
        <span class="closebtn" onclick="this.parentElement.style.display='none';">&times;</span>
        <div class="error-message"><?php echo htmlspecialchars($error_message); ?></div>
    </div>

    <?php endif; ?>






    <div id="login" class="w3-modal">
        <div class="w3-modal-content w3-animate-top border-radius">
            <div class="w3-container">
                <span onclick="document.getElementById('login').style.display='none'"
                    class="w3-button w3-display-topright"
                    style="border-radius: 20px;margin-top:10px;margin-right:10px">&times;</span>
                <h3>Sign In</h3>
                <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>"
                    style='margin-top:20px;margin-bottom:20px;'>
                    <label for="email"><b>Email</b></label>
                    <input type="text" placeholder="Enter Email" name="email" required>

                    <label for="password"><b>Password</b></label>
                    <input type="password" placeholder="Enter Password" name="password" required>
                    <label>
                        <input type="checkbox" checked="checked" name="remember"> Remember me
                    </label>
                    <button type="submit" name="login">Sign In</button>
                </form>
            </div>
        </div>
    </div>

    <div id="register" class="w3-modal">
        <div class="w3-modal-content w3-animate-top border-radius">
            <div class="w3-container">
                <span onclick="document.getElementById('register').style.display='none'"
                    class="w3-button w3-display-topright"
                    style="border-radius: 20px;margin-top:10px;margin-right:10px">&times;</span>
                <h3>Sign Up</h3>
                <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>"
                    style='margin-top:20px;margin-bottom:20px;'>
                    <label for="fname"><b>First Name</b></label>
                    <input type="text" placeholder="Enter First Name" name="first_name" required>

                    <label for="lname"><b>Last Name</b></label>
                    <input type="text" placeholder="Enter Last Name" name="last_name" required>

                    <label for="lname"><b>Email</b></label>
                    <input type="text" placeholder="Enter Email" name="email" required>

                    <label for="address"><b>Address</b></label>
                    <input type="text" placeholder="Enter Address" name="address" required>

                    <label for="password"><b>Password</b></label>
                    <input type="password" placeholder="Enter Password" name="password" required>

                    <button type="submit" name="register">Register</button>
                </form>
            </div>
        </div>
    </div>

    <script>
    var loginModal = document.getElementById('login');
    var registerModal = document.getElementById('register');

    window.onclick = function(event) {
        if (event.target == loginModal) {
            loginModal.style.display = "none";
        }
        if (event.target == registerModal) {
            registerModal.style.display = "none";
        }
    }
    </script>
</body>

</html>