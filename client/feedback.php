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

$query = "
    SELECT f.id AS feedback_id, f.feedback_text, f.rating, f.date, u.first_name ,u.last_name
    FROM feedback f
    JOIN users u ON f.user_id = u.id
    ORDER BY f.date DESC
";

$statsQuery = "
    SELECT 
        COUNT(*) AS total_reviews,
        AVG(rating) AS average_rating,
        SUM(rating = 1) AS one_star,
        SUM(rating = 2) AS two_star,
        SUM(rating = 3) AS three_star,
        SUM(rating = 4) AS four_star,
        SUM(rating = 5) AS five_star
    FROM feedback
";
$statsResult = $conn->query($statsQuery);
$stats = $statsResult->fetch_assoc();
$result = $conn->query($query);

$total_reviews = $stats['total_reviews'];

$five_star_percentage = ($total_reviews > 0) ? ($stats['five_star'] / $total_reviews) * 100 : 0;
$four_star_percentage = ($total_reviews > 0) ? ($stats['four_star'] / $total_reviews) * 100 : 0;
$three_star_percentage = ($total_reviews > 0) ? ($stats['three_star'] / $total_reviews) * 100 : 0;
$two_star_percentage = ($total_reviews > 0) ? ($stats['two_star'] / $total_reviews) * 100 : 0;
$one_star_percentage = ($total_reviews > 0) ? ($stats['one_star'] / $total_reviews) * 100 : 0;

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

    .add-review-button {
        background-color: #2a3577;
        color: white;
        border: none;
        border-radius: 5px;
        padding: 8px 10px;
        cursor: pointer;
        width: 130px;
        transition: background-color 0.3s ease;
        margin-top: 20px;
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


    .container {
        width: 100%;
        margin: 0 auto;
        padding: 20px;
        color: #546178
    }

    .feedback-list {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
        /* Add some space between items */
    }

    .feedback-item {
        /* display: flex;
        flex-wrap: wrap; */
        width: 49%;
        /* Two items per row with space between */
        border: 1px solid #ddd;
        padding: 10px;
        border-radius: 8px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    }

    .feedback-header {
        display: flex;
        flex-wrap: wrap;
    }

    .feedback-item .feedback-content {
        flex: 1;
    }

    .feedback-item .user-info {
        text-align: right;
        /* margin-top: 10px; */
    }

    .feedback-item .rating {
        color: #ffd800;
        font-size: 18px;
    }

    .feedback-item .date {
        color: #666;
        font-size: 12px;
    }

    .feedback-item .feedback-text {
        font-size: 16px;
        color: #333;
    }

    .feedback-item .user-name {
        font-weight: bold;
    }

    .statistics {}

    .statistics ul {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .statistics li {
        font-size: 16px;
        margin: 5px 0;
    }

    .average-stars {
        display: flex;
        align-items: center;
        /* Vertically aligns the items */
        justify-content: center;
        /* Horizontally centers the items */
        gap: 5px;
        /* Space between stars */
        margin: 5px auto;
        /* Adds space above and below, and centers the container */
        width: fit-content;
        /* Ensures the container fits the content */
    }


    .star {
        font-size: 30px;
        margin-right: 2px;
        color: #ccc;
        /* Default empty star color */
    }

    .star.full {
        color: #ffd800;
        /* Full star color */
    }

    .star.half {
        background: linear-gradient(to right, #ffd800 50%, #ccc 50%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .average-value {
        margin-left: 10px;
        font-size: 18px;
        color: #555;
    }

    .review-item {
        display: flex;
        align-items: center;
        margin-bottom: 10px;
    }

    .review-text {
        flex: 1;
        font-size: 16px;
        margin-right: 10px;
    }

    .progress-container {
        flex: 4;
        position: relative;
        height: 13px;
        background-color: #f1f1f1;
        border-radius: 15px;
        overflow: hidden;
    }

    .progress-bar {
        height: 100%;
        background-color: #ffd800;
        text-align: right;
        color: white;
        padding-right: 5px;
        line-height: 24px;
        border-radius: 15px 0 0 15px;
    }

    .percentage {
        margin-left: 10px;
        font-size: 14px;
    }

    .main-margin {
        margin: 40px
    }

    /* Stack sections vertically on smaller screens */
    @media (max-width: 768px) {

        .left-section,
        .right-section {
            flex-basis: 100%;
            /* Take full width */
        }

        .feedback-item {
            width: 100%;
            /* Stack feedback items on top of each other */
        }

        .main-margin {
            margin: 20px
        }

        .progress-container {
            flex: 2;
            position: relative;
            height: 13px;
            background-color: #f1f1f1;
            border-radius: 15px;
            overflow: hidden;
        }
    }
    </style>
</head>

<body>
    <?php include './header.php'; ?>
    <div class='main-margin'>
        <div style='font-size: 15px; color: #cd0a0a; font-weight: medium;'>Home / Customer Reviews</div>

        <div style='margin-top:50px'>
            <div style='text-align:center;font-size:35px ;font-weight:700;margin-bottom:20px;color: #546178'>Customer
                Reviews</div>
            <div class="content-wrapper">
                <div class="right-section">
                    <div style="text-align:center;"><span style='font-size:25px'><?php 
        $average_rating = $stats['average_rating'] ?? 0; // Default to 0 if null
        echo number_format($average_rating, 1); 
        ?>/</span><span>5</span>
                    </div>
                    <div class="average-stars">
                        <center><?php
                        $average = $stats['average_rating'] ?? 0; // Default to 0 if null
               if ($average <= 0) {
                // Display all empty stars if average is null or 0
                for ($i = 1; $i <= 5; $i++) {
                    echo '<span class="star empty">★</span>';
                }
            } else {
                // Display stars based on the average rating
                for ($i = 1; $i <= 5; $i++) {
                    if ($i <= floor($average)) {
                        echo '<span class="star full">★</span>'; // Full star
                    } elseif ($i - $average < 1) {
                        echo '<span class="star half">★</span>'; // Half star
                    } else {
                        echo '<span class="star empty">★</span>'; // Empty star
                    }
                }
            }
                ?></center>
                    </div>
                    <div style="text-align:center;font-size:16px"><?php echo $stats['total_reviews']?? 0; ?> Reviews
                    </div>
                    <?php if ($user): ?>
                    <div>
                        <button onclick="document.getElementById('addFeedback').style.display='block'"
                            name="addFeedback" class="add-review-button">Write a Review</button>
                    </div>
                    <?php else: ?>
                    <p style='color:red'>Please log in to provide feedback.</p>
                    <?php endif; ?>


                </div>
                <div class="left-section">
                    <div class="statistics">
                        <ul>
                            <li class="review-item">
                                <span class="review-text"><strong>5-Star :</strong>
                                    <?php echo $stats['five_star'] ?? 0; ?></span>
                                <div class="progress-container">
                                    <div class="progress-bar" style="width: <?php echo $five_star_percentage; ?>%;">
                                    </div>
                                </div>
                            </li>
                            <li class="review-item">
                                <span class="review-text"><strong>4-Star :</strong>
                                    <?php echo $stats['four_star']?? 0; ?></span>
                                <div class="progress-container">
                                    <div class="progress-bar" style="width: <?php echo $four_star_percentage; ?>%;">
                                    </div>
                                </div>
                            </li>
                            <li class="review-item">
                                <span class="review-text"><strong>3-Star :</strong>
                                    <?php echo $stats['three_star']?? 0; ?></span>
                                <div class="progress-container">
                                    <div class="progress-bar" style="width: <?php echo $three_star_percentage; ?>%;">
                                    </div>
                                </div>
                            </li>
                            <li class="review-item">
                                <span class="review-text"><strong>2-Star :</strong>
                                    <?php echo $stats['two_star']?? 0; ?></span>
                                <div class="progress-container">
                                    <div class="progress-bar" style="width: <?php echo $two_star_percentage; ?>%;">
                                    </div>
                                </div>
                            </li>
                            <li class="review-item">
                                <span class="review-text"><strong>1-Star :</strong>
                                    <?php echo $stats['one_star']?? 0; ?></span>
                                <div class="progress-container">
                                    <div class="progress-bar" style="width: <?php echo $one_star_percentage; ?>%;">
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="container">
            <!-- <div style='font-size:20px;font-weight:700;margin-bottom:20px'>Customer Reviews</div> -->

            <?php if ($result->num_rows > 0): ?>
            <div class="feedback-list">
                <?php while ($row = $result->fetch_assoc()): ?>
                <div class="feedback-item">
                    <div class='feedback-header'>
                        <div class="feedback-content">
                            <div class="feedback-text">
                                <img src="../images/user-review.png" style="width: 10%; margin: 0 auto;"
                                    alt="User Image"><?php echo htmlspecialchars($row['first_name']); ?>&nbsp;<?php echo htmlspecialchars($row['last_name']); ?>
                            </div>

                        </div>
                        <div class="user-info">
                            <div class="rating"><?php echo str_repeat('<label for="star1"
                                class="fa fa-star"></label>', $row['rating']); ?></div>
                            <div class="date"><?php echo date('Y-m-d H:i:s', strtotime($row['date'])); ?></div>
                        </div>
                    </div>
                    <div style='margin-top:8px;font-size:14px'><?php echo htmlspecialchars($row['feedback_text']); ?>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
            <?php else: ?>
            <p>No feedback available.</p>
            <?php endif; ?>
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
    <?php include './footer.php'; ?>
</body>

</html>