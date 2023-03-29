<!-- <footer class="footer1 card-footer">
    <div class="container">
        <div class="row align-items-center justify-content-xl-between">
            <div class="col-lg-6">
                <div class="copyright text-center text-xl-left text-muted">
                    &copy; 2023 <?= $getsettingsdb["name"]?>  - Powered by <a href="https://github.com/AtoroTech" class="font-weight-bold ml-1" target="_blank">AtoroTech</a>
                </div>
            </div>
            <div class="col-lg-6">
                <ul class="nav nav-footer justify-content-center justify-content-lg-end text-muted" >
                    <li class="nav-item" >
                        <span>v2.0</span>
                    </li>
                    <li class="nav-item space2">
                        <span>-</span>
                    </li>
                    <li class="nav-item space2">
                        <span id="loadtime"></span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</footer> -->

<style>
    .footer1 {
        position: fixed;
        left: 0;
        bottom: 0;
        width: 100%;
        background-color: #191f33;
        color: white;
        text-align: center;
    }
    .space2 {
        margin-left: 5px;
    }
</style>
<script type="text/javascript">
    var before_loadtime = new Date().getTime();
    window.onload = Pageloadtime;
    function Pageloadtime() {
        var aftr_loadtime = new Date().getTime();
        // Time calculating in seconds
        pgloadtime = (aftr_loadtime - before_loadtime) / 1000

        document.getElementById("loadtime").innerHTML = pgloadtime + "s";
    }
</script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/js-yaml/4.1.0/js-yaml.min.js"></script>
<script src="<?= $getsettingsdb["proto"] . $_SERVER['SERVER_NAME']?>/assets/js/core/jquery.3.2.1.min.js"></script>
<script src="<?= $getsettingsdb["proto"] . $_SERVER['SERVER_NAME']?>/assets/js/core/popper.min.js"></script>
<script src="<?= $getsettingsdb["proto"] . $_SERVER['SERVER_NAME']?>/assets/js/core/bootstrap.min.js"></script>
<script src="<?= $getsettingsdb["proto"] . $_SERVER['SERVER_NAME']?>/assets/js/plugin/jquery-ui-1.12.1.custom/jquery-ui.min.js"></script>
<script src="<?= $getsettingsdb["proto"] . $_SERVER['SERVER_NAME']?>/assets/js/plugin/jquery-ui-touch-punch/jquery.ui.touch-punch.min.js"></script>
<script src="<?= $getsettingsdb["proto"] . $_SERVER['SERVER_NAME']?>/assets/js/plugin/jquery-scrollbar/jquery.scrollbar.min.js"></script>
<script src="<?= $getsettingsdb["proto"] . $_SERVER['SERVER_NAME']?>/assets/js/plugin/jquery.sparkline/jquery.sparkline.min.js"></script>
<script src="<?= $getsettingsdb["proto"] . $_SERVER['SERVER_NAME']?>/assets/js/atlantis.min.js"></script>


