<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start(); 
}

if (!isset($conn)) {
    require '../config/config.php';  // Database connection
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['login'])) {
        $email = $_POST['email'];
        $password = $_POST['password'];

        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_name'] = $user['first_name'];
                $_SESSION['id'] = $user['id'];
                $_SESSION['role'] = $user['role'];
                $success_message = "Login successful!";
                header('Location: ' . $_SERVER['PHP_SELF']);
                exit();
            } else {
                $error_message = "Invalid password!";
            }
        } else {
            $error_message = "No user found with this email!";
        }
        
    }

if (isset($_POST['register'])) {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $address = $_POST['address'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    $checkEmail = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $checkEmail->bind_param("s", $email);
    $checkEmail->execute();
    $result = $checkEmail->get_result();

    if ($result->num_rows > 0) {
        $error_message = "User already exists!";
    } else {
        $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, address, password) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $first_name, $last_name, $email, $address, $password);

        if ($stmt->execute()) {
            $user_id = $stmt->insert_id;

            $_SESSION['user_email'] = $email;
            $_SESSION['user_name'] = $first_name;
            $_SESSION['id'] = $user_id;
            $_SESSION['role'] = 0; 

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
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<link rel="stylesheet" href="../css/header.css">
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
    max-width: 500px;
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
    padding: 5px;
    width: 80px;
    background-color: #ffd800;
    font-size: 14px;
    color: white;
    border: none;
    cursor: pointer;
    border-radius: 10px;
    margin-top: 10px;
    border-color: #ffd800;
    border: 1px solid #ffd800;
    font-family: "Outfit", sans-serif;
}

.btn:hover {
    background-color: #ffd800;
    border-color: #ffd800;
    border: 1px solid #ffd800;
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

.topnav {
  overflow: hidden;
  background-color: #2a3577;
}

.topnav a {
  float: left;
  display: block;
  color: #f2f2f2;
  text-align: center;
  padding: 14px 16px;
  text-decoration: none;
  font-size: 16px;
}


.topnav .icon {
  display: none;
}

@media screen and (max-width: 600px) {
  .topnav a:not(:first-child) {display: none;}
  .topnav a.icon {
    float: right;
    display: block;
  }
}

.split {
    float: right;
    display: flex;
      justify-content: center;
      align-items: center;
      height: 90px;
    margin-right: 10px;
    font-weight: 500;
    background-color: transparent !important;;
}

@media screen and (max-width: 600px) {
    @media screen and (max-width: 600px) {
  .topnav a {
    display: none; /* Hide all links by default on smaller screens */
  }
  .topnav a.icon {
    float: right;
    display: block; /* Display the hamburger menu icon */
  }
  .topnav.responsive a {
    display: block; /* Show links when the menu is toggled */
    text-align: left; /* Align the links to the left for better appearance */
  }
}

    .split {
        display: block; /* Adjust layout for mobile */
        height: auto;          /* Let height adjust based on content */
        margin-top: 10px;     /* Adjust margin for smaller screens */
        float: left;
        text-align: left;
    }
}

</style>

<body>

<div class="topnav" id="myTopnav">
    <img src="../images/logo.png" class="logo" style="width:90px" alt="Logo">
  
    <a href="javascript:void(0);" class="icon" onclick="myFunction()">
        <i class="fa fa-bars"></i>
    </a>
    <div class="split">
        <a href="./../index.php">Home</a>
        <a href="./order.php">Order Now</a>
        <a href="./feedback.php">Feedback</a>
        <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 1): ?>
        <a href="./inventory.php">Admin</a>
        <?php endif; ?>

        <?php if (isset($_SESSION['user_name'])): ?>
        <a class="user-email" style="margin-left:20px">
            <?php echo htmlspecialchars($_SESSION['user_name']); ?>
        </a>
        <a style="padding:0px" href="./user-profile.php">
            <img src="../images/user.png" class="logo" style="width:40px" alt="Logo">
        </a>
        <a style="padding-left:10px" href="./order-history.php">
            <img src="../images/order-header.png" class="logo" style="width:40px" alt="Logo">
        </a>
        <?php else: ?>
        <button class="btn" onclick="document.getElementById('login').style.display='block'">SIGN IN</button>&nbsp;&nbsp;&nbsp;
        <button class="btn" onclick="document.getElementById('register').style.display='block'">SIGN UP</button>
        <?php endif; ?>
    </div>
</div>


    <!-- Display error message, if any -->
    <?php if (isset($error_message)): ?>
    <script>
    document.addEventListener("DOMContentLoaded", function() {
        Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: "<?php echo addslashes($error_message); ?>",
        });
    });
    </script>
    <?php endif; ?>

    <?php if (isset($success_message)): ?>
    <script>
    document.addEventListener("DOMContentLoaded", function() {
        Swal.fire({
            icon: 'success',
            title: 'Success!',
            text: "<?php echo addslashes($success_message); ?>",
        });
    });
    </script>
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
                    <label for="email"><b>Email *</b></label>
                    <input type="text" placeholder="Enter Email" name="email">

                    <label for="password"><b>Password *</b></label>
                    <input type="password" placeholder="Enter Password" name="password">
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
            <div class="w3-container ">
                <span onclick="document.getElementById('register').style.display='none'"
                    class="w3-button w3-display-topright"
                    style="border-radius: 20px;margin-top:10px;margin-right:10px">&times;</span>
                <h3>Sign Up</h3>
                <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>"
                    style='margin-top:20px;margin-bottom:20px;'>
                    <label for="fname"><b>First Name *</b></label>
                    <input type="text" placeholder="Enter First Name" name="first_name">

                    <label for="lname"><b>Last Name *</b></label>
                    <input type="text" placeholder="Enter Last Name" name="last_name">

                    <label for="lname"><b>Email *</b></label>
                    <input type="text" placeholder="Enter Email" name="email">

                    <label for="address"><b>Address *</b></label>
                    <input type="text" placeholder="Enter Address" name="address">

                    <label for="password"><b>Password *</b></label>
                    <input type="password" placeholder="Enter Password" name="password">

                    <button type="submit" name="register">Register</button>
                </form>
            </div>
        </div>
    </div>

    <script>
    // Validation for Sign In Form
    document.querySelector('form[action="<?php echo $_SERVER["PHP_SELF"]; ?>"][method="post"]').addEventListener(
        'submit',
        function(e) {
            const form = e.target;
            const email = form.querySelector('input[name="email"]').value.trim();
            const password = form.querySelector('input[name="password"]').value.trim();

            if (email === "" || password === "") {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    text: 'Please fill in all required fields!',
                });
                return false;
            }
        });

    // Validation for Sign Up Form
    document.querySelectorAll('form[action="<?php echo $_SERVER["PHP_SELF"]; ?>"][method="post"]').forEach(function(
        form) {
        form.addEventListener('submit', function(e) {
            const firstName = form.querySelector('input[name="first_name"]').value.trim();
            const lastName = form.querySelector('input[name="last_name"]').value.trim();
            const email = form.querySelector('input[name="email"]').value.trim();
            const address = form.querySelector('input[name="address"]').value.trim();
            const password = form.querySelector('input[name="password"]').value.trim();

            if (firstName === "" || lastName === "" || email === "" || address === "" || password ===
                "") {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    text: 'Please fill in all required fields!',
                });
                return false;
            }
        });
    });

    function myFunction() {
    var nav = document.getElementById("myTopnav");
    if (nav.className === "topnav") {
        nav.className += " responsive";
    } else {
        nav.className = "topnav";
    }
}

    </script>

</body>

</html>