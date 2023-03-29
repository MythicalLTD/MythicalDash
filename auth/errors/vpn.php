<?php
require("../../core/require/config.php");
require("../../core/require/sql.php");
$getsettingsdb = $cpconn->query("SELECT * FROM settings")->fetch_array();
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">

    <title><?= $getsettingsdb["name"] ?> | VPNs not allowed</title>
    <?php 
		include('../../core/imports/header.php');
  ?>
</head>

<body data-background-color="dark">

<div class="">
    <div class="container mt--8 pb-5">
        <div class="row justify-content-center mt-5">
            <div class="col-lg-4 col-md-3">
                <div class="card card-profile mt-5">
                    <div class="card-header">
                        <h4 class="card-title text-center">You got caught with a VPN</h4>
                    </div>
                    <div class="card-body pt-5 px-5">
                        <ul class="nav nav-pills nav-secondary nav-pills-no-bd nav-pills-icons justify-content-center" id="pills-tab-with-icon" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="pills-home-tab-icon" data-toggle="pill" href="" role="tab" aria-controls="pills-home-icon" aria-selected="true">
                                    <i class="flaticon-lock-1"></i>
                                    VPN
                                </a>
                            </li>
                        </ul>
                        <div class="tab-content mt-2 mb-3 text-white" id="pills-with-icon-tabContent">
                            <div class="tab-pane fade show active text-center text-white" id="pills-home-icon" role="tabpanel" aria-labelledby="pills-home-tab-icon">
                                <p>
                                    Looks like your cheating the systems. Won't work here!
                                </p>
                                <p>We try to provide good user experience, so we have blocked all VPN access to the client area.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<!--
<div class="container mt--8 pb-5">
    <div class="row justify-content-center mt-5">
        <div class="col-lg-4 col-md-3">
            <div class="card card-profile bg-secondary mt-5">
                <div class="card-body pt-5 px-5">
                    <div class="text-center mb-4">
                        <img src="https://i.imgur.com/CvhLFdI.png" width="100" />
                        <h3>VPNs are not allowed.</h3>
                        <p>We aim to provide you a good user experience. That is why for moderation purposes VPNs are not allowed.</p>
                    </div>
                    <form role="form">
                        <div class="text-center">
                            <a href="<?= $_CONFIG["discordserver"] ?>" target="_blank"><button type="button" class="btn btn-primary mt-2">Join our discord server</button></a>
                            <a href="/auth/logout"><button type="button" class="btn btn-danger mt-2">Logout</button></a>
                        </div>
                    </form>
                    <br/><br/>
                </div>
            </div>
        </div>
    </div>
</div>-->



  <?php 
include('../../core/imports/footer.php')
?>
</body>

</html>