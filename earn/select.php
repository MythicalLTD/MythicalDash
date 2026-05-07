<?php
require("../core/require/page.php");
if ($getsettingsdb['disable_earning'] == "true")
{
    echo '<script>window.location.replace("/");</script>';
    $_SESSION['error'] = "You are not allowed to earn coins!";
    die;
}
$userdb = mysqli_query($cpconn, "SELECT * FROM users WHERE user_id = '" . mysqli_real_escape_string($cpconn, $_SESSION["uid"]) . "'")->fetch_object();
?>
<!-- Header -->
<div class="header bg-primary pb-6">
    <div class="container-fluid">
        <div class="header-body">
            <div class="row align-items-center py-4">
                <div class="col-lg-6 col-7">
                    <h6 class="h2 text-white d-inline-block mb-0">Earn coins</h6>
                    <nav aria-label="breadcrumb" class="d-none d-md-inline-block ml-md-4">
                        <ol class="breadcrumb breadcrumb-links breadcrumb-dark">
                            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
                            <li class="breadcrumb-item active" aria-current="page">Earn coins</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>
</div>
<input id="node" name="node" type="hidden" value="">
<!-- Page content -->
<div class="container-fluid mt--6">
    <div class="row justify-content-center">
        <div class="col-lg-8 card-wrapper">
            <div class="card">
                <div class="card-header">
                    <h3 class="mb-0"><img src="https://i.imgur.com/jv3Frir.png" width="30"> Select a way to earn coins</h3>
                </div>
                <div class="card-body" style="text-align: center;">
                    <p>Select a way to earn coins.</p> 
                    <?php 
                    if ($getsettingsdb['enable_afk'] == "true")
                    {
                        ?>
                            <a href="afk"><button type="button" class="btn btn-primary" style="margin-bottom: 10px; margin-right: 10px;"><img src="<?= $getsettingsdb["proto"] . $_SERVER['SERVER_NAME']?>/assets/img/timer.png" width="64"><br/><br/>AFK</button></a>
                        <?php
                    }
                    if ($getsettingsdb['linkvertise_status'] == "true")
                    {
                        ?>
                            <a href="linkvertise"><button type="button" class="btn btn-primary" style="margin-bottom: 10px; margin-right: 10px;"><img src="https://d1fdloi71mui9q.cloudfront.net/0S4upCHkTOq6Bprk20uY_44FK656BY2OELcU9   " width="64"><br/><br/>Linkvertise</button></a>
                        <?php
                    }
                    ?>
                    <a href="reedem"><button type="button" class="btn btn-primary" style="margin-bottom: 10px; margin-right: 10px;"><img src="<?= $getsettingsdb["proto"] . $_SERVER['SERVER_NAME']?>/assets/img/membership_card_480px.png" width="64"><br/><br/>REEDEM</button></a>
                </div>
            </div>
        </div>
    </div>
              <div class = 'row'>
<div class = 'col-lg-6 grid-margin stretch-card '>
<div class = 'card'>
<div class = 'card-body'>
<h4 class = 'card-title'>Top afk</h4>

<div class = 'table-responsive text-white'>
<table class = 'table'>
<thead>
<tr>
<th class="text-white"> Ranking </th>
<th class="text-white"> Username </th>
<th class="text-white"> Minutes afk </th>
</tr>
</thead>
<tbody>
<?php
$result = mysqli_query( $cpconn, 'SELECT username, minutes_idle FROM users ORDER BY minutes_idle DESC LIMIT 5' );

/* First rank will be 1 and
second be 2 and so on */
$ranking = 1;

/* Fetch Rows from the SQL query */
if ( mysqli_num_rows( $result ) ) {

    while ( $row = mysqli_fetch_array( $result ) ) {

        echo '<tr class="mbr-text text-white mbr-fonts-style display-7">';
        echo '<td class="mbr-text text-white mbr-fonts-style display-7">'.htmlspecialchars( $ranking ).'.</td>';
        echo '<td class="mbr-text text-white mbr-fonts-style display-7">'.htmlspecialchars( $row[ 'username' ] ).'</td>';
        echo '<td class="mbr-text text-white mbr-fonts-style display-7">'.htmlspecialchars( round( $row[ 'minutes_idle' ] ) ).'</td>';

        echo '</tr>';
        $ranking++;
    }

}

