<?php
require_once 'config/config.php';

// Ensure $pdo is available globally
global $pdo;

// Check if user is logged in
if (!isLoggedIn()) {
    header('Location: index.php');
    exit;
}

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: profile.php?id=' . $_SESSION['user_id']);
    exit;
}

$user_id = $_SESSION['user_id'];
$bio = isset($_POST['bio']) ? trim($_POST['bio']) : '';
$errors = [];

// Validation
if (strlen($bio) > 500) {
    $errors[] = "Bio is too long (maximum 500 characters).";
}

// Get current user data
$stmt = $pdo->prepare("SELECT profile_picture FROM " . getTableName('users') . " WHERE id = ?");
$stmt->execute([$user_id]);
$current_user = $stmt->fetch();

if (!$current_user) {
    $errors[] = "User not found.";
}

$profile_picture = $current_user['profile_picture'];

// Handle profile picture upload
if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $file_type = $_FILES['profile_picture']['type'];
    $file_size = $_FILES['profile_picture']['size'];
    $max_file_size = 2 * 1024 * 1024; // 2MB

    // Validate file type
    if (!in_array($file_type, $allowed_types)) {
        $errors[] = "Invalid file type for profile picture. Please use JPEG, PNG, GIF, or WebP.";
    }

    // Validate file size
    if ($file_size > $max_file_size) {
        $errors[] = "Profile picture is too large. Maximum size is 2MB.";
    }

    // Process upload if no errors
    if (empty($errors)) {
        $extension = pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION);
        $new_profile_picture = 'profile_' . $user_id . '_' . uniqid() . '.' . strtolower($extension);
        $upload_path = 'assets/uploads/' . $new_profile_picture;

        // Create directory if it doesn't exist
        $upload_dir = dirname($upload_path);
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $upload_path)) {
            // Resize profile picture to a square
            if (resizeProfilePicture($upload_path, 300)) {
                // Delete old profile picture if it's not the default
                if ($profile_picture && $profile_picture !== 'default.jpg') {
                    $old_path = 'assets/uploads/' . $profile_picture;
                    if (file_exists($old_path)) {
                        unlink($old_path);
                    }
                }
                $profile_picture = $new_profile_picture;
            } else {
                $errors[] = "Failed to process profile picture.";
                // Delete uploaded file if processing failed
                if (file_exists($upload_path)) {
                    unlink($upload_path);
                }
            }
        } else {
            $errors[] = "Failed to upload profile picture.";
        }
    }
}

// Update profile if no errors
if (empty($errors)) {
    $sanitized_bio = sanitizeInput($bio);

    try {
        $stmt = $pdo->prepare("UPDATE " . getTableName('users') . " SET bio = ?, profile_picture = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
        $stmt->execute([$sanitized_bio, $profile_picture, $user_id]);

        // Set success message in session
        $_SESSION['success_message'] = "Profile updated successfully!";

    } catch (PDOException $e) {
        $errors[] = "Failed to update profile. Please try again.";
    }
}

// Set error messages in session if any
if (!empty($errors)) {
    $_SESSION['error_messages'] = $errors;
}

// Redirect back to profile page
header('Location: profile.php?id=' . $user_id);
exit;

/**
 * Resize and crop profile picture to a square
 */
function resizeProfilePicture($file_path, $size) {
    // Get image info
    $image_info = getimagesize($file_path);
    if (!$image_info) {
        return false;
    }

    $width = $image_info[0];
    $height = $image_info[1];
    $type = $image_info[2];

    // Create source image
    $source = createImageFromType($type, $file_path);

    if (!$source) {
        return false;
    }

    // Calculate crop dimensions for square
    $crop_size = min($width, $height);
    $crop_x = ($width - $crop_size) / 2;
    $crop_y = ($height - $crop_size) / 2;

    // Create square canvas
    $square = imagecreatetruecolor($size, $size);

    // Preserve transparency for PNG and GIF
    if ($type == IMAGETYPE_PNG || $type == IMAGETYPE_GIF) {
        imagealphablending($square, false);
        imagesavealpha($square, true);
        $transparent = imagecolorallocatealpha($square, 255, 255, 255, 127);
        imagefilledrectangle($square, 0, 0, $size, $size, $transparent);
    }

    // Crop and resize to square
    imagecopyresampled($square, $source, 0, 0, $crop_x, $crop_y, $size, $size, $crop_size, $crop_size);

    // Save processed image
    $result = false;
    switch ($type) {
        case IMAGETYPE_JPEG:
            $result = imagejpeg($square, $file_path, 90);
            break;
        case IMAGETYPE_PNG:
            $result = imagepng($square, $file_path, 8);
            break;
        case IMAGETYPE_GIF:
            $result = imagegif($square, $file_path);
            break;
        case IMAGETYPE_WEBP:
            $result = imagewebp($square, $file_path, 90);
            break;
    }

    // Clean up memory
    imagedestroy($source);
    imagedestroy($square);

    return $result;
}
