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
        color: #546178
    }

    .hero-image {
        background-image: linear-gradient(rgba(0, 0, 0, 0.3), rgba(0, 0, 0, 0.2)), url("./images/index3.jpg");
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

    .header1 {
        font-size: 50px;
        font-weight: 600;
        text-align: center;
        margin-top: 40px;
    }

    .no {
        padding: 15px;
        background-color: #ffd800;
        border-radius: 50%;
        color: white;
        font-weight: bold;
    }

    .topic {
        margin-top: 20px;
        font-weight: 600;
        font-size: 25px
    }

    .des {
        margin-top: 20px;
        padding: 20px
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
    <div class="hero-image">
        <div class="hero-text">
            <h1 style="font-size:55px;font-weight:650">YOU CLICK. WE'LL DELIVER SUPER QUICK !</h1>
            <p style="font-size:20px;">
                Experience the convenience of ordering gas cylinders anytime, anywhere.
                Safe, reliable, and hassle-free delivery right to your doorstep!</p>
            <button onclick="window.location.href='./client/order.php'">Order Now</button>

        </div>
    </div>
    <div class='header1'> Home Delivery </div>
    <div class="container-home">

        <div class="column-home">
            <img src='./images/LITRO.jpg' style='width:90% ;border-radius:15px;height:90%' alt='contact image'>
        </div>
        <div class="column-home">
            <p style='font-size:16px;margin-top:30px'>Our Gas Delivery App is designed to bring unparalleled convenience
                to your life, ensuring your gas cylinder is just a tap away. With our user-friendly app, you can place
                orders anytime and have your cylinder delivered directly to your doorstep quickly and reliably.<br />

                We prioritize your safety and satisfaction, offering seamless tracking, secure payments, and prompt
                customer support to enhance your experience. For any inquiries or assistance, connect with us through
                our dedicated helpline at 011 - 34 56 768.<br />

                Enjoy the comfort of hassle-free gas delivery and let us make your daily routine simpler and more
                efficient</p>

        </div>
    </div>
    <div class='header1'> How It Works </div>
    <div class="container-home">
        <div class="column-home"
            style="float: right; width: 90%; margin-top: 40px; border-radius: 15px; height: 80%; text-align: center;">
            <div><span class='no'>01</span><img src='./images/online-order.png' style='width:80px;margin-left:20px'
                    alt='contact image'></div>
            <div class='topic'>Place Your Order</div>
            <div class='des'>Visit our website, select your preferred gas cylinder type, and place your order in just a
                few clicks.</div>
        </div>
        <div class="column-home"
            style="float: right; width: 90%; margin-top: 40px; border-radius: 15px; height: 80%; text-align: center;">
            <div><span class='no'>02</span><img src='./images/cashless-payment.png' style='width:80px;margin-left:20px'
                    alt='contact image'></div>
            <div class='topic'>Secure Payment</div>
            <div class='des'>Complete your order with secure online payment options. Pay effortlessly using multiple
                payment methods, ensuring a smooth and convenient checkout experience.</div>
        </div>
        <div class="column-home"
            style="float: right; width: 90%; margin-top: 40px; border-radius: 15px; height: 80%; text-align: center;">
            <div><span class='no'>03</span><img src='./images/doorstep-delivery.png' style='width:80px;margin-left:20px'
                    alt='contact image'></div>
            <div class='topic'>Doorstep Delivery</div>
            <div class='des'>Sit back and relax! Our team will deliver your gas cylinder safely and promptly to your
                doorstep at your chosen time.</div>
        </div>
        <div class="column-home"
            style="float: right; width: 90%; margin-top: 40px; border-radius: 15px; height: 80%; text-align: center;">
            <div><span class='no'>04</span><img src='./images/tracking.png' style='width:80px;margin-left:20px'
                    alt='contact image'></div>
            <div class='topic'>Stay Connected</div>
            <div class='des'>Track your order status in real-time through our app, and for any inquiries, our customer
                support is just a call away!</div>
        </div>


    </div>
    <?php include './includes/footer.php'; ?>
</body>

</html>