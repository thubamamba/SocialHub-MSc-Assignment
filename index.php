<?php
require_once 'config/config.php';
require_once 'includes/header.php';
require_once 'includes/footer.php';

// Ensure $pdo is available globally
global $pdo;

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username = sanitizeInput($_POST['username']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$username, $username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['user_level'] = $user['user_level'];
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                showAlert('Welcome back, " . $user['username'] . "!', 'success');
            });
        </script>";
    } else {
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                showAlert('Invalid username or password!', 'danger');
            });
        </script>";
    }
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: index.php');
    exit;
}

// Display session messages
$success_message = '';
$error_messages = [];

if (isset($_SESSION['success_message'])) {
    $success_message = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}

if (isset($_SESSION['error_messages'])) {
    $error_messages = $_SESSION['error_messages'];
    unset($_SESSION['error_messages']);
}

// Get posts for feed
$stmt = $pdo->prepare("
    SELECT p.*, u.username, u.profile_picture,
           (SELECT COUNT(*) FROM likes WHERE post_id = p.id) as likes_count,
           (SELECT COUNT(*) FROM likes WHERE post_id = p.id AND user_id = ?) as user_liked,
           (SELECT COUNT(*) FROM comments WHERE post_id = p.id) as comments_count
    FROM posts p 
    JOIN users u ON p.user_id = u.id 
    ORDER BY p.created_at DESC
");
$stmt->execute([isLoggedIn() ? $_SESSION['user_id'] : 0]);
$posts = $stmt->fetchAll();

$currentUser = getCurrentUser($pdo);

startPage('SocialHub - Connect with Friends', 'home');
?>

<!-- Main Content -->
<div class="container-fluid mt-5 pt-4">
    <!-- Success/Error Messages -->
    <?php if (!empty($success_message)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success_message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (!empty($error_messages)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle"></i>
            <?php if (count($error_messages) == 1): ?>
                <?php echo htmlspecialchars($error_messages[0]); ?>
            <?php else: ?>
                <ul class="mb-0">
                    <?php foreach ($error_messages as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <!-- Left Sidebar (hidden on mobile) -->
        <div class="col-lg-3 d-none d-lg-block">
            <div class="sidebar">
                <?php if (isLoggedIn()): ?>
                    <div class="card mb-4">
                        <div class="card-body text-center">
                            <img src="assets/uploads/<?php echo htmlspecialchars($currentUser['profile_picture']); ?>"
                                 alt="Profile" class="profile-img-small mb-2">
                            <h6><?php echo htmlspecialchars($currentUser['username']); ?></h6>
                            <p class="text-muted small"><?php echo htmlspecialchars($currentUser['bio']); ?></p>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-header">
                        <h6><i class="fas fa-fire"></i> Trending Topics</h6>
                    </div>
                    <div class="card-body">
                        <div class="trending-topic">#BeachLife</div>
                        <div class="trending-topic">#Photography</div>
                        <div class="trending-topic">#Nature</div>
                        <div class="trending-topic">#Sunset</div>
                        <div class="trending-topic">#Hiking</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Feed -->
        <div class="col-lg-6 col-md-8">
            <!-- Bootstrap Carousel -->
            <div id="featuredCarousel" class="carousel slide mb-4" data-bs-ride="carousel">
                <div class="carousel-indicators">
                    <button type="button" data-bs-target="#featuredCarousel" data-bs-slide-to="0" class="active"></button>
                    <button type="button" data-bs-target="#featuredCarousel" data-bs-slide-to="1"></button>
                    <button type="button" data-bs-target="#featuredCarousel" data-bs-slide-to="2"></button>
                </div>
                <div class="carousel-inner">
                    <div class="carousel-item active">
                        <img src="assets/images/connect2.jpg" class="d-block w-100" alt="Connect with Friends">
                        <div class="carousel-caption d-none d-md-block">
                            <h5>Welcome to SocialHub</h5>
                            <p>Connect, share, and explore with friends from around the world.</p>
                        </div>
                    </div>
                    <div class="carousel-item">
                        <img src="assets/images/happy.jpg" class="d-block w-100" alt="Happy Image">
                        <div class="carousel-caption d-none d-md-block">
                            <h5>Share Your Moments</h5>
                            <p>Upload photos and share your experiences with your community.</p>
                        </div>
                    </div>
                    <div class="carousel-item">
                        <img src="assets/images/connect.jpg" class="d-block w-100" alt="Connect with People">
                        <div class="carousel-caption d-none d-md-block">
                            <h5>Discover New Connections</h5>
                            <p>Find and connect with people who share your interests.</p>
                        </div>
                    </div>
                </div>
                <button class="carousel-control-prev" type="button" data-bs-target="#featuredCarousel" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon"></span>
                    <span class="visually-hidden">Previous</span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#featuredCarousel" data-bs-slide="next">
                    <span class="carousel-control-next-icon"></span>
                    <span class="visually-hidden">Next</span>
                </button>
            </div>

            <!-- Create Post (Only for logged-in users) -->
            <?php if (isLoggedIn()): ?>
                <div class="card mb-4">
                    <div class="card-body">
                        <form action="create_post.php" method="POST" enctype="multipart/form-data">
                            <div class="mb-3">
                                <textarea class="form-control" name="content" placeholder="What's on your mind, <?php echo htmlspecialchars($currentUser['username']); ?>?" rows="3" required></textarea>
                            </div>
                            <div class="mb-3">
                                <input type="file" class="form-control" name="image" accept="image/*">
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-share"></i> Share Post
                            </button>
                        </form>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Posts Feed -->
            <div id="posts-container">
                <?php foreach ($posts as $post): ?>
                    <div class="card mb-4 post-card" data-post-id="<?php echo $post['id']; ?>">
                        <div class="card-header d-flex align-items-center">
                            <img src="assets/uploads/<?php echo htmlspecialchars($post['profile_picture']); ?>"
                                 alt="Profile" class="profile-img-tiny me-2">
                            <div class="flex-grow-1">
                                <a href="profile.php?id=<?php echo $post['user_id']; ?>" class="fw-bold text-decoration-none">
                                    <?php echo htmlspecialchars($post['username']); ?>
                                </a>
                                <div class="text-muted small"><?php echo formatDate($post['created_at']); ?></div>
                            </div>
                            <?php if (isLoggedIn() && ($_SESSION['user_level'] == 2 || $_SESSION['user_id'] == $post['user_id'])): ?>
                                <div class="dropdown">
                                    <button class="btn btn-link text-muted" data-bs-toggle="dropdown">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item text-danger" href="#" onclick="deletePost(<?php echo $post['id']; ?>)">
                                                <i class="fas fa-trash"></i> Delete
                                            </a></li>
                                    </ul>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="card-body">
                            <p class="card-text"><?php echo nl2br(htmlspecialchars($post['content'])); ?></p>
                            <?php if ($post['image_url']): ?>
                                <img src="assets/uploads/<?php echo htmlspecialchars($post['image_url']); ?>"
                                     class="img-fluid rounded mb-3" alt="Post image">
                            <?php endif; ?>
                        </div>

                        <div class="card-footer">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <?php if (isLoggedIn()): ?>
                                        <button class="btn btn-link p-0 me-3 like-btn <?php echo $post['user_liked'] ? 'liked' : ''; ?>"
                                                onclick="toggleLike(<?php echo $post['id']; ?>)">
                                            <i class="fas fa-heart"></i> <span class="likes-count"><?php echo $post['likes_count']; ?></span>
                                        </button>
                                    <?php else: ?>
                                        <span class="text-muted">
                                        <i class="fas fa-heart"></i> <?php echo $post['likes_count']; ?>
                                    </span>
                                    <?php endif; ?>

                                    <button class="btn btn-link p-0" onclick="toggleComments(<?php echo $post['id']; ?>)">
                                        <i class="fas fa-comment"></i> <span class="comments-count"><?php echo $post['comments_count']; ?></span>
                                    </button>
                                </div>
                            </div>

                            <!-- Comments Section -->
                            <div class="comments-section mt-3" id="comments-<?php echo $post['id']; ?>" style="display: none;">
                                <div class="comments-list">
                                    <?php
                                    $stmt = $pdo->prepare("
                                        SELECT c.*, u.username, u.profile_picture 
                                        FROM comments c 
                                        JOIN users u ON c.user_id = u.id 
                                        WHERE c.post_id = ? 
                                        ORDER BY c.created_at ASC
                                    ");
                                    $stmt->execute([$post['id']]);
                                    $comments = $stmt->fetchAll();

                                    if (empty($comments)): ?>
                                        <div class="no-comments text-muted text-center py-3">
                                            <i class="fas fa-comment-slash me-2"></i>
                                            No comments yet. Be the first to comment!
                                        </div>
                                    <?php else: ?>
                                        <?php foreach ($comments as $comment): ?>
                                            <div class="comment mb-2" data-comment-id="<?php echo $comment['id']; ?>">
                                                <div class="d-flex">
                                                    <img src="assets/uploads/<?php echo htmlspecialchars($comment['profile_picture']); ?>"
                                                         alt="Profile" class="profile-img-tiny me-2">
                                                    <div class="flex-grow-1">
                                                        <div class="comment-content">
                                                            <strong><?php echo htmlspecialchars($comment['username']); ?></strong>
                                                            <?php echo htmlspecialchars($comment['content']); ?>
                                                        </div>
                                                        <small class="text-muted"><?php echo formatDate($comment['created_at']); ?></small>
                                                        <?php if (isLoggedIn() && ($_SESSION['user_level'] == 2 || $_SESSION['user_id'] == $comment['user_id'])): ?>
                                                            <button class="btn btn-link btn-sm text-danger p-0 ms-2" onclick="deleteComment(<?php echo $comment['id']; ?>)">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>

                                <?php if (isLoggedIn()): ?>
                                    <form class="mt-3" onsubmit="addComment(event, <?php echo $post['id']; ?>)">
                                        <div class="input-group">
                                            <input type="text" class="form-control" placeholder="Write a comment..." required>
                                            <button class="btn btn-primary" type="submit">
                                                <i class="fas fa-paper-plane"></i>
                                            </button>
                                        </div>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Right Sidebar (hidden on mobile) -->
        <div class="col-lg-3 d-none d-lg-block">
            <div class="card">
                <div class="card-header">
                    <h6><i class="fas fa-users"></i> Suggested Users</h6>
                </div>
                <div class="card-body">
                    <?php
                    $stmt = $pdo->prepare("SELECT * FROM users ORDER BY created_at DESC LIMIT 5");
                    $stmt->execute();
                    $suggested_users = $stmt->fetchAll();

                    foreach ($suggested_users as $user):
                        ?>
                        <div class="d-flex align-items-center mb-3">
                            <img src="assets/uploads/<?php echo htmlspecialchars($user['profile_picture']); ?>"
                                 alt="Profile" class="profile-img-tiny me-2">
                            <div class="flex-grow-1">
                                <div class="fw-bold"><?php echo htmlspecialchars($user['username']); ?></div>
                                <small class="text-muted">Joined <?php echo date('M Y', strtotime($user['created_at'])); ?></small>
                            </div>
                            <a href="profile.php?id=<?php echo $user['id']; ?>" class="btn btn-sm btn-outline-primary">
                                View
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php endPage(); ?>