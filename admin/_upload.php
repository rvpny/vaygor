<?php
/**
 * Helper upload + path gambar: dipakai fields.php & categories.php.
 */

/**
 * Cari lokasi file gambar yang benar di beberapa folder (assets/uploads,
 * assets/images, uploads) dan kembalikan path relatif dari root proyek.
 */
function resolve_image(string $image): ?string
{
    $candidates = [];
    if (str_starts_with($image, 'assets/')) {
        $candidates[] = $image;
    } elseif (str_starts_with($image, 'uploads/')) {
        $candidates[] = $image;
        $candidates[] = 'assets/' . $image;
        $candidates[] = 'assets/images/' . substr($image, 8);
    } else {
        $candidates[] = 'assets/uploads/' . $image;
        $candidates[] = 'assets/images/' . $image;
        $candidates[] = 'uploads/' . $image;
    }
    $root = dirname(__DIR__);
    foreach ($candidates as $rel) {
        if (is_file($root . '/' . $rel)) {
            return $rel;
        }
    }
    return null;
}

function image_src(?string $image, string $base): ?string
{
    if (!$image) {
        return null;
    }
    $rel = resolve_image($image);
    return $rel === null ? null : rtrim($base, '/') . '/' . $rel;
}

function delete_upload(?string $image): void
{
    if (!$image) {
        return;
    }
    $rel = resolve_image($image);
    if ($rel !== null) {
        @unlink(dirname(__DIR__) . '/' . $rel);
    }
}

function save_upload(array &$errors, string $subdir = 'assets/uploads/'): ?string
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
    return $name;
}