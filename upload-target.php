<?php
declare(strict_types=1);

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['targetImage'])) {
    $file = $_FILES['targetImage'];
    if ($file['error'] === UPLOAD_ERR_OK) {
        if ($file['size'] > 12 * 1024 * 1024) {
            $error = 'File size exceeds 12 MB limit.';
        } else {
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime = $finfo->file($file['tmp_name']);
            $allowed = ['image/jpeg', 'image/png', 'image/webp'];
            if (!in_array($mime, $allowed, true)) {
                $error = 'Invalid image format. Only JPG, PNG, and WEBP are allowed.';
            } else {
                $targetDir = __DIR__ . '/assets/targets/';
                if (!is_dir($targetDir)) {
                    mkdir($targetDir, 0755, true);
                }
                $destination = $targetDir . 'picture.jpg';
                if (move_uploaded_file($file['tmp_name'], $destination)) {
                    if (isset($_POST['compile_after']) && $_POST['compile_after'] === '1') {
                        header('Location: compile-target.php');
                        exit;
                    } else {
                        header('Location: index.php?uploaded=1');
                        exit;
                    }
                } else {
                    $error = 'Failed to move uploaded file.';
                }
            }
        }
    } else {
        $error = 'Upload error code: ' . $file['error'];
    }
}