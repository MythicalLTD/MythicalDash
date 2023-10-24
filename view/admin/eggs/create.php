<?php 
include(__DIR__ . '/../../requirements/page.php');
include(__DIR__ . '/../../requirements/admin.php');
if (isset($_GET['create_egg'])) {
    $name = mysqli_real_escape_string($conn, $_GET['name']);
    $category = mysqli_real_escape_string($conn, $_GET['category']);
    $nest_id = mysqli_real_escape_string($conn, $_GET['nest_id']);
    $nest_egg_id = mysqli_real_escape_string($conn, $_GET['nest_egg_id']);
    if ($name == "" || $category == "" || $nest_id == "" || $nest_egg_id == "" ) {
        header('location: /admin/eggs?e=Please fill in all information.');
        die();
    } else {
        $check_query = "SELECT * FROM mythicaldash_eggs WHERE egg = '$nest_egg_id'";
        $result = mysqli_query($conn, $check_query);
        if (mysqli_num_rows($result) > 0) {
            header('location: /admin/eggs?e=This egg exists in the database');
            $conn->close();
            die();

        } else {
            $conn->query("INSERT INTO `mythicaldash_eggs` (`name`, `category`, `egg`, `nest`) VALUES ('" . $name . "', '" . $category . "', '" . $nest_egg_id . "', '" . $nest_id . "');");
            header('location: /admin/eggs?s=Done we added a new egg');
            $conn->close();
            die();
        }
    }
} else {
    header('location: /admin/eggs');
    die();
}
?>