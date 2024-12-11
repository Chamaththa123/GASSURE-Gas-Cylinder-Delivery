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

// Handle feedback submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : null;
    $feedback_text = isset($_POST['feedback_text']) ? trim($_POST['feedback_text']) : null;
    $rating = isset($_POST['rating']) ? intval($_POST['rating']) : null;

    if ($user_id && $feedback_text && $rating) {
        // Insert feedback into the database
        $query = $conn->prepare("INSERT INTO feedback (user_id, feedback_text, rating, date) VALUES (?, ?, ?, NOW())");
        $query->bind_param("isi", $user_id, $feedback_text, $rating);

        if ($query->execute()) {
            $_SESSION['message'] = "Feedback submitted successfully!";
        } else {
            $_SESSION['error'] = "Error submitting feedback. Please try again.";
        }
    } else {
        $_SESSION['error'] = "All fields are required.";
    }

    // Redirect back to the feedback page
    header('Location: feedback.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feedback</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="public/assets/css/profile.css">
    <style>
    #addFeedback {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        display: none;
        /* justify-content: center; */
        align-items: flex-start;
        padding-top: 10px;
        background-color: rgba(0, 0, 0, 0.4);
        z-index: 1000;
        color: #546178
    }

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

    .stars {
        display: flex;
        justify-content: center;
        gap: 5px;
        direction: rtl;
        /* Ensures left-to-right layout */
    }

    .stars input {
        display: none;
        /* Hide the radio inputs */
    }

    .stars label {
        font-size: 30px;
        color: #ccc;
        /* Default star color */
        cursor: pointer;
        transition: color 0.2s ease;
    }

    /* Highlight stars left-to-right when a star is selected */
    .stars input:checked~label {
        color: #ffd800;
        /* Highlighted color */
    }

    /* Highlight stars left-to-right on hover */
    .stars label:hover,
    .stars label:hover~label {
        color: #ffd800;
        /* Highlighted color */
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
        <div style='font-size: 15px; color: #cd0a0a; font-weight: medium;'>Home / Feedback</div>

        <div style='margin-top:50px'>
            <div class="content-wrapper">
                <div class="right-section">
                    <img src="../images/user.png" style="width: 60%; display: block; margin: 0 auto;" alt="User Image">

                    <?php if ($user): ?>
                    <div>
                        <button onclick="document.getElementById('addFeedback').style.display='block'"
                            name="addFeedback" class="delete-button">Add Your Feedback</button>
                    </div>
                    <?php else: ?>
                    <p>No user details available. Please log in to provide feedback.</p>
                    <?php endif; ?>


                </div>
                <div class="left-section">
                    <h2 style='text-align:left'>
                        Order Details
                    </h2>
                </div>
            </div>
        </div>
    </div>

    <div id="addFeedback" class="w3-modal">
        <div class="w3-modal-content w3-animate-top border-radius">
            <div class="w3-container">
                <span onclick="document.getElementById('addFeedback').style.display='none'"
                    class="w3-button w3-display-topright"
                    style="border-radius: 20px;margin-top:10px;margin-right:10px ">&times;</span>
                <h3 style="font-weight:600">Write a Feedback</h3>
                <form action="" method="POST">
                    <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user['id']); ?>">

                    <label for="feedback_text">Your
                        Feedback:</label><br />
                    <textarea name="feedback_text" id="feedback_text" rows="4"
                        style='border-radius:5px;width:100%'></textarea>

                    <div class="input-group" style='margin-top:10px'>
                        <label for="rating">Rating:</label>
                        <div class="stars">
                            <input type="radio" id="star1" name="rating" value="5"><label for="star1"
                                class="fa fa-star"></label>
                            <input type="radio" id="star2" name="rating" value="4"><label for="star2"
                                class="fa fa-star"></label>
                            <input type="radio" id="star3" name="rating" value="3"><label for="star3"
                                class="fa fa-star"></label>
                            <input type="radio" id="star4" name="rating" value="2"><label for="star4"
                                class="fa fa-star"></label>
                            <input type="radio" id="star5" name="rating" value="1"><label for="star5"
                                class="fa fa-star"></label>
                        </div>
                    </div>
                    <button type="submit" class="btn" style='float:right'>Submit</button>
                </form>
            </div>
        </div>
    </div>

    <script>
    <?php if (isset($_SESSION['message'])): ?>
    Swal.fire({
        icon: 'success',
        title: 'Success',
        text: '<?php echo $_SESSION['message']; ?>',
    });
    <?php unset($_SESSION['message']); ?>
    <?php elseif (isset($_SESSION['error'])): ?>
    Swal.fire({
        icon: 'error',
        title: 'Error',
        text: '<?php echo $_SESSION['error']; ?>',
    });
    <?php unset($_SESSION['error']); ?>
    <?php endif; ?>
    // Validation for the Feedback Form
    document.querySelector('form[action=""]').addEventListener('submit', function(e) {
        const form = e.target;
        const feedbackText = form.querySelector('textarea[name="feedback_text"]').value.trim();
        const rating = form.querySelector('input[name="rating"]:checked'); // Check if any rating is selected

        // Check if feedback text or rating is empty
        if (!feedbackText || !rating) {
            e.preventDefault(); // Prevent form submission
            Swal.fire({
                icon: 'error',
                title: 'Validation Error',
                text: 'Both feedback and rating are required!',
            });
            return false;
        }
    });
    </script>

</body>

</html>