<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require('../core/require/page.php');


if (isset($_POST['create_ticket'])) {
    $user_id = $_SESSION['uid'];
    $query = "SELECT COUNT(*) FROM tickets WHERE user_id = '$user_id' AND status='open'";
    $result = mysqli_query($cpconn, $query);
    $count = mysqli_fetch_array($result)[0];
    if ($count >= 3) {
      $_SESSION['error'] = "Please close your old tickets";
      echo '<script>window.location.replace("/");</script>';
      die();
    } else {
      $content = mysqli_real_escape_string($cpconn, $_POST['content']);
      $ticketdb = $cpconn->query("SELECT * FROM users WHERE user_id = '" . mysqli_real_escape_string($cpconn, $_SESSION["uid"]) . "'")->fetch_array();

      $query = "INSERT INTO tickets (user_id, username, content, status) VALUES ('$user_id', '".$ticketdb['username']."','$content', 'open')";
      mysqli_query($cpconn, $query);
      $ticket_id = mysqli_insert_id($cpconn);
    
      $query = "INSERT INTO messages (user_id, username, ticket_id, content) VALUES ('$user_id', '".$ticketdb['username']."','$ticket_id', '$content')";
      mysqli_query($cpconn, $query);
      $current_date_and_time = date("Y-m-d H:i");
      $hex = $getsettingsdb['seo_color'];

      $r = hexdec(substr($hex, 1, 2));
      $g = hexdec(substr($hex, 3, 2));
      $b = hexdec(substr($hex, 5, 2));

      $rgb = ($r << 16) + ($g << 8) + $b;

      $json_data = json_encode([
        "content" => "@everyone <".$getsettingsdb["proto"] . $_SERVER['SERVER_NAME']."/support/view_ticket.php?id=$ticket_id>",
        "embeds" => [
            [
               "title" => $getsettingsdb['name']." | New ticket",
                "description" => "There is a new ticket on our dashboard please check it out and make sure to respect the user!",
                "color" => $rgb,
                "fields" => [
                    [
                        "name" => "Ticket owner",
                        "value" => $ticketdb['username'],
                    ],
                    [
                        "name" => "Ticket Description",
                        "value" => $content,
                    ],
                    [
                      "name" => "Ticket creation date",
                      "value" => $current_date_and_time,
                    ],
                ],
            ],
        ],
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $getsettingsdb['webhook'],
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $json_data,
        CURLOPT_HTTPHEADER => [
            "Content-Type: application/json"
        ],
    ]);
    
    $response = curl_exec($ch);
    curl_close($ch);
    echo '<script>window.location.replace("view_ticket.php?id='.$ticket_id.'");</script>';
    mysqli_close($cpconn);
    exit;
    }
   
}
?>
<div class="header bg-primary pb-6">
    <div class="container-fluid">
        <div class="header-body">
            <div class="row align-items-center py-4">
                <div class="col-lg-6 col-7">
                    <h6 class="h2 text-white d-inline-block mb-0">Support</h6>
                    <nav aria-label="breadcrumb" class="d-none d-md-inline-block ml-md-4">
                        <ol class="breadcrumb breadcrumb-links breadcrumb-dark">
                            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
                            <li class="breadcrumb-item active" aria-current="page">Tickets</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>
</div>
<input id="node" name="node" type="hidden" value="">
<div class="container-fluid mt--6">
    <div class="row justify-content-center">
        <div class="col-lg-8 card-wrapper">
            <div class="card">
                <div class="card-header text-center">
                  <h1>Create Ticket</h1>
                    <form action="create_ticket.php" method="post">
                      <div class="form-group">
                        <label for="content">Message</label>
                        <textarea required class="form-control" name="content" id="content" rows="3"></textarea>
                      </div>
                      <p>Your user ID is: <?php echo $_SESSION['uid']; ?></p>
                      <button type="submit" name="create_ticket" class="btn btn-primary">Create Ticket</button>
                    </form>
                  </div>
                </div>
              </div>
            </div>  
          </div>
        </div>
      </div>
    </div>
  </div>

</body>

<script>
      $("#gamepanelopen").popover({ trigger: "hover" });
  </script>
  <script src="<?= $getsettingsdb["proto"] . $_SERVER['SERVER_NAME']?>/assets/argon/js/jquery.min.js"></script>
  <script src="<?= $getsettingsdb["proto"] . $_SERVER['SERVER_NAME']?>/assets/argon/js/bootstrap.bundle.min.js"></script>
  <script src="<?= $getsettingsdb["proto"] . $_SERVER['SERVER_NAME']?>/assets/argon/js/js.cookie.js"></script>
  <script src="<?= $getsettingsdb["proto"] . $_SERVER['SERVER_NAME']?>/assets/argon/js/jquery.scrollbar.min.js"></script>
  <script src="<?= $getsettingsdb["proto"] . $_SERVER['SERVER_NAME']?>/assets/argon/js/jquery-scrollLock.min.js"></script>
  <!-- Argon JS -->
  <script src="<?= $getsettingsdb["proto"] . $_SERVER['SERVER_NAME']?>/assets/argon/js/argon.js?v=1.2.0"></script>
</html>