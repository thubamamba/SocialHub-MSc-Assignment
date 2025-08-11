<?php
require_once 'config/config.php';

// Check if user is logged in
if (!isLoggedIn()) {
    header('Location: index.php');
    exit;
}

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$content = isset($_POST['content']) ? trim($_POST['content']) : '';
$errors = [];

// Validation
if (empty($content)) {
    $errors[] = "Post content cannot be empty.";
}

if (strlen($content) > 1000) {
    $errors[] = "Post content is too long (maximum 1000 characters).";
}

// Handle image upload
$image_url = null;
if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $file_type = $_FILES['image']['type'];
    $file_size = $_FILES['image']['size'];
    $max_file_size = 5 * 1024 * 1024; // 5MB

    // Validate file type
    if (!in_array($file_type, $allowed_types)) {
        $errors[] = "Invalid file type. Please use JPEG, PNG, GIF, or WebP.";
    }

    // Validate file size
    if ($file_size > $max_file_size) {
        $errors[] = "File is too large. Maximum size is 5MB.";
    }

    // Process upload if no errors
    if (empty($errors)) {
        $extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $image_url = 'post_' . uniqid() . '.' . strtolower($extension);
        $upload_path = 'assets/uploads/' . $image_url;

        // Create directory if it doesn't exist
        $upload_dir = dirname($upload_path);
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        if (!move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
            $errors[] = "Failed to upload image.";
            $image_url = null;
        } else {
            // Resize image if it's too large
            resizeImage($upload_path, 1200, 1200);
        }
    }
}

// Insert post if no errors
if (empty($errors)) {
    $sanitized_content = sanitizeInput($content);

    try {
        $stmt = $pdo->prepare("INSERT INTO posts (user_id, content, image_url) VALUES (?, ?, ?)");
        $stmt->execute([$user_id, $sanitized_content, $image_url]);

        // Set success message in session
        $_SESSION['success_message'] = "Post shared successfully!";

    } catch (PDOException $e) {
        $errors[] = "Failed to create post. Please try again.";

        // Delete uploaded image if database insert failed
        if ($image_url && file_exists('assets/uploads/' . $image_url)) {
            unlink('assets/uploads/' . $image_url);
        }
    }
}

// Set error messages in session if any
if (!empty($errors)) {
    $_SESSION['error_messages'] = $errors;
}

// Redirect back to the referring page or home
$redirect_url = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'index.php';
header('Location: ' . $redirect_url);
exit;

/**
 * Resize image to fit within specified dimensions while maintaining aspect ratio
 */
function resizeImage($file_path, $max_width, $max_height) {
    // Get image info
    $image_info = getimagesize($file_path);
    if (!$image_info) {
        return false;
    }

    $width = $image_info[0];
    $height = $image_info[1];
    $type = $image_info[2];

    // Check if resize is needed
    if ($width <= $max_width && $height <= $max_height) {
        return true;
    }

    // Calculate new dimensions
    $ratio = min($max_width / $width, $max_height / $height);
    $new_width = round($width * $ratio);
    $new_height = round($height * $ratio);

    // Create source image
    switch ($type) {
        case IMAGETYPE_JPEG:
            $source = imagecreatefromjpeg($file_path);
            break;
        case IMAGETYPE_PNG:
            $source = imagecreatefrompng($file_path);
            break;
        case IMAGETYPE_GIF:
            $source = imagecreatefromgif($file_path);
            break;
        case IMAGETYPE_WEBP:
            $source = imagecreatefromwebp($file_path);
            break;
        default:
            return false;
    }

    if (!$source) {
        return false;
    }

    // Create resized image
    $resized = imagecreatetruecolor($new_width, $new_height);

    // Preserve transparency for PNG and GIF
    if ($type == IMAGETYPE_PNG || $type == IMAGETYPE_GIF) {
        imagealphablending($resized, false);
        imagesavealpha($resized, true);
        $transparent = imagecolorallocatealpha($resized, 255, 255, 255, 127);
        imagefilledrectangle($resized, 0, 0, $new_width, $new_height, $transparent);
    }

    // Resize image
    imagecopyresampled($resized, $source, 0, 0, 0, 0, $new_width, $new_height, $width, $height);

    // Save resized image
    $result = false;
    switch ($type) {
        case IMAGETYPE_JPEG:
            $result = imagejpeg($resized, $file_path, 85);
            break;
        case IMAGETYPE_PNG:
            $result = imagepng($resized, $file_path, 8);
            break;
        case IMAGETYPE_GIF:
            $result = imagegif($resized, $file_path);
            break;
        case IMAGETYPE_WEBP:
            $result = imagewebp($resized, $file_path, 85);
            break;
    }

    // Clean up memory
    imagedestroy($source);
    imagedestroy($resized);

    return $result;
}
?>