<?php
require_once '../config/config.php';

// Ensure $pdo is available globally
global $pdo;

// Set content type to JSON
header('Content-Type: application/json');

// Check if user is logged in
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'You must be logged in to comment']);
    exit;
}

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);
$post_id = isset($input['post_id']) ? intval($input['post_id']) : 0;
$content = isset($input['content']) ? trim($input['content']) : '';

// Validation
if (!$post_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid post ID']);
    exit;
}

if (empty($content)) {
    echo json_encode(['success' => false, 'message' => 'Comment cannot be empty']);
    exit;
}

if (strlen($content) > 500) {
    echo json_encode(['success' => false, 'message' => 'Comment is too long (max 500 characters)']);
    exit;
}

// Check if post exists
$stmt = $pdo->prepare("SELECT id FROM " . getTableName('posts') . " WHERE id = ?");
$stmt->execute([$post_id]);
if (!$stmt->fetch()) {
    echo json_encode(['success' => false, 'message' => 'Post not found']);
    exit;
}

$user_id = $_SESSION['user_id'];
$sanitized_content = sanitizeInput($content);

try {
    // Insert comment
    $stmt = $pdo->prepare("INSERT INTO " . getTableName('comments') . " (post_id, user_id, content) VALUES (?, ?, ?)");
    $stmt->execute([$post_id, $user_id, $sanitized_content]);

    $comment_id = $pdo->lastInsertId();

    // Get comment data with user info
    $stmt = $pdo->prepare("
        SELECT c.*, u.username, u.profile_picture 
        FROM " . getTableName('comments') . " c 
        JOIN " . getTableName('users') . " u ON c.user_id = u.id 
        WHERE c.id = ?
    ");
    $stmt->execute([$comment_id]);
    $comment = $stmt->fetch();

    // Check if current user can delete this comment
    $current_user = getCurrentUser($pdo);
    $can_delete = $current_user && ($current_user['user_level'] == 2 || $current_user['id'] == $comment['user_id']);

    echo json_encode([
        'success' => true,
        'message' => 'Comment added successfully',
        'comment' => [
            'id' => $comment['id'],
            'content' => htmlspecialchars($comment['content']),
            'username' => htmlspecialchars($comment['username']),
            'profile_picture' => htmlspecialchars($comment['profile_picture']),
            'created_at' => $comment['created_at'],
            'can_delete' => $can_delete
        ]
    ]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Failed to add comment']);
}
