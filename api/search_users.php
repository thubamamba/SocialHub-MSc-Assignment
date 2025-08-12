<?php
require_once '../config/config.php';

// Ensure $pdo is available globally
global $pdo;

// Set content type to JSON
header('Content-Type: application/json');

// Check if request method is GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Get search query
$query = isset($_GET['q']) ? trim($_GET['q']) : '';

if (empty($query)) {
    echo json_encode(['success' => false, 'message' => 'Search query cannot be empty']);
    exit;
}

if (strlen($query) < 2) {
    echo json_encode(['success' => false, 'message' => 'Search query must be at least 2 characters long']);
    exit;
}

try {
    // Search for users by username or bio
    $search_term = '%' . $query . '%';
    $stmt = $pdo->prepare("
        SELECT id, username, bio, profile_picture, user_level, created_at
        FROM " . getTableName('users') . " 
        WHERE username LIKE ? OR bio LIKE ?
        ORDER BY 
            CASE 
                WHEN username LIKE ? THEN 1 
                ELSE 2 
            END,
            username
        LIMIT 20
    ");
    $stmt->execute([$search_term, $search_term, $query . '%']);
    $users = $stmt->fetchAll();

    // Format results
    $formatted_users = [];
    foreach ($users as $user) {
        $formatted_users[] = [
            'id' => $user['id'],
            'username' => htmlspecialchars($user['username']),
            'bio' => htmlspecialchars($user['bio'] ?? ''),
            'profile_picture' => htmlspecialchars($user['profile_picture']),
            'user_level' => $user['user_level'],
            'created_at' => $user['created_at'],
            'user_type' => $user['user_level'] == 2 ? 'Moderator' : 'Member'
        ];
    }

    echo json_encode([
        'success' => true,
        'users' => $formatted_users,
        'count' => count($formatted_users),
        'query' => htmlspecialchars($query)
    ]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Search failed']);
}
