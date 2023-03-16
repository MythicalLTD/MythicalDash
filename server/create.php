<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require("../core/require/page.php");
require("../core/require/addons.php");

if($getperms['cancreate'] == "true")
{

}
else
{
    echo '<script>window.location.replace("/");</script>';
    $_SESSION['error'] = "You are not allowed to create servers";
    die;
}
$userdb = mysqli_query($cpconn, "SELECT * FROM users where user_id = '". $_SESSION["uid"]. "'")->fetch_object();


?>
<!-- Header -->
<div class="header bg-primary pb-6">
    <div class="container-fluid">
        <div class="header-body">
            <div class="row align-items-center py-4">
                <div class="col-lg-6 col-7">
                    <h6 class="h2 text-white d-inline-block mb-0">Create a server</h6>
                    <nav aria-label="breadcrumb" class="d-none d-md-inline-block ml-md-4">
                        <ol class="breadcrumb breadcrumb-links breadcrumb-dark">
                            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
                            <li class="breadcrumb-item active" aria-current="page">Create a new server</li>
                        </ol>
                    </nav>
                </div>
                <div class="col-lg-6 col-5 text-right">
                    <button class="btn btn-sm btn-neutral" id="chksrvslots" onClick="seeServerSlots();">Only check server slots</button>
                </div>
            </div>
        </div>
    </div>