?>
</tbody>
</table>
</div>
</div>
</div>
</div>
<div class = 'col-lg-6 grid-margin stretch-card'>
<div class = 'card'>
<div class = 'card-body'>
<h4 class = 'card-title'>Top Coins</h4>
<div class = 'table-responsive'>
<table class = 'table table'>
<thead>
<tr>
<th class="text-white"> Ranking </th>
<th class="text-white"> Username </th>
<th class="text-white"> Coins </th>
</tr>
</thead>
<tbody>
<?php
$result = mysqli_query( $cpconn, 'SELECT username, coins FROM users ORDER BY coins DESC LIMIT 5' );

/* First rank will be 1 and
second be 2 and so on */
$ranking = 1;

/* Fetch Rows from the SQL query */
if ( mysqli_num_rows( $result ) ) {

    while ( $row = mysqli_fetch_array( $result ) ) {

        echo '<tr class="mbr-text text-white mbr-fonts-style display-7">';
        echo '<td class="mbr-text text-white mbr-fonts-style display-7">'.htmlspecialchars( $ranking ).'.</td>';
        echo '<td class="mbr-text text-white mbr-fonts-style display-7">'.htmlspecialchars( $row[ 'username' ] ).'</td>';
        echo '<td class="mbr-text text-white mbr-fonts-style display-7">'.htmlspecialchars( round( $row[ 'coins' ] ) ).'</td>';

        echo '</tr>';
        $ranking++;
    }

}

?>
</tbody>
</table>
</div>
</div>
</div>
</div>
</section>

</div>
    <!-- Footer -->
    <footer class="footer pt-0">
        <div class="row align-items-center justify-content-lg-between">
            <div class="col-lg-6">
                <div class="copyright text-center  text-lg-left  text-muted">
                    Copyright &copy;2020-2023 <a href="https://github.com/MythicalLTD/MythicalDash" class="font-weight-bold ml-1" target="_blank">ShadowDash x MythicalDash </a> - Theme by <a href="https://creativetim.com" target="_blank">Creative Tim</a>
                </div>
            </div>
            <div class="col-lg-6">
                <ul class="nav nav-footer justify-content-center justify-content-lg-end">
                    <li class="nav-item">
                        <a href="<?= $getsettingsdb["website"] ?>" class="nav-link" target="_blank"> Website</a>
                    </li>
                    <li class="nav-item">
                        <a href="<?= $getsettingsdb["statuspage"] ?>" class="nav-link" target="_blank">Uptime / Status</a>
                    </li>
                    <li class="nav-item">
                        <a href="<?= $getsettingsdb["privacypolicy"] ?>" class="nav-link" target="_blank">Privacy policy</a>
                    </li>
                    <li class="nav-item">
                        <a href="<?= $getsettingsdb["termsofservice"] ?>" class="nav-link" target="_blank">Terms of service</a>
                    </li>
                </ul>
            </div>
        </div>
    </footer>
    </div>
    </div>
    <script src="<?= $getsettingsdb["proto"] . $_SERVER['SERVER_NAME']?>/assets/js/jquery.min.js"></script>
  <script src="<?= $getsettingsdb["proto"] . $_SERVER['SERVER_NAME']?>/assets/js/bootstrap.bundle.min.js"></script>
  <script src="<?= $getsettingsdb["proto"] . $_SERVER['SERVER_NAME']?>/assets/js/js.cookie.js"></script>
  <script src="<?= $getsettingsdb["proto"] . $_SERVER['SERVER_NAME']?>/assets/js/jquery.scrollbar.min.js"></script>
  <script src="<?= $getsettingsdb["proto"] . $_SERVER['SERVER_NAME']?>/assets/js/jquery-scrollLock.min.js"></script>
  <!-- Argon JS -->
  <script src="<?= $getsettingsdb["proto"] . $_SERVER['SERVER_NAME']?>/assets/js/argon.js?v=1.2.0"></script>
</div>

</html>
