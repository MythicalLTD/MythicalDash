<?php
use MythicalDash\Encryption;
use MythicalDash\ErrorHandler;
use MythicalDash\SettingsManager;

$csrf = new MythicalDash\CSRF();

include(__DIR__ . '/../requirements/page.php');
if (SettingsManager::getSetting("enable_stripe") == "false") {
    header('location: /');
}
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['pay'])) {
        $mypaymentkey = Encryption::generate_keynoinfo();
        $conn->query("INSERT INTO `mythicaldash_payments` (`code`, `coins`, `ownerkey`, `getaway`, `status`) VALUES ('".$mypaymentkey."', '".mysqli_real_escape_string($conn, $_GET['coins'])."', '".mysqli_real_escape_string($conn, $_COOKIE['token'])."', 'stripe', 'pending');");
        try {
            //stripe_secret_key
            \Stripe\Stripe::setApiKey(SettingsManager::getSetting('stripe_secret_key'));
            $checkout_session = \Stripe\Checkout\Session::create([
                "mode" => "payment",
                'customer_email' => $session->getUserInfo('email'),
                "success_url" => "" . $appURL . "/store/get/stripe/coins?code=".$mypaymentkey,
                "cancel_url" => "" . $appURL . "/user/payments?e=We canceled your payment!",
                "line_items" => [
                    [
                        "quantity" => 1,
                        "price_data" => [
                            "currency" => strtolower(SettingsManager::getSetting('stripe_currency')),
                            "unit_amount" => intval(SettingsManager::getSetting('stripe_coin_per_balance')) * intval($_GET['coins']),
                            "product_data" => [
                                "images" => [
                                    $appURL . "/assets/img/illustrations/page-pricing-standard.png"
                                ],
                                "name" => "Payment of " . $_GET['coins'] . " coins on " . SettingsManager::getSetting("name"),
                                "description" => "Upon successful payment, you'll be acquiring " . $_GET['coins'] . " on " . SettingsManager::getSetting("name") . "! Rest assured, our system is fully secure and powered by Stripe for end-to-end protection."
                            ]
                        ]
                    ]
                ]
            ]);
            http_response_code(303);
            header("location: " . $checkout_session->url);
            die();
        } catch (Exception $e) {
            header("location: /user/payments?e=Stripe Error".$e);
            ErrorHandler::Error("Stripe Error ", $e);
            die();
        }

    }
}
?>
<!DOCTYPE html>

<html lang="en" class="dark-style layout-navbar-fixed layout-menu-fixed" dir="ltr" data-theme="theme-semi-dark"
    data-assets-path="<?= $appURL ?>/assets/" data-template="vertical-menu-template">

<head>
    <meta charset="utf-8" />
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    <?php include(__DIR__ . '/../requirements/head.php'); ?>
    <title>
        <?= SettingsManager::getSetting("name") ?> - Buy Coins
    </title>
    <link rel="stylesheet" href="<?= $appURL ?>/assets/vendor/css/pages/page-help-center.css" />
</head>

<body>

    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            <?php include(__DIR__ . '/../components/sidebar.php') ?>
            <div class="layout-page">
                <?php include(__DIR__ . '/../components/navbar.php') ?>
                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">
                        <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Earn / Buy / </span> Coins</h4>
                        <?php include(__DIR__ . '/../components/alert.php') ?>
                        <div id="ads">
                            <?php
                            if (SettingsManager::getSetting("enable_ads") == "true") {
                                ?>
                                <br>
                                <?= SettingsManager::getSetting("ads_code") ?>
                                <br>
                                <?php
                            }
                            ?>
                        </div>
                        <div class="row">
                            <div class="col-md-12 grid-margin stretch-card">
                                <div class="card">
                                    <div class="card-header text-center">
                                        <div class="card-title">Buy Coins Using Stripe</div>
                                    </div>
                                    <div class="card-body text-center">
                                        <form method='GET'>
                                            <p>Welcome to our billing system. Here you can buy coins, so you can use
                                                them inside our dashboard</p>
                                            <p>The prices are "
                                                <?= number_format(intval(SettingsManager::getSetting('stripe_coin_per_balance')) / 100, 2) ?>
                                                in
                                                <?= strtoupper(SettingsManager::getSetting('stripe_currency')) ?> " for
                                                1
                                                coin
                                            </p>
                                            <input type="number" class="form-control mb-2" placeholder="50" value="25"
                                                name="coins">
                                            <br>
                                            <?= $csrf->input('pay-form'); ?>
                                            <button name="pay" type="subbmit" class="btn btn-primary">Purchase
                                                Coins (DO NOT PAY!! U WON'T GET COINS FUNCTION IS NOT DONE)</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div id="ads">
                            <?php
                            if (SettingsManager::getSetting("enable_ads") == "true") {
                                ?>
                                <br>
                                <?= SettingsManager::getSetting("ads_code") ?>
                                <br>
                                <?php
                            }
                            ?>
                        </div>
                    </div>
                </div>
                <?php include(__DIR__ . '/../components/footer.php') ?>
                <div class="content-backdrop fade"></div>
            </div>
        </div>
    </div>
    <div class="layout-overlay layout-menu-toggle"></div>
    <div class="drag-target"></div>
    </div>
    <?php include(__DIR__ . '/../requirements/footer.php') ?>

    <script src="<?= $appURL ?>/assets/js/user/paymentss-ecommerce.js"></script>

</body>