</div>
<input id="node" name="node" type="hidden" value="">
<!-- Page content -->
<div class="container-fluid mt--6">
    <div class="row justify-content-center">
    <div class="col-md-12">
            </div>
        <div class="col-lg-8 card-wrapper">
            <div class="card" id="stepsCard">
                <div class="card-header">
                    <h3 class="mb-0"><img src="https://i.imgur.com/F8TP5Tx.png" width="30"> Create a new server</h3>
                </div>
                <div class="card-body">
                <div class="progress-wrapper">
                    <div class="progress-info">
                        <div class="progress-label">
                            <span id="currentStep">Step 1/3</span>
                        </div>
                        <div class="progress-percentage">
                            <span id="stepPercentage">33%</span>
                        </div>
                    </div>
                    <div class="progress" id="progressColor">
                        <div id="stepProgress" class="progress-bar bg-primary" role="progressbar" aria-valuenow="33" aria-valuemin="0" aria-valuemax="100" style="width: 33%;"></div>
                    </div>
                </div>
                </div>
            </div>
            <!-- Alerts -->
            <?php
            if (isset($_SESSION["success"])) {
                echo '<div class="alert alert-success" role="alert"><strong>Success!</strong> ' . $_SESSION["success"] . '</div>';
                unset($_SESSION["success"]);
            }
            ?>
            <?php
            if (isset($_SESSION["error"])) {
                echo '<div class="alert alert-danger" role="alert"><strong>Error!</strong> ' . $_SESSION["error"] . '</div>';
                unset($_SESSION["error"]);
            }
            ?>
            
        </div>
            <div id="alert"></div>
            <!--
                LOADING CARD
            --->
            <div class="card" id="loadingCard" style="display: none;">
                <div class="card-header">
                    <h4 class="card-title"></h4>
                </div>
                <div class="card-content collapse show" aria-expanded="true">
                    <div class="card-body">
                        <p class="card-text">
                        <center>
                            <h3>
                                <img src="https://i.imgur.com/UxVBmZl.png" /><br/>
                                Please wait
                                <br/><br/>
                                <img src="https://assets.mythicalnodes.xyz/mythicaldash/img/loading.gif" width="64">
                            </h3>
                        </center>
                        </p>
                    </div>
                </div>
            </div>
            <!--
                STEP 1
            --->
            <div class="card" id="step1">
                <div class="card-header">
                    <h4 class="card-title">Set your server info and specs.</h4>
                </div>
                <div class="card-content collapse show" aria-expanded="true">
                    <div class="card-body">
                        <p class="card-text">
                        <div class="mb-3">
                            <label for="name" class="form-label">Server name</label>
                            <input type="text" id="name" name="name" class="form-control" placeholder="Awesome server" required>
                        </div>
                        <div class="mb-3">
                            <label for="memory" class="form-label">Amount of RAM (In MB)</label>
                            <input type="number" id="memory" name="memory" class="form-control memory" value="<?= floor($userdb->memory/$userdb->server_limit) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="cores" class="form-label">CPU limit (In %)</label>
                            <input type="number" id="cores" name="cores" class="form-control cores" value="<?= floor($userdb->cpu/$userdb->server_limit) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="disk" class="form-label">Amount of disk (In MB)</label>
                            <input type="number" id="disk" name="disk" class="form-control disk" value="<?= floor($userdb->disk_space/$userdb->server_limit) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="ports" class="form-lavel">Additional Ports</label>
                            <input type="number" id="ports" name="ports" class="form-control ports" value="0" required>
                        </div>
                        <div class="mb-3">
                            <label for="databases" class="form-label">Databases</label>
                            <input type="number" id="databases" name="databases" class="form-control databases" value="0" required>
                        </div>
                        <div class="mb-3">
                            <label for="backups" class="form-label">Backups</label>
                            <input type="number" id="backups" name="backups" class="form-control backups" value="0" required>
                        </div>
                        </p>
                        <p><small>By proceeding to the next step, you agree to our <a href="<?= $getsettingsdb["termsofservice"] ?>" target="_blank">terms and conditions</a>, and our <a href="<?= $getsettingsdb["privacypolicy"] ?>">privacy policy</a>.</small></p>
                        <button class="btn btn-primary" id="step1btn" onClick="nextStep();">Next »</button>
                    </div>
                </div>
            </div>
            <!---
                Step 2
            --->
            <div class="card" id="step2" style="display: none;">
                <div class="card-header">
                    <h4 class="card-title" id="selectNodeText">Select the location for your server</h4>
                </div>
                <div class="card-content collapse show" aria-expanded="true">
                    <div class="card-body">
                        <p class="card-text">
                            <div style="text-align: center;">
                                <div class="container">
                                    <div class="row">
                                    <?php
                                    $locations = mysqli_query($cpconn, "SELECT * FROM locations")->fetch_all(MYSQLI_ASSOC);
                                    foreach ($locations as $location) {
                                    $serversOnLoc = mysqli_query($cpconn, "SELECT * FROM servers WHERE location='" . $location["id"] . "'")->fetch_all(MYSQLI_ASSOC);
                                    $availableSlots = $location['slots'] - count($serversOnLoc);
                                    $serversInQueue = mysqli_query($cpconn, "SELECT * FROM servers_queue WHERE location='" . $location["id"] . "'")->fetch_all(MYSQLI_ASSOC);
                                    ?><div class="col-sm">
                                        <?php
                                        if ($location["status"] == "PRIVATE") {
                                            echo "<span class='badge badge-warning badge-glow' style='font-size: 15px;'>Private node</span>";
                                        }
                                        if ($location["status"] == "MAINTENANCE") {
                                            echo "<span class='badge badge-danger badge-glow' style='font-size: 15px;'>In maintenance</span>";
                                        }
                                        ?>
                                        <br/>
                                        <img src="<?=$location["icon"] ?>" width="70">
                                        <h3><?= $location["name"] ?></h3>
                        <p><b><?= $availableSlots ?></b> out of <b><?php echo $location["slots"]; ?></b> slots left.<br/>
                            <b><?php echo count($serversInQueue); ?></b> servers in queue.</p>
                        <br/><br/>
                        <?php
                        if ($location["status"] == "PRIVATE") {
                            if ($isDonator == false) {
                                echo '<button type="button" class="btn btn-danger" style="cursor: not-allowed;" disabled="1">Private node users only</button><br/><a href="/billing/buy/private">Buy private node access</a>';
                            }
                            else {
                                echo '<button type="button" class="btn btn-primary" id="btnnode' . $location["id"] . '" onclick="selectNode(' . $location["id"] . ', this);">SELECT</button>';
                            }

                        } elseif ($location["status"] == "MAINTENANCE") {
                            echo '<button type="button" class="btn btn-danger" style="cursor: not-allowed;" disabled="1">Maintenance</button>';
                        } else {
                            echo '<button type="button" class="btn btn-primary" id="btnnode' . $location["id"] . '" tag="nodeselectionbutton" onclick="selectNode(' . $location["id"] . ', this);">SELECT</button>';
                        }
                        ?>
                    </div>
                    <?php
                    }
		    if (count($locations) == 0) {
			echo "No nodes are available at the moment; Server creation might currently be disabled.";
		    }
                    ?>
                </div>
                </div>
                </center>
                </p>
                <button class="btn btn-primary" id="prevstep2btn" onclick="previousStep()">« Previous</button>
                <button class="btn btn-primary" id="step2btn" disabled="1" onClick="nextStep();">Next »</button>
            </div>
        </div>
    </div>
