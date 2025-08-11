<?php
require_once 'config/config.php';
require_once 'includes/header.php';
require_once 'includes/footer.php';

// Ensure $pdo is available globally
global $pdo;

// Get user ID from URL
$user_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$user_id) {
    header('Location: index.php');
    exit;
}

// Get user data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$profile_user = $stmt->fetch();

if (!$profile_user) {
    header('Location: index.php');
    exit;
}

// Check if this is the current user's profile
$is_own_profile = isLoggedIn() && $_SESSION['user_id'] == $user_id;

// Custom page title
$pageTitle = htmlspecialchars($profile_user['username']) . '\'s Profile - SynCNet';

// Get user's posts
$stmt = $pdo->prepare("
    SELECT p.*, 
           (SELECT COUNT(*) FROM likes WHERE post_id = p.id) as likes_count,
           (SELECT COUNT(*) FROM comments WHERE post_id = p.id) as comments_count,
           (SELECT COUNT(*) FROM likes WHERE post_id = p.id AND user_id = ?) as user_liked
    FROM posts p 
    WHERE p.user_id = ? 
    ORDER BY p.created_at DESC
");
$stmt->execute([isLoggedIn() ? $_SESSION['user_id'] : 0, $user_id]);
$user_posts = $stmt->fetchAll();

// Get user statistics
$stmt = $pdo->prepare("SELECT COUNT(*) as post_count FROM posts WHERE user_id = ?");
$stmt->execute([$user_id]);
$post_count = $stmt->fetch()['post_count'];

$stmt = $pdo->prepare("SELECT COUNT(*) as total_likes FROM likes l JOIN posts p ON l.post_id = p.id WHERE p.user_id = ?");
$stmt->execute([$user_id]);
$total_likes = $stmt->fetch()['total_likes'];

$stmt = $pdo->prepare("SELECT COUNT(*) as total_comments FROM comments c JOIN posts p ON c.post_id = p.id WHERE p.user_id = ?");
$stmt->execute([$user_id]);
$total_comments = $stmt->fetch()['total_comments'];

$currentUser = getCurrentUser($pdo);

startPage($pageTitle, 'profile');
?>

<!-- Main Content -->
<div class="container mt-5 pt-4">
    <div class="row">
        <!-- Profile Information -->
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-body text-center">
                    <img src="assets/uploads/<?php echo htmlspecialchars($profile_user['profile_picture']); ?>"
                         alt="Profile Picture" class="profile-img-large mb-3">

                    <h4 class="fw-bold"><?php echo htmlspecialchars($profile_user['username']); ?></h4>

                    <div class="badge bg-<?php echo $profile_user['user_level'] == 2 ? 'warning' : 'primary'; ?> mb-3">
                        <?php echo $profile_user['user_level'] == 2 ? 'Moderator' : 'Member'; ?>
                    </div>

                    <?php if ($profile_user['bio']): ?>
                        <p class="text-muted"><?php echo nl2br(htmlspecialchars($profile_user['bio'])); ?></p>
                    <?php endif; ?>

                    <div class="text-muted small mb-3">
                        <i class="fas fa-calendar-alt"></i>
                        Joined <?php echo date('F Y', strtotime($profile_user['created_at'])); ?>
                    </div>

                    <?php if ($is_own_profile): ?>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#editProfileModal">
                            <i class="fas fa-edit"></i> Edit Profile
                        </button>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Statistics -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6><i class="fas fa-chart-bar"></i> Statistics</h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-4">
                            <div class="h4 text-primary"><?php echo $post_count; ?></div>
                            <div class="small text-muted">Posts</div>
                        </div>
                        <div class="col-4">
                            <div class="h4 text-danger"><?php echo $total_likes; ?></div>
                            <div class="small text-muted">Likes</div>
                        </div>
                        <div class="col-4">
                            <div class="h4 text-success"><?php echo $total_comments; ?></div>
                            <div class="small text-muted">Comments</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="card d-none d-lg-block">
                <div class="card-header">
                    <h6><i class="fas fa-clock"></i> Recent Activity</h6>
                </div>
                <div class="card-body">
                    <?php if (count($user_posts) > 0): ?>
                        <div class="activity-item mb-2">
                            <i class="fas fa-edit text-primary"></i>
                            <span class="small">Posted "<?php echo substr(htmlspecialchars($user_posts[0]['content']), 0, 30); ?>..."
                            <?php echo formatDate($user_posts[0]['created_at']); ?></span>
                        </div>
                    <?php endif; ?>

                    <div class="activity-item">
                        <i class="fas fa-user-plus text-success"></i>
                        <span class="small">Joined SynCNet <?php echo formatDate($profile_user['created_at']); ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Posts Feed -->
        <div class="col-lg-8">
            <?php if ($is_own_profile): ?>
                <!-- Create Post -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form action="create_post.php" method="POST" enctype="multipart/form-data">
                            <div class="mb-3">
                                <textarea class="form-control" name="content" placeholder="What's on your mind?"
                                          rows="3" required maxlength="1000" oninput="autoResizeTextarea(this)"></textarea>
                                <div class="form-text">
                                    <span id="postCharCount">1000 characters remaining</span>
                                </div>
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

            <!-- Posts Header -->
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5><?php echo $is_own_profile ? 'Your Posts' : htmlspecialchars($profile_user['username']) . "'s Posts"; ?></h5>
                <div class="text-muted"><?php echo $post_count; ?> posts</div>
            </div>

            <!-- Posts List -->
            <?php if (count($user_posts) > 0): ?>
                <?php foreach ($user_posts as $post): ?>
                    <div class="card mb-4 post-card" data-post-id="<?php echo $post['id']; ?>">
                        <div class="card-header d-flex align-items-center">
                            <img src="assets/uploads/<?php echo htmlspecialchars($profile_user['profile_picture']); ?>"
                                 alt="Profile" class="profile-img-tiny me-2">
                            <div class="flex-grow-1">
                                <div class="fw-bold"><?php echo htmlspecialchars($profile_user['username']); ?></div>
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
                                        <i class="fas fa-comment"></i> <?php echo $post['comments_count']; ?> Comments
                                    </button>
                                </div>

                                <button class="btn btn-link p-0 text-muted" onclick="copyToClipboard(window.location.origin + '/profile.php?id=<?php echo $user_id; ?>&post=<?php echo $post['id']; ?>')">
                                    <i class="fas fa-share"></i> Share
                                </button>
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
                                                            <strong>
                                                                <a href="profile.php?id=<?php echo $comment['user_id']; ?>" class="text-decoration-none">
                                                                    <?php echo htmlspecialchars($comment['username']); ?>
                                                                </a>
                                                            </strong>
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
            <?php else: ?>
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No posts yet</h5>
                        <p class="text-muted">
                            <?php echo $is_own_profile ? "Share your first post to get started!" : htmlspecialchars($profile_user['username']) . " hasn't shared any posts yet."; ?>
                        </p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Edit Profile Modal -->
<?php if ($is_own_profile): ?>
    <div class="modal fade" id="editProfileModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Profile</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="update_profile.php" method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="mb-3 text-center">
                            <img src="assets/uploads/<?php echo htmlspecialchars($profile_user['profile_picture']); ?>"
                                 alt="Profile Preview" class="profile-img-small mb-3" id="editProfilePreview">
                            <div>
                                <label for="edit_profile_picture" class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-camera"></i> Change Picture
                                </label>
                                <input type="file" class="d-none" id="edit_profile_picture" name="profile_picture"
                                       accept="image/*" onchange="previewImage(this, 'editProfilePreview')">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="edit_bio" class="form-label">Bio</label>
                            <textarea class="form-control" id="edit_bio" name="bio" rows="3" maxlength="500"><?php echo htmlspecialchars($profile_user['bio']); ?></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php endPage(); ?>