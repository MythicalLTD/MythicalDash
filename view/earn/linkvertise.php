<?php
include(__DIR__ . '/../requirements/page.php');
if ($settings['linkvertise_enabled'] == "false") {
    header('location: /');
}
if (isset($_GET['key'])) {
    $key = mysqli_escape_string($conn, $_GET['key']);
    $result = mysqli_query($conn, "SELECT * FROM mythicaldash_linkvertise WHERE skey='$key'");
    if (mysqli_num_rows($result) > 0) {
        $usr_coins = $userdb['coins'];
        $newcoins = $usr_coins + $settings['linkvertise_coins'];
        $conn->query("UPDATE `mythicaldash_users` SET `coins` = '" . $newcoins . "' WHERE `mythicaldash_users`.`api_key` = '" . mysqli_real_escape_string($conn, $_COOKIE["token"])."'");
        $conn->query("DELETE FROM mythicaldash_linkvertise WHERE skey='$key'");
        header('location: /');
    } else {
        header('location: /dashboard?e=Error: Key not found.');
    }
}
?>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?= $settings['name'] ?> - Linkvertise
    </title>
    <link rel="icon" href="<?= $settings['logo'] ?>" type="image/png">
    <style>
        * {
            font-family: Google sans, Arial;
        }

        html,
        body {
            margin: 0;
            padding: 0;
        }

        .flex-container {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            color: white;
            animation: colorSlide 15s cubic-bezier(0.075, 0.82, 0.165, 1) infinite;
        }

        .flex-container .text-center {
            text-align: center;
        }

        .flex-container .text-center h1,
        .flex-container .text-center h3 {
            margin: 10px;
            cursor: default;
        }

        .flex-container .text-center h1 .fade-in,
        .flex-container .text-center h3 .fade-in {
            animation: fadeIn 2s ease infinite;
        }

        .flex-container .text-center h1 {
            font-size: 8em;
            transition: font-size 200ms ease-in-out;
            border-bottom: 1px dashed white;
        }

        .flex-container .text-center h1 span#digit1 {
            animation-delay: 200ms;
        }

        .flex-container .text-center h1 span#digit2 {
            animation-delay: 300ms;
        }

        .flex-container .text-center h1 span#digit3 {
            animation-delay: 400ms;
        }

        .flex-container .text-center button {
            border: 1px solid white;
            background: transparent;
            outline: none;
            padding: 10px 20px;
            font-size: 1.1rem;
            font-weight: bold;
            color: white;
            text-transform: uppercase;
            transition: background-color 200ms ease-in;
            margin: 20px 0;
        }

        .flex-container .text-center button:hover {
            background-color: white;
            color: #555;
            cursor: pointer;
        }

        @keyframes colorSlide {
            0% {
                background-color: #152a68;
            }

            25% {
                background-color: royalblue;
            }

            50% {
                background-color: seagreen;
            }

            75% {
                background-color: tomato;
            }

            100% {
                background-color: #152a68;
            }
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            100% {
                opacity: 1;
            }
        }
    </style>
</head>

<body>
    <div class="flex-container">
        <div class="text-center">
            <h1>
                <span class="fade-in" id="digit1">Link</span>
                <span class="fade-in" id="digit2">ready</span>
            </h1>
            <h3 class="fadeIn">Please click the continue button to continue</h3>
            <?php
            $genid = mt_rand(100000000000000, 999999999999999);
            $linkid = $genid;
            mysqli_query($conn, "INSERT INTO `mythicaldash_linkvertise` (`skey`) VALUES ('" . $linkid . "');");
            $url = "/earn/linkvertise?key=" . $linkid;
            echo '
        <a href="' . $url . '"><button type="button" name="button">Continue</button></a>
        ';
            ?>
        </div>
    </div>
</body>

</html>
<script src="https://publisher.linkvertise.com/cdn/linkvertise.js"></script>
<script>linkvertise(<?= $settings['linkvertise_code'] ?>, { whitelist: ["<?= $appURL ?>"], blacklist: [] });</script>