</div>
    <!---
        Step 3
    --->
    <div class="card" id="step3" style="display: none;">
        <div class="card-header">
            <h4 class="card-title">Select the server type</h4>
        </div>
        <div class="card-content collapse show" aria-expanded="true">
            <div class="card-body">
                <p class="card-text">
                    <button class="btn btn-primary" onclick="previousStep()">Previous «</button>
                <div style="text-align: center;">
                    <div class="container">
                        <div class="row">
                    <?php
                    $alrCategories = array();
                    $categories = mysqli_query($cpconn, "SELECT category FROM eggs")->fetch_all(MYSQLI_ASSOC);
                    foreach ($categories as $category) {
                        if (in_array($category["category"], $alrCategories)) {
                            continue;
                        }
                        echo "</div><br/><br/>";
                        array_push($alrCategories, $category["category"]);
                        echo "<h3>" . $category["category"] . "</h3> <br/>";
                        $eggs = mysqli_query($cpconn, "SELECT * FROM eggs WHERE category='" . $category["category"] . "'")->fetch_all(MYSQLI_ASSOC);
                        $i = -1;
                        echo '<div class="row">';
                        foreach ($eggs as $egg) {
                            $i++;
                            if ($i == 3) {
                                echo "</div><div class='row' style='align-content: center;'>";
                                $i = 0;
                            }
                            ?>
                            <div class="col-sm">
                                <br/>
                                <img src="<?= $egg["icon"] ?>" width="70">
                                <h3><?= $egg["name"] ?></h3>
                                <br/><br/>

                                <button type="button" onclick="submitForm(<?= $egg['id'] ?>)" class="btn btn-primary">Create!</button>
                            </div>
                            <?php
                        }
                    }
                    ?>
                        </div>
                    </div>
            </div>
            </center>
            </p><br/><br/>
        </div>
    </div>

                </div>
                <div class="col-md-12">
            <br><br>
            
        </div>
            
        </div>
    </div>

    <!-- Argon Scripts -->
    <!-- Core -->
