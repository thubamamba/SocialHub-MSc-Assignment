<?php
require_once 'config/config.php';

// Ensure $pdo is available globally
global $pdo;

// Check if user is logged in and is admin
if (!isLoggedIn()) {
    $_SESSION['error_messages'] = ['Please log in to access the admin panel.'];
    header('Location: index.php');
    exit;
}

$currentUser = getCurrentUser($pdo);
if (!$currentUser || $currentUser['user_level'] != 2) {
    $_SESSION['error_messages'] = ['You do not have permission to access the admin panel.'];
    header('Location: index.php');
    exit;
}

$currentPage = 'admin';
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - SocialHub</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
<div class="container-fluid mt-5 pt-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-lg">
                <div class="card-header bg-primary text-white">
                    <h2 class="card-title mb-0">
                        <i class="fas fa-shield-alt me-2"></i>
                        Admin Panel
                    </h2>
                </div>
                <div class="card-body p-5">
                    <div class="text-center">
                        <i class="fas fa-tools fa-5x text-muted mb-4"></i>
                        <h3 class="text-muted">Admin Dashboard</h3>
                        <p class="lead text-muted">
                            Welcome to the administrator control panel. This is where you can manage users,
                            moderate content, and configure system settings for SocialHub.
                        </p>
                        <hr class="my-4">
                        <p class="text-muted">
                            Admin features and controls can be implemented here.
                        </p>
                        <a href="index.php" class="btn btn-primary">
                            <i class="fas fa-home me-2"></i>
                            Back to Home
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>