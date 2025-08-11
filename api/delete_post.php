<?php
require_once '../config/config.php';

// Set content type to JSON
header('Content-Type: application/json');

// Check if user is logged in
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'You must be logged in to delete posts']);
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

$user_id = $_SESSION['user_id'];
$user_level = $_SESSION['user_level'];

try {
    // Get post info to check ownership and get image path
    $stmt = $pdo->prepare("SELECT user_id, image_url FROM posts WHERE id = ?");
    $stmt->execute([$post_id]);
    $post = $stmt->fetch();

    if (!$post) {
        echo json_encode(['success' => false, 'message' => 'Post not found']);
        exit;
    }

    // Check if user can delete this post (owner or moderator)
    if ($post['user_id'] != $user_id && $user_level != 2) {
        echo json_encode(['success' => false, 'message' => 'You do not have permission to delete this post']);
        exit;
    }

    // Start transaction
    $pdo->beginTransaction();

    // Delete related likes first
    $stmt = $pdo->prepare("DELETE FROM likes WHERE post_id = ?");
    $stmt->execute([$post_id]);

    // Delete related comments
    $stmt = $pdo->prepare("DELETE FROM comments WHERE post_id = ?");
    $stmt->execute([$post_id]);

    // Delete the post
    $stmt = $pdo->prepare("DELETE FROM posts WHERE id = ?");
    $stmt->execute([$post_id]);

    if ($stmt->rowCount() > 0) {
        // Delete associated image file if it exists and is not default
        if ($post['image_url'] && $post['image_url'] !== 'default.jpg') {
            $image_path = '../assets/images/' . $post['image_url'];
            if (file_exists($image_path)) {
                unlink($image_path);
            }
        }

        // Commit transaction
        $pdo->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Post deleted successfully'
        ]);
    } else {
        // Rollback transaction
        $pdo->rollback();
        echo json_encode(['success' => false, 'message' => 'Failed to delete post']);
    }

} catch (PDOException $e) {
    // Rollback transaction
    $pdo->rollback();
    echo json_encode(['success' => false, 'message' => 'Database error occurred']);
}
?>