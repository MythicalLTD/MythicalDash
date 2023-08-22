<?php 
echo "====== MythicalDash queue ======\n\n";
echo "[INFO/loader] Loading files...\n";
try {
    if (file_exists('../vendor/autoload.php')) { 
        require("../vendor/autoload.php");
    } else {
        die("Hello, it looks like you did not run:  'composer install --no-dev --optimize-autoloader'. Please run that and refresh the page");
    }
} catch (Exception $e) {
    die("Hello, it looks like you did not run: 'composer install --no-dev --optimize-autoloader' Please run that and refresh");
}

?>