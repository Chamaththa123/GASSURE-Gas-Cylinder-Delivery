<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <style>
    .container-contact-f {
        display: flex;
        justify-content: space-between;
        flex-wrap: wrap;
        font-size: 13px;
        background-color: #2a3577;
        color: white;
        margin-top: 20px;
    }

    .column-contact-f-1 {
        flex: 1;
        padding: 20px;
        box-sizing: border-box;
    }

    .column-contact-f-2 {
        flex: 1;
        padding: 20px;
        box-sizing: border-box;
        text-align: right;
    }

    .nav {
        display: inline-block;
        padding: 0px 16px;
        text-decoration: none;
        color: white;
    }

    .fa {
        padding: 12px;
        font-size: 18px;
        width: 40px;
        height: 40px;
        text-align: center;
        text-decoration: none;
        margin: 5px 2px;
        border-radius: 50%;
    }

    .fa:hover {
        opacity: 0.7;
    }

    .fa-facebook {
        background: #3B5998;
        color: white;
    }

    .fa-twitter {
        background: #55ACEE;
        color: white;
    }

    .fa-google {
        background: #dd4b39;
        color: white;
    }

    .fa-linkedin {
        background: #007bb5;
        color: white;
    }

    @media (max-width: 768px) {
        .container-contact-f {
            flex-direction: column;
        }

        .column-contact-f-1,
        .column-contact-f-2 {
            width: 100%;
            text-align: center;

        }

        .nav {
            display: block;
            padding: 10px 0;

        }
    }
    </style>
</head>

<body>
    <div class="container-contact-f">
        <div class="column-contact-f-1 column-1">
            <p style='font-size:18px;font-weight:600'>GASSURE (PVT) LTD</p>
            <div>
                <p href="#" class="fa fa-facebook"></p>
                <p href="#" class="fa fa-twitter"></p>
                <p href="#" class="fa fa-google"></p>
                <p href="#" class="fa fa-linkedin"></p>
            </div>
            <div style='margin-top:20px'> Copyright © 2025 GASSURE. All rights reserved.</div>
        </div>
        <div class="column-contact-f-1 column-1" style='text-align:center'>
            <p style='font-size:16px;font-weight:600'>Links</p>
            <a class="nav"  style='margin-bottom:10px' href="index.php">Home</a><br />
            <a class="nav" style='margin-bottom:10px' href="client/order.php">Order Now</a><br />
            <a class="nav"  style='margin-bottom:10px'href="client/feedback.php">Feedback</a>
        </div>
        <div class="column-contact-f-2 column-2" style='text-align:left;padding-left:0px'>
            <div style='padding-left:100px'>
                <p style='font-size:16px;font-weight:600'>Contact Us</p>
                <p style='text-align:left'>123 Gas Avenue <br /> Colombo, Sri Lanka</p>
                <p style='text-align:left'>Tel : 011-2345534 | Email: contact@gassure.com</p>
            </div>
        </div>

    </div>

</body>

</html>