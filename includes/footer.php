<DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <!-- <link rel="stylesheet" href="public/assets/css/trainer.css"> -->
        <style>
        .container-contact-f {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            font-size: 12px;
            background-color: #0074D9;
            color: white;
            margin-top: 20px;
        }

        .column-contact-f-1 {
            flex: 1;
            padding: 20px;
            box-sizing: border-box;
            width: 30%;
        }

        .column-contact-f-2 {
            flex: 1;
            padding: 20px;
            box-sizing: border-box;
            width: 70%;
            color: white
        }

        .nav {
            float: left;
            text-align: center;
            padding: 0px 16px;
            text-decoration: none;
            color: white
        }

        @media (max-width: 768px) {

            .container-contact-f {
                flex-direction: column;
            }

            .column-contact-f-1 {
                padding: 20px;
                width: 100%;
                margin: 0;
            }

            .column-contact-f-2 {
                padding: 20px;
                width: 100%;
                margin: 0;
            }
        }
        </style>

    </head>

    <body>
        <div class="container-contact-f">
            <div class="column-contact-f-1 column-1">
                Copyright © 2024 FitZone. All rights reserved.
            </div>
            <div class="column-contact-f-2 column-2">
                <a class='nav' href="index.php">Home</a>
                <a class='nav' href="#membership">Membership</a>
                <a class='nav' href="trainer.php">Our Trainers</a>
                <a class='nav' href="trainer.php">Classes</a>
                <a class='nav' href="contactus.php">Contact Us</a>
                <a class='nav' href="about.php">About Us</a>
                <a class='nav' href="#blog">Blog</a>
            </div>
        </div>
    </body>

    </html>