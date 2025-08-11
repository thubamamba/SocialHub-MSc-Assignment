<?php
// Load environment variables from .env file
$dotenv = parse_ini_file(__DIR__ . '/../.env');
if ($dotenv === false) {
    die('Error: .env file not found or not readable. Please copy .env.example to .env and configure your settings.');
}

// Set error reporting based on environment
if (($dotenv['APP_ENV'] ?? 'production') === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Database configuration from environment variables
define('DB_HOST', $dotenv['DB_HOST'] ?? 'localhost:8889');
define('DB_NAME', $dotenv['DB_NAME'] ?? 'social_media_db');
define('DB_USER', $dotenv['DB_USER'] ?? 'root');
define('DB_PASS', $dotenv['DB_PASS'] ?? 'root');

// Database connection
try {
    // Parse host and port if port is included in DB_HOST
    $hostParts = explode(':', DB_HOST);
    $host = $hostParts[0];
    $port = $hostParts[1] ?? '3306'; // Default MySQL port if not specified

    $pdo = new PDO(
        "mysql:host=$host;port=$port;dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
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

// Function to sanitize input
function sanitizeInput($input) {
    return htmlspecialchars(strip_tags(trim($input)));
}

// Function to format date
function formatDate($date) {
    return date('M j, Y g:i A', strtotime($date));
}
