<?php
/**
 * Render a post card component
 * @param array $post - Post data
 * @param object $pdo - Database connection
 */
function renderPostCard($post, $pdo) {
    ?>
    <div class="card mb-4 post-card" data-post-id="<?php echo $post['id']; ?>">
        <div class="card-header d-flex align-items-center">
            <img src="assets/images/<?php echo htmlspecialchars($post['profile_picture']); ?>"
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
                <img src="assets/images/<?php echo htmlspecialchars($post['image_url']); ?>"
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
                        <i class="fas fa-comment"></i> Comments
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

                    foreach ($comments as $comment):
                        renderComment($comment);
                    endforeach;
                    ?>
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
    <?php
}

/**
 * Render a comment component
 * @param array $comment - Comment data
 */
function renderComment($comment) {
    ?>
    <div class="comment mb-2" data-comment-id="<?php echo $comment['id']; ?>">
        <div class="d-flex">
            <img src="assets/images/<?php echo htmlspecialchars($comment['profile_picture']); ?>"
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
    <?php
}

/**
 * Render user profile sidebar
 * @param array $user - User data
 */
function renderUserSidebar($user) {
    if (!$user) return;
    ?>
    <div class="card mb-4">
        <div class="card-body text-center">
            <img src="assets/images/<?php echo htmlspecialchars($user['profile_picture']); ?>"
                 alt="Profile" class="profile-img-small mb-2">
            <h6><?php echo htmlspecialchars($user['username']); ?></h6>
            <p class="text-muted small"><?php echo htmlspecialchars($user['bio'] ?? ''); ?></p>
            <?php if ($user['user_level'] == 2): ?>
                <span class="badge bg-warning text-dark">
                    <i class="fas fa-shield-alt"></i> Moderator
                </span>
            <?php endif; ?>
        </div>
    </div>
    <?php
}

/**
 * Render trending topics sidebar
 * @param array $topics - Array of trending topics
 */
function renderTrendingTopics($topics = null) {
    if (!$topics) {
        $topics = ['#BeachLife', '#Photography', '#Nature', '#Sunset', '#Hiking'];
    }
    ?>
    <div class="card">
        <div class="card-header">
            <h6><i class="fas fa-fire"></i> Trending Topics</h6>
        </div>
        <div class="card-body">
            <?php foreach ($topics as $topic): ?>
                <div class="trending-topic"><?php echo htmlspecialchars($topic); ?></div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php
}

/**
 * Render suggested users sidebar
 * @param object $pdo - Database connection
 * @param int $limit - Number of users to show
 */
function renderSuggestedUsers($pdo, $limit = 5) {
    $stmt = $pdo->prepare("SELECT * FROM users ORDER BY created_at DESC LIMIT ?");
    $stmt->execute([$limit]);
    $suggested_users = $stmt->fetchAll();
    ?>
    <div class="card">
        <div class="card-header">
            <h6><i class="fas fa-users"></i> Suggested Users</h6>
        </div>
        <div class="card-body">
            <?php foreach ($suggested_users as $user): ?>
                <div class="d-flex align-items-center mb-3">
                    <img src="assets/images/<?php echo htmlspecialchars($user['profile_picture']); ?>"
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
    <?php
}

/**
 * Render create post form
 * @param array $user - Current user data
 */
function renderCreatePostForm($user) {
    if (!isLoggedIn() || !$user) return;
    ?>
    <div class="card mb-4">
        <div class="card-body">
            <form action="create_post.php" method="POST" enctype="multipart/form-data">
                <div class="mb-3">
                    <textarea class="form-control" name="content"
                              placeholder="What's on your mind, <?php echo htmlspecialchars($user['username']); ?>?"
                              rows="3" required maxlength="1000"></textarea>
                </div>
                <div class="mb-3">
                    <input type="file" class="form-control" name="image" accept="image/*">
                    <div class="form-text">
                        <i class="fas fa-info-circle"></i> Supported formats: JPEG, PNG, GIF, WebP (Max 5MB)
                    </div>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-share"></i> Share Post
                    </button>
                    <div class="text-muted small">
                        <i class="fas fa-users"></i> Public post
                    </div>
                </div>
            </form>
        </div>
    </div>
    <?php
}

/**
 * Render loading placeholder
 * @param string $message - Loading message
 */
function renderLoadingPlaceholder($message = 'Loading...') {
    ?>
    <div class="text-center py-4">
        <div class="loading mb-2"></div>
        <p class="text-muted"><?php echo htmlspecialchars($message); ?></p>
    </div>
    <?php
}

/**
 * Render empty state
 * @param string $title - Empty state title
 * @param string $description - Empty state description
 * @param string $icon - FontAwesome icon class
 * @param string $actionText - Action button text
 * @param string $actionUrl - Action button URL
 */
function renderEmptyState($title, $description, $icon = 'fa-inbox', $actionText = null, $actionUrl = null) {
    ?>
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="fas <?php echo $icon; ?> fa-3x text-muted mb-3"></i>
            <h5 class="text-muted"><?php echo htmlspecialchars($title); ?></h5>
            <p class="text-muted"><?php echo htmlspecialchars($description); ?></p>
            <?php if ($actionText && $actionUrl): ?>
                <a href="<?php echo htmlspecialchars($actionUrl); ?>" class="btn btn-primary">
                    <?php echo htmlspecialchars($actionText); ?>
                </a>
            <?php endif; ?>
        </div>
    </div>
    <?php
}

/**
 * Render breadcrumb navigation
 * @param array $breadcrumbs - Array of breadcrumb items ['title' => 'url']
 */
function renderBreadcrumbs($breadcrumbs) {
    if (empty($breadcrumbs)) return;
    ?>
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="index.php"><i class="fas fa-home"></i> Home</a>
            </li>
            <?php
            $items = array_keys($breadcrumbs);
            $lastItem = end($items);
            foreach ($breadcrumbs as $title => $url):
                ?>
                <li class="breadcrumb-item <?php echo $title === $lastItem ? 'active' : ''; ?>">
                    <?php if ($title === $lastItem): ?>
                        <?php echo htmlspecialchars($title); ?>
                    <?php else: ?>
                        <a href="<?php echo htmlspecialchars($url); ?>"><?php echo htmlspecialchars($title); ?></a>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ol>
    </nav>
    <?php
}
?>