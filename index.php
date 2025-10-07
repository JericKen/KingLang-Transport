<?php

declare(strict_types=1); 

date_default_timezone_set('Asia/Manila');



ini_set('display_errors', 1);

error_reporting(E_ALL);



// Set stricter cookie parameters

ini_set('session.use_strict_mode', '1');

ini_set('session.use_cookies', '1');

ini_set('session.use_only_cookies', '1');

ini_set('session.cookie_httponly', '1');



// Start session with proper settings

session_start();



// Include database configuration

require_once __DIR__ . "/config/database.php";



// Include timezone utilities

require_once __DIR__ . "/config/timezone_utils.php";



// Include settings helper

require_once __DIR__ . "/config/settings.php";



require_once __DIR__ . "/routes/web.php";   

?>