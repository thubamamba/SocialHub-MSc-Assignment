<?php
require_once '../config/config.php';

// Ensure $pdo is available globally
global $pdo;

// Set content type to JSON
header('Content-Type: application/json');

// Check if user is logged in
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'You must be logged in to delete comments']);
    exit;
}

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);
$comment_id = isset($input['comment_id']) ? intval($input['comment_id']) : 0;

if (!$comment_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid comment ID']);
    exit;
}

$user_id = $_SESSION['user_id'];
$user_level = $_SESSION['user_level'];

try {
    // Get comment info to check ownership
    $stmt = $pdo->prepare("SELECT user_id FROM comments WHERE id = ?");
    $stmt->execute([$comment_id]);
    $comment = $stmt->fetch();

    if (!$comment) {
        echo json_encode(['success' => false, 'message' => 'Comment not found']);
        exit;
    }

    // Check if user can delete this comment (owner or moderator)
    if ($comment['user_id'] != $user_id && $user_level != 2) {
        echo json_encode(['success' => false, 'message' => 'You do not have permission to delete this comment']);
        exit;
    }

    // Delete the comment
    $stmt = $pdo->prepare("DELETE FROM comments WHERE id = ?");
    $stmt->execute([$comment_id]);

    if ($stmt->rowCount() > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'Comment deleted successfully'
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete comment']);
    }

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error occurred']);
}
