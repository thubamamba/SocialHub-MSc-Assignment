<?php
/**
 * Render the website header
 * @param string $pageTitle - The page title
 * @param string $currentPage - Current page identifier for navigation highlighting
 * @param array $additionalCSS - Array of additional CSS files to include
 * @param array $additionalJS - Array of additional JS files to include
 */
function renderHeader($pageTitle = 'SynCNet - Connect with Friends', $currentPage = 'home', $additionalCSS = [], $additionalJS = []) {
    $currentUser = getCurrentUser($GLOBALS['pdo']);
    ?>
    <!DOCTYPE html>
    <html lang="en" data-bs-theme="light">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo htmlspecialchars($pageTitle); ?></title>

        <!-- Favicon -->
        <link rel="icon" type="image/x-icon" href="assets/images/favicon.ico">

        <!-- Core CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
        <link href="assets/css/style.css" rel="stylesheet">

        <!-- Additional CSS -->
        <?php foreach ($additionalCSS as $css): ?>
            <link href="<?php echo htmlspecialchars($css); ?>" rel="stylesheet">
        <?php endforeach; ?>

        <!-- Additional JS -->
        <?php foreach ($additionalJS as $js): ?>
            <script src="<?php echo htmlspecialchars($js); ?>"></script>
        <?php endforeach; ?>

        <!-- Meta tags for SEO -->
        <meta name="description" content="SynCNet - Connect, share, and explore with friends from around the world.">
        <meta name="keywords" content="social media, connect, share, friends, community">
        <meta name="author" content="SynCNet">

        <!-- Open Graph tags -->
        <meta property="og:title" content="<?php echo htmlspecialchars($pageTitle); ?>">
        <meta property="og:description" content="Connect, share, and explore with friends from around the world.">
        <meta property="og:type" content="website">
        <meta property="og:url" content="<?php echo getCurrentURL(); ?>">
        <meta property="og:image" content="assets/images/social-preview.jpg">
    </head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg fixed-top" data-bs-theme="dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-share-alt"></i> SynCNet
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link <?php echo $currentPage === 'home' ? 'active' : ''; ?>" href="index.php">
                            <i class="fas fa-home"></i> Home
                        </a>
                    </li>
                    <?php if (isLoggedIn()): ?>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $currentPage === 'profile' ? 'active' : ''; ?>" href="profile.php?id=<?php echo $_SESSION['user_id']; ?>">
                                <i class="fas fa-user"></i> My Profile
                            </a>
                        </li>

                    <?php endif; ?>
                </ul>

                <!-- Search Bar -->
                <form class="d-flex me-3" id="searchForm">
                    <input class="form-control" type="search" id="searchInput" placeholder="Search users..." aria-label="Search">
                    <button class="btn btn-outline-light" type="submit">
                        <i class="fas fa-search"></i>
                    </button>
                </form>

                <!-- Theme Toggle -->
                <button class="btn btn-outline-light me-2" onclick="toggleTheme()" id="theme-toggle" title="Toggle dark/light mode">
                    <i class="fas fa-moon" id="theme-icon"></i>
                </button>

                <ul class="navbar-nav">
                    <?php if (isLoggedIn()): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                                <img src="assets/uploads/<?php echo htmlspecialchars($currentUser['profile_picture']); ?>"
                                     alt="Profile" class="profile-img-tiny me-1">
                                <?php echo htmlspecialchars($currentUser['username']); ?>
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="profile.php?id=<?php echo $_SESSION['user_id']; ?>">
                                        <i class="fas fa-user"></i> My Profile
                                    </a></li>
                                <?php if ($_SESSION['user_level'] == 2): ?>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="admin.php">
                                            <i class="fas fa-shield-alt"></i> Admin Panel
                                        </a></li>
                                <?php endif; ?>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="#" onclick="confirmLogout()">
                                        <i class="fas fa-sign-out-alt"></i> Logout
                                    </a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <button class="btn btn-outline-light me-2" data-bs-toggle="modal" data-bs-target="#loginModal">
                                <i class="fas fa-sign-in-alt"></i> Login
                            </button>
                        </li>
                        <li class="nav-item">
                            <a class="btn btn-light" href="register.php">
                                <i class="fas fa-user-plus"></i> Register
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content Container -->
    <div class="main-content">
    <?php
}

/**
 * Render session messages (success/error alerts)
 */
function renderSessionMessages() {
    // Display session messages
    if (isset($_SESSION['success_message'])) {
        echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle"></i> ' . htmlspecialchars($_SESSION['success_message']) . '
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
              </div>';
        unset($_SESSION['success_message']);
    }

    if (isset($_SESSION['error_messages'])) {
        $errors = $_SESSION['error_messages'];
        echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle"></i>';

        if (count($errors) == 1) {
            echo ' ' . htmlspecialchars($errors[0]);
        } else {
            echo '<ul class="mb-0">';
            foreach ($errors as $error) {
                echo '<li>' . htmlspecialchars($error) . '</li>';
            }
            echo '</ul>';
        }

        echo '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
              </div>';
        unset($_SESSION['error_messages']);
    }
}

/**
 * Helper function to get current URL
 */
function getCurrentURL() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    return $protocol . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
}
?>