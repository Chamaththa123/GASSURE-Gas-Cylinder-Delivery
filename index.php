<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My PHP Project</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <style>
    body,
    html {
        height: 100%;
        margin: 0;
    }

    .hero-image {
        background-image: linear-gradient(rgba(0, 0, 0, 0.3), rgba(0, 0, 0, 0.2)), url("public/assets/images/StockCake-Yoga Class Session_1730543693.jpg");
        height: 90%;
        background-position: center;
        background-repeat: no-repeat;
        background-size: cover;
        position: relative;
    }

    .hero-text {
        text-align: center;
        position: absolute;
        top: 50%;
        left: 50%;
        width: 65%;
        transform: translate(-50%, -50%);
        color: white;
    }

    .hero-text button {
        border: none;
        outline: 0;
        display: inline-block;
        padding: 15px 25px;
        color: white;
        font-size: 18px;
        font-weight: 700;
        background-color: #0074D9;
        text-align: center;
        cursor: pointer;
        width: auto;
        border-radius: 8px;
    }

    .container-home {
        display: grid;
        grid-template-columns: 1fr;
        gap: 20px;
        padding: 20px;
    }

    .column-home {
        padding: 20px;
        text-align: left;
        border-radius: 8px;
    }

    @media (min-width: 600px) {
        .container-home {
            grid-template-columns: 1fr 1fr;
        }
    }
    </style>
</head>

<body>

    <!-- Header Section -->
    <?php include './includes/header.php'; ?>
    <!-- Main Content Section -->
    <!-- <div class="hero-image">
        <div class="hero-text">
            <h1 style="font-size:55px;font-weight:650">Achieve Your Fitness Goals Today!</h1>
            <p style="font-size:20px;">
                Join <i>FitZone Fitness Club</i> and Start Your
                Journey
                Towards a<br />
                Healthier,
                Stronger You!</p>
            <button onclick="window.location.href='membership.php'">Join Us Today</button>

        </div>
    </div>
    <div class="container-home">
        <div class="column-home">
            <img src='public/assets/images/StockCake-Indoor Yoga Practice_1731165152.jpg'
                style='width:80% ;border-radius:15px;height:80%' alt='contact image'>
        </div>
        <div class="column-home">
            <h2 style='font-weight:600'>Discover Your Perfect Class</h2>
            <p>FitZone offers a diverse range of classes tailored to meet all fitness levels. From high-energy cardio
                and strength training to calming yoga and flexibility classes, there’s something for everyone. Dive into
                our expertly designed classes led by certified trainers who will inspire and support you every step of
                the way!</p>
            <button onclick="window.location.href='class.php'" style='width:200px;border-radius:10px'>Explore All
                Classes</button>
        </div>
    </div>
    <div class="container-home">
        <div class="column-home">
            <h2 style='font-weight:600'>Connect with FitZone</h2>
            <p>Have questions about our fitness programs or memberships? Our team is here to help! Whether you’re
                looking for guidance on joining, or need support with ongoing membership, reach out to us anytime. We’re
                passionate about helping you achieve your fitness goals and are just one click away!</p>
            <button onclick="window.location.href='contactus.php'" style='width:200px;border-radius:10px'>Get in
                Touch</button>
        </div>
        <div class="column-home"
            style="float: right; width: 90%; margin-top: 40px; border-radius: 15px; height: 80%; text-align: center;">
            <img src="public/assets/images/StockCake-Group Workout Session_1729583555.jpg"
                style="width: 100%; height: 100%; border-radius: 15px;" alt="contact image">
        </div>


    </div> -->

    <!-- <?php include 'src/includes/footer.php'; ?> -->
</body>

</html>