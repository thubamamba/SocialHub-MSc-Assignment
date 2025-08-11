<?php
/**
 * Helper functions - improve code quality and maintainability
 */
function createImageFromType($type, $file_path) {
    $image_functions = [
        IMAGETYPE_JPEG => 'imagecreatefromjpeg',
        IMAGETYPE_PNG => 'imagecreatefrompng',
        IMAGETYPE_GIF => 'imagecreatefromgif',
        IMAGETYPE_WEBP => 'imagecreatefromwebp'
    ];

    if (!isset($image_functions[$type])) {
        return false;
    }

    $source = $image_functions[$type]($file_path);

    if (!$source) {
        return false;
    }

    return $source;
}
