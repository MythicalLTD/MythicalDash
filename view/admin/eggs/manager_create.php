<?php
use MythicalDash\EggManagerConfig;

include(__DIR__ . '/../../requirements/page.php');
include(__DIR__ . '/../../requirements/admin.php');
if (isset($_GET['create_egg'])) {
    $name = mysqli_real_escape_string($conn, $_GET['name']);
    $value = mysqli_real_escape_string($conn, $_GET['value']);
    if ($name == "" || $value == "") {
        header('location: /admin/eggs/config?e=Please fill in all information.');
        die();
    } else {
        $check_query = "SELECT * FROM mythicaldash_eggs_config WHERE setting_name = '$name'";
        $result = mysqli_query($conn, $check_query);
        if (!mysqli_num_rows($result) > 0) {
            if (EggManagerConfig::addConfig($name, $value)) {
                header('location: /admin/eggs/config?s=Done we added a new egg config.');
                $conn->close();
                die();
            } else {
                $conn->close();
                header('location: /admin/eggs/config?e=Failed to config the eggs config.');
                die();
            }
        } else {
            header('location: /admin/eggs/config?e=This egg exists in the database.');
            $conn->close();
            die();
        }
    }
} else {
    header('location: /admin/eggs/config');
    die();
}
?>