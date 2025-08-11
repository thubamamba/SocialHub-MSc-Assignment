<?php
// Database configuration for MAMP
define('DB_HOST', 'localhost:8889'); // Note the port 8889
define('DB_NAME', 'social_media_db');
define('DB_USER', 'root');
define('DB_PASS', 'root'); // MAMP default password is 'root'

// Alternative connection string for MAMP
try {
    $pdo = new PDO("mysql:host=localhost;port=8889;dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Start session
session_start();

// Function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Function to get current user data
function getCurrentUser($pdo) {
    if (!isLoggedIn()) {
        return null;
    }

    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}

// Function to get any user by ID
function getUser($pdo, $user_id) {
    if (!$user_id) {
        return null;
    }

    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    return $stmt->fetch();
}

// Function to get user by username
function getUserByUsername($pdo, $username) {
    if (!$username) {
        return null;
    }

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    return $stmt->fetch();
}
// Function to sanitize input
function sanitizeInput($input) {
    return htmlspecialchars(strip_tags(trim($input)));
}

// Function to format date
function formatDate($date) {
    return date('M j, Y g:i A', strtotime($date));
}
?>

