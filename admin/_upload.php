<?php
/**
 * Helper upload + path gambar: dipakai fields.php & categories.php.
 */

function image_src(?string $image, string $base): ?string
{
    if (!$image) {
        return null;
    }
    $relative = str_starts_with($image, 'uploads/') || str_starts_with($image, 'assets/')
        ? $image
        : 'assets/images/' . $image;
    return is_file(dirname(__DIR__) . '/' . $relative) ? $base . '/' . $relative : null;
}

function delete_upload(?string $image): void
{
    if (!$image) {
        return;
    }
    $relative = str_starts_with($image, 'uploads/') || str_starts_with($image, 'assets/')
        ? $image
        : 'assets/images/' . $image;
    $file = dirname(__DIR__) . '/' . $relative;
    if (is_file($file)) {
        @unlink($file);
    }
}

function save_upload(array &$errors, string $subdir = 'uploads/'): ?string
{
    if (empty($_FILES['image']['name'])) {
        return null;
    }
    $file = $_FILES['image'];
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        $errors[] = 'Upload gambar gagal.';
        return null;
    }
    if ($file['size'] > 2 * 1024 * 1024) {
        $errors[] = 'Ukuran gambar maksimal 2MB.';
        return null;
    }
    $info   = @getimagesize($file['tmp_name']);
    $extMap = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    if (!$info || !isset($extMap[$info['mime']])) {
        $errors[] = 'Format gambar harus JPG, PNG, atau WEBP.';
        return null;
    }
    $name = bin2hex(random_bytes(16)) . '.' . $extMap[$info['mime']];
    $dir  = dirname(__DIR__) . '/' . $subdir;
    if (!is_dir($dir) && !@mkdir($dir, 0777, true)) {
        $errors[] = 'Gagal menyimpan gambar (folder tidak tersedia).';
        return null;
    }
    if (!move_uploaded_file($file['tmp_name'], $dir . $name)) {
        $errors[] = 'Gagal menyimpan gambar.';
        return null;
    }
    return $subdir . $name;
}