<?php 
use MythicalDash\ErrorHandler;
include (__DIR__ . '/../../requirements/page.php');
include (__DIR__ . '/../../requirements/admin.php');

try {
    if (isset($_POST['cmd']) && !empty($_POST['cmd'])) {
        try {
            $result = $conn->query($_POST['cmd']);

            if ($result !== false) {
                $message = 'Query executed successfully';
            } else {
                $message = 'Query executed successfully, but no results returned.';
            }
            header('location: /admin/settings?sqlr='.$message);
            die();
        } catch (Exception $e) {
            $message = 'Failed to run SQL script: ' . $e->getMessage();
            header('location: /admin/settings?sqlr='.$message);
            die();
        }
    } else {
        $message = 'Failed to run SQL script: Null';
        header('location: /admin/settings?e=' . $message);
        die();
    }
} catch (Exception $e) {   
    ErrorHandler::Critical("MYSQL","Failed to run SQL script: " . $e->getMessage());
    header('location: /admin/settings?e=Failed to run SQL script: ' . $e->getMessage());
    die();
}
