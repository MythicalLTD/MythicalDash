<?php 
include(__DIR__ . '/../../requirements/page.php');
include(__DIR__ . '/../../requirements/admin.php');
use MythicalDash\Telemetry;
if (isset($_GET['create_location'])) {
    $name = mysqli_real_escape_string($conn, $_GET['name']);
    $locationid = mysqli_real_escape_string($conn, $_GET['locationid']);
    $slots = mysqli_real_escape_string($conn, $_GET['slots']);
    if ($name == "" || $locationid == "" || $slots == "" ) {
        header('location: /admin/locations?e=Please fill in all information.');
        die();
    } else {
        $check_query = "SELECT * FROM mythicaldash_locations WHERE name = '$name' OR locationid = '$locationid'";
        $result = mysqli_query($conn, $check_query);
        if (mysqli_num_rows($result) > 0) {
            header('location: /admin/locations?e=This location exists in the database');
            $conn->close();
            die();

        } else {
            $conn->query("INSERT INTO `mythicaldash_locations` (`name`, `locationid`, `slots`) VALUES ('" . $name . "', '" . $locationid . "', '" . $slots . "');");
            Telemetry::NewNode();
            header('location: /admin/locations?s=Done we added a new location');
            $conn->close();
            die();
        }
    }
} else {
    header('location: /admin/locations');
    die();
}
?>