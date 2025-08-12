<?php
require_once '../config/config.php';

// Ensure $pdo is available globally
global $pdo;

// Set content type to JSON
header('Content-Type: application/json');

// Check if user is logged in
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'You must be logged in to like posts']);
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

if (!$post_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid post ID']);
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

try {
    // Check if user already liked this post
    $stmt = $pdo->prepare("SELECT id FROM " . getTableName('likes') . " WHERE post_id = ? AND user_id = ?");
    $stmt->execute([$post_id, $user_id]);
    $existing_like = $stmt->fetch();

    if ($existing_like) {
        // Unlike the post
        $stmt = $pdo->prepare("DELETE FROM " . getTableName('likes') . " WHERE post_id = ? AND user_id = ?");
        $stmt->execute([$post_id, $user_id]);

        // Update likes count in posts table
        $stmt = $pdo->prepare("UPDATE " . getTableName('posts') . " SET likes_count = likes_count - 1 WHERE id = ?");
        $stmt->execute([$post_id]);

        $liked = false;
    } else {
        // Like the post
        $stmt = $pdo->prepare("INSERT INTO " . getTableName('likes') . " (post_id, user_id) VALUES (?, ?)");
        $stmt->execute([$post_id, $user_id]);

        // Update likes count in posts table
        $stmt = $pdo->prepare("UPDATE " . getTableName('posts') . " SET likes_count = likes_count + 1 WHERE id = ?");
        $stmt->execute([$post_id]);

        $liked = true;
    }

    // Get updated likes count
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM " . getTableName('likes') . " WHERE post_id = ?");
    $stmt->execute([$post_id]);
    $likes_count = $stmt->fetch()['count'];

    echo json_encode([
        'success' => true,
        'liked' => $liked,
        'likes_count' => $likes_count
    ]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error occurred']);
}
