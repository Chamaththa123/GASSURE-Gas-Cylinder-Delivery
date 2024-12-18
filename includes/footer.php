<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        .container-contact-f {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            font-size: 12px;
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

        @media (max-width: 768px) {
            .container-contact-f {
                flex-direction: column;
            }

            .column-contact-f-1,
            .column-contact-f-2 {
                width: 100%;
                text-align: center; /* Adjust for center alignment in mobile view */
            }

            .nav {
                display: block;
                padding: 10px 0; /* Add spacing between links */
            }
        }
    </style>
</head>

<body>
    <div class="container-contact-f">
        <div class="column-contact-f-1 column-1">
            Copyright © 2025 GASSURE. All rights reserved.
        </div>
        <div class="column-contact-f-2 column-2">
            <a class="nav" href="./index.php">Home</a>
            <a class="nav" href="./client/order.php">Order Now</a>
            <a class="nav" href="./client/feedback.php">Feedback</a>
        </div>
    </div>
</body>

</html>
