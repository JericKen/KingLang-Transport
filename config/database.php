<?php
$host = "localhost";
$user = "root";
$password = "";
$database = "kinglang_booking";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$database;charset=utf8", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    error_log("Database connection failed: " . $e->getMessage());
    die("Database connection failed. Please check the error log for details.");
}

// Helper functions for authentication
function is_client_authenticated() {
    return isset($_SESSION["user_id"]) && !empty($_SESSION["user_id"]);
}

function is_admin_authenticated() {
    return isset($_SESSION["role"]) && ($_SESSION["role"] === "Super Admin" || $_SESSION["role"] === "Admin");
}

function require_client_auth() {
    $requestUri = $_SERVER['REQUEST_URI'];
    
    // Skip auth check for login-related routes to avoid redirect loops
    if (strpos($requestUri, '/admin/login') !== false || 
        strpos($requestUri, '/home/login') !== false ||
        strpos($requestUri, '/home/signup') !== false) {
        return;
    }
    
    if (!is_client_authenticated()) {
        header("Location: /home/login");
        exit();
    }
}

function require_admin_auth() {
    $requestUri = $_SERVER['REQUEST_URI'];
    
    // Skip auth check for login-related routes to avoid redirect loops
    if (strpos($requestUri, '/admin/login') !== false || 
        strpos($requestUri, '/admin/submit-login') !== false) {
        return;
    }
    
    if (!is_admin_authenticated()) {
        header("Location: /admin/login");
        exit();
    }
}
?>