<!-- Step changing -->
<script type="text/javascript">
    var currentStep = 1;
    async function previousStep() {
        currentStep--;
        lastStep = currentStep - 1;
        var w = parseInt(document.getElementById('stepProgress').style.width);
        document.getElementById('stepProgress').style.width= (w - 30) +'%';
        document.getElementById("currentStep").innerHTML = "Step " + currentStep + "/3"
        document.getElementById("stepPercentage").innerHTML = (currentStep*33) + "%"
        if (currentStep == 1) {
            document.getElementById("step1").style.display = "block";
            document.getElementById("step1").style.display = "block";
            document.getElementById("step2").style.display = "none";
        }
        if (currentStep == 2) {
            document.getElementById("step2").style.display = "block";
            document.getElementById("step3").style.display = "none";
        }
        if (currentStep == 3) {
            document.getElementById("step3").style.display = "block";
            document.getElementById("step2").style.display = "none";
        }

    }
    async function nextStep() {
        var div = document.getElementById("alert");
        var error = false;
        if (currentStep == 1) {
            document.getElementById("loadingCard").style.display = "block";
            document.getElementById("step1").style.display = "none";

            var name = document.getElementById("name").value;
            var memory = document.getElementById("memory").value;
            var cores = document.getElementById("cores").value;
            var disk = document.getElementById("disk").value;

            var free_memory = null;
            var free_disk = null;
            var free_cpu = null;
            var cpuLimit = null;
            name = name.toString();
            if (name.length == 0) {
                error = true;
                var button = document.createElement("button");
                button.className = "alert alert-danger";
                button.innerHTML = "The name field is empty.";
                button.style = "width:100%";
                div.appendChild(button);

                document.getElementById("loadingCard").style.display = "none";
                document.getElementById("step1").style.display = "block";
            }
            await $.get("/api/user/freememory?userid=<?= $_SESSION["uid"] ?>", function(data) {
                free_memory = JSON.parse(data).freeMemory;
                console.log(free_memory);
                if (memory > free_memory) {
                    error = true;
                    var button = document.createElement("button");
                    button.className = "alert alert-danger";
                    button.innerHTML = "You don't have enough memory, you only have " + free_memory + "MB left. <span data-dismiss='alert' class='pull-right float-right'>✕</span>";
                    button.style = "width:100%";
                    var div = document.getElementById("alert");
                    div.appendChild(button);

                    document.getElementById("loadingCard").style.display = "none";
                    document.getElementById("step1").style.display = "block";
                }
            });
            await $.get("/api/user/freedisk?userid=<?= $_SESSION["uid"] ?>", function(data) {
                free_disk = JSON.parse(data).freeDisk;

                if (disk > free_disk) {
                    error = true;
                    var button = document.createElement("button");
                    button.className = "alert alert-danger";
                    button.innerHTML = "You don't have enough disk, you only have " + free_disk + "MB left. <span data-dismiss='alert' class='pull-right float-right'>✕</span>";
                    button.style = "width:100%";
                    var div = document.getElementById("alert");
                    div.appendChild(button);

                    document.getElementById("loadingCard").style.display = "none";
                    document.getElementById("step1").style.display = "block";
                }
            });
            await $.get("/api/user/freecpu?userid=<?= $_SESSION["uid"] ?>", function(data) {
                free_cpu = JSON.parse(data).freecpu;

                if (cores > free_cpu) {
                    error = true;
                    var button = document.createElement("button");
                    button.className = "alert alert-danger";
                    button.innerHTML = "You don't have enough cpu limit, you only have <?= $userdb->cpu ?>% limit. <span data-dismiss='alert' class='pull-right float-right'>✕</span>";
                    button.style = "width:100%";
                    var div = document.getElementById("alert");
                    div.appendChild(button);

                    document.getElementById("loadingCard").style.display = "none";
                    document.getElementById("step1").style.display = "block";

                }
            });


            if (memory < 256) {
                error = true;
                var button = document.createElement("button");
                button.className = "alert alert-danger";
                button.innerHTML = "Minimum memory is 256MB <span data-dismiss='alert' class='pull-right float-right'>✕</span>";
                button.style = "width:100%";
                var div = document.getElementById("alert");
                div.appendChild(button);

                document.getElementById("loadingCard").style.display = "none";
                document.getElementById("step1").style.display = "block";

            }
            if (disk < 256) {
                error = true;
                var button = document.createElement("button");
                button.className = "alert alert-danger";
                button.innerHTML = "Minimum disk is 256MB <span data-dismiss='alert' class='pull-right float-right'>✕</span>";
                button.style = "width:100%;";
                div.appendChild(button);

                document.getElementById("loadingCard").style.display = "none";
                document.getElementById("step1").style.display = "block";
            }
            if (cores < 0.15) {
                error = true;
                var button = document.createElement("button");
                button.className = "alert alert-danger";
                button.innerHTML = "Minimum cores is 0.15 <span data-dismiss='alert' class='pull-right float-right'>✕</span>";
                button.style = "width:100%;";
                div.appendChild(button);

                document.getElementById("loadingCard").style.display = "none";
                document.getElementById("step1").style.display = "block";
            }


        }
        currentStep++;
        document.getElementById("stepPercentage").innerHTML = (currentStep*33) + "%"
        if (error == true) {
            currentStep = 1;
        }
        var w = parseInt(document.getElementById('stepProgress').style.width);
        if (error !== true) {document.getElementById('stepProgress').style.width= (w + 30) +'%';
            document.getElementById("currentStep").innerHTML = "Step " + currentStep + "/3"; }
        if (currentStep == 2) {
            document.getElementById("loadingCard").style.display = "none";
            document.getElementById("step1").style.display = "none";
            document.getElementById("step2").style.display = "block";
        }
        if (currentStep == 3) {
            document.getElementById("loadingCard").style.display = "none";
            document.getElementById("step2").style.display = "none";
            document.getElementById("step3").style.display = "block";
        }
        if (currentStep == 4) {
            document.getElementById("loadingCard").style.display = "none";
            document.getElementById("step3").style.display = "block";
            document.getElementById("step4").style.display = "none";
        }
    }
</script>
<!-- Only check server slots -->
<script>
    function seeServerSlots() {
        document.getElementById("loadingCard").style.display = "none";
        document.getElementById("step1").style.display = "none";
        document.getElementById("step2").style.display = "block";
        document.getElementById("step2btn").style.display = "none";
        document.getElementById("stepsCard").style.display = "none";
        document.getElementById("selectNodeText").innerHTML = "Available nodes and slots";
        document.getElementById("prevstep2btn").style.display = "none";
        document.getElementById("chksrvslots").style.display = "none";
        var ele = document.getElementsByTagName("Button");
        if (ele.length > 0) {
            for (i = 0; i < ele.length; i++) {
                if (ele[i].type == "button")
                    ele[i].style.display = "none";
            }
        }
    }
</script>
<!-- Node selection -->
<script type="text/javascript">
    var lastbutton = "";

    function selectNode(nodeID, btn) {
        document.getElementById("node").value = nodeID;
        document.getElementById("step2btn").disabled = false;
        if (btn == lastbutton) {

        } else {
            lastbutton.textContent = "SELECT";
            lastbutton.className = "btn btn-primary";
            lastbutton = btn;
        }
        btn.textContent = "SELECTED";
        btn.className = "btn btn-success";
    }
    async function submitForm(egg) {
        var w = parseInt(document.getElementById('stepProgress').style.width);
        document.getElementById('stepProgress').style.width= (w + 100) +'%';
        document.getElementById('progressColor').className = "progress progress-bar-success mt-25";
        document.getElementById("loadingCard").style.display = "block";
        document.getElementById("step3").style.display = "none";
        var node = document.getElementById("node").value;
        var name = document.getElementById("name").value;
        var memory = document.getElementById("memory").value;
        var disk = document.getElementById("disk").value;
        var cores = document.getElementById("cores").value;
        var dbs = document.getElementById("databases").value;
        var ports = document.getElementById("ports").value;
        var backups = document.getElementById("backups").value;
        $.post('/api/user/servers/create',   // url
            {
                "name": name,
                "memory": memory,
                "cores": cores,
                "disk": disk,
                "ports": ports,
                "databases": dbs,
                "backups": backups,
                "location": node,
                "egg": egg

            }, // data to be submit
            function(data, status, jqXHR) {// success callback
                console.log(data)
                var response = JSON.parse(data);
                var status = response.success;
                if (status == true) {
                    window.location.replace("/");
                }
                else {
                    var button = document.createElement("button");
                    button.className = "alert alert-danger";
                    button.innerHTML = "<strong>Error:</strong> " + response['errors']['detail'] + "<span data-dismiss='alert' class='pull-right float-right'>✕</span>";
                    button.style = "width:100%";
                    var div = document.getElementById("alert");
                    div.appendChild(button);
                    document.getElementById("loadingCard").style.display = "none";
                    document.getElementById("step3").style.display = "block";
                }
            });


    }
</script>
<script src="<?= $getsettingsdb["proto"] . $_SERVER['SERVER_NAME']?>/assets/argon/js/jquery.min.js"></script>
  <script src="<?= $getsettingsdb["proto"] . $_SERVER['SERVER_NAME']?>/assets/argon/js/bootstrap.bundle.min.js"></script>
  <script src="<?= $getsettingsdb["proto"] . $_SERVER['SERVER_NAME']?>/assets/argon/js/js.cookie.js"></script>
  <script src="<?= $getsettingsdb["proto"] . $_SERVER['SERVER_NAME']?>/assets/argon/js/jquery.scrollbar.min.js"></script>
  <script src="<?= $getsettingsdb["proto"] . $_SERVER['SERVER_NAME']?>/assets/argon/js/jquery-scrollLock.min.js"></script>
  <!-- Argon JS -->
  <script src="<?= $getsettingsdb["proto"] . $_SERVER['SERVER_NAME']?>/assets/argon/js/argon.js?v=1.2.0"></script>
</div>

</html>
