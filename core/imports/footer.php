

<style>

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

