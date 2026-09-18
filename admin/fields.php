<?php
require_once __DIR__ . '/../config/helpers.php';
require_role('admin');

$pdo = db();

$fieldTypes = ['indoor' => 'Indoor', 'outdoor' => 'Outdoor'];
$statuses   = ['available' => 'Tersedia', 'maintenance' => 'Perawatan', 'inactive' => 'Nonaktif'];
$categories = ['futsal', 'mini soccer', 'badminton', 'basket', 'volley', 'tennis'];

function field_image_src(?string $image, string $base): ?string
{
    if (!$image) {
        return null;
    }
    $relative = str_starts_with($image, 'uploads/') ? $image : 'assets/images/' . $image;
    return is_file(dirname(__DIR__) . '/' . $relative) ? $base . '/' . $relative : null;
}

function delete_upload(?string $image): void
{
    if ($image && str_starts_with($image, 'uploads/')) {
        $file = dirname(__DIR__) . '/' . $image;
        if (is_file($file)) {
            @unlink($file);
        }
    }
}

function save_upload(array &$errors): ?string
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
    if (!move_uploaded_file($file['tmp_name'], dirname(__DIR__) . '/uploads/' . $name)) {
        $errors[] = 'Gagal menyimpan gambar.';
        return null;
    }
    return 'uploads/' . $name;
}

$action = (string) ($_GET['action'] ?? 'list');
$editId = (int) ($_GET['edit'] ?? 0);
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $op = (string) ($_POST['op'] ?? '');

    if ($op === 'delete') {
        $id   = (int) ($_POST['id'] ?? 0);
        $stmt = $pdo->prepare('SELECT image FROM fields WHERE id = ?');
        $stmt->execute([$id]);
        $image = $stmt->fetchColumn();
        if ($image !== false) {
            $pdo->prepare('DELETE FROM fields WHERE id = ?')->execute([$id]);
            delete_upload($image ?: null);
            flash_set('ok', 'Lapangan dihapus.');
        } else {
            flash_set('err', 'Lapangan tidak ditemukan.');
        }
        redirect('fields.php');
    }

    if ($op === 'save') {
        $id        = (int) ($_POST['id'] ?? 0);
        $name      = trim((string) ($_POST['name'] ?? ''));
        $location  = trim((string) ($_POST['location'] ?? ''));
        $category  = trim((string) ($_POST['category'] ?? ''));
        $surface   = trim((string) ($_POST['surface'] ?? ''));
        $type      = (string) ($_POST['field_type'] ?? '');
        $status    = (string) ($_POST['status'] ?? '');
        $capacity  = (int) ($_POST['capacity'] ?? 0);
        $price     = (string) ($_POST['price_per_hour'] ?? '0');
        $desc      = trim((string) ($_POST['description'] ?? ''));

        if ($name === '') {
            $errors[] = 'Nama lapangan wajib diisi.';
        } elseif (mb_strlen($name) > 100) {
            $errors[] = 'Nama lapangan maksimal 100 karakter.';
        }
        if ($location === '') {
            $errors[] = 'Lokasi wajib diisi.';
        } elseif (mb_strlen($location) > 255) {
            $errors[] = 'Lokasi maksimal 255 karakter.';
        }
        if (!isset($fieldTypes[$type])) {
            $errors[] = 'Tipe lapangan tidak valid.';
        }
        if (!isset($statuses[$status])) {
            $errors[] = 'Status tidak valid.';
        }
        if ($category !== '' && mb_strlen($category) > 50) {
            $errors[] = 'Kategori maksimal 50 karakter.';
        }
        if (!is_numeric($price) || (float) $price < 0) {
            $errors[] = 'Harga per jam harus angka tidak negatif.';
        }
        if ($capacity < 0) {
            $errors[] = 'Kapasitas tidak boleh negatif.';
        }

        $newImage = $errors ? null : save_upload($errors);

        if (!$errors) {
            $oldImage = null;
            if ($id > 0) {
                $stmt = $pdo->prepare('SELECT image FROM fields WHERE id = ?');
                $stmt->execute([$id]);
                $oldImage = $stmt->fetchColumn();
                if ($oldImage === false) {
                    $errors[] = 'Lapangan tidak ditemukan.';
                }
            }

            if (!$errors) {
                $params = [
                    $name,
                    $location,
                    $desc !== '' ? $desc : null,
                    $type,
                    $category !== '' ? $category : null,
                    $surface !== '' ? $surface : null,
                    $capacity,
                    number_format((float) $price, 2, '.', ''),
                    $status,
                ];

                if ($id > 0) {
                    $sql = 'UPDATE fields SET name = ?, location = ?, description = ?, field_type = ?, category = ?,
                            surface = ?, capacity = ?, price_per_hour = ?, status = ?';
                    if ($newImage) {
                        $sql     .= ', image = ?';
                        $params[] = $newImage;
                    }
                    $sql     .= ' WHERE id = ?';
                    $params[] = $id;
                    $pdo->prepare($sql)->execute($params);
                    if ($newImage) {
                        delete_upload($oldImage ?: null);
                    }
                } else {
                    $params[] = $newImage;
                    $pdo->prepare(
                        'INSERT INTO fields (name, location, description, field_type, category, surface,
                         capacity, price_per_hour, status, image) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
                    )->execute($params);
                }
                flash_set('ok', 'Lapangan berhasil disimpan.');
                redirect('fields.php');
            }
        }

        $action = 'form';
        $editId = $id;
    }
}

$form = [
    'id'             => 0,
    'name'           => '',
    'location'       => '',
    'description'    => '',
    'field_type'     => 'indoor',
    'category'       => '',
    'surface'        => '',
    'capacity'       => 10,
    'price_per_hour' => '',
    'status'         => 'available',
    'image'          => null,
];

if ($action === 'form') {
    if ($editId > 0) {
        $stmt = $pdo->prepare('SELECT * FROM fields WHERE id = ?');
        $stmt->execute([$editId]);
        $row = $stmt->fetch();
        if ($row) {
            $form = array_merge($form, $row);
        } else {
            $action = 'list';
            flash_set('err', 'Lapangan tidak ditemukan.');
        }
    }
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $form = array_merge($form, [
            'id'             => $editId,
            'name'           => $name ?? '',
            'location'       => $location ?? '',
            'description'    => $desc ?? '',
            'field_type'     => $type ?? 'indoor',
            'category'       => $category ?? '',
            'surface'        => $surface ?? '',
            'capacity'       => $capacity ?? 10,
            'price_per_hour' => $price ?? '',
            'status'         => $status ?? 'available',
        ]);
    }
}

$base      = '..';
$pageTitle = 'Kelola Lapangan - Admin VAYGOR';
require __DIR__ . '/../includes/head.php';
?>
<div class="admin-shell">

  <?php $adminNav = 'lapangan'; require __DIR__ . '/_sidebar.php'; ?>

  <main class="admin-main">
    <?php if ($action === 'form'): ?>

      <header class="admin-topbar">
        <div>
          <h1 class="admin-page-title"><?= $form['id'] > 0 ? 'Edit Lapangan' : 'Tambah Lapangan' ?></h1>
          <p class="admin-page-sub">Data lapangan yang dijual di VAYGOR</p>
        </div>
        <a class="admin-btn-ghost" href="fields.php">&larr; Kembali</a>
      </header>

      <?php if ($errors): ?>
        <div class="admin-flash is-err">
          <?php foreach ($errors as $i => $msg): ?>
            <?= $i > 0 ? '<br>' : '' ?><?= e($msg) ?>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <section class="admin-panel">
        <form class="admin-form" method="post" enctype="multipart/form-data">
          <?= csrf_field() ?>
          <input type="hidden" name="op" value="save">
          <input type="hidden" name="id" value="<?= (int) $form['id'] ?>">

          <div class="admin-form-grid">
            <div class="admin-field">
              <label for="name">Nama Lapangan</label>
              <input class="admin-input" id="name" type="text" name="name" value="<?= e($form['name']) ?>" required>
            </div>
            <div class="admin-field">
              <label for="category">Kategori</label>
              <input class="admin-input" id="category" type="text" name="category" list="categoryOptions"
                     value="<?= e($form['category']) ?>" placeholder="futsal / badminton / basket">
              <datalist id="categoryOptions">
                <?php foreach ($categories as $c): ?>
                  <option value="<?= e($c) ?>"></option>
                <?php endforeach; ?>
              </datalist>
            </div>
            <div class="admin-field">
              <label for="field_type">Tipe</label>
              <select class="admin-input" id="field_type" name="field_type">
                <?php foreach ($fieldTypes as $val => $label): ?>
                  <option value="<?= e($val) ?>"<?= $form['field_type'] === $val ? ' selected' : '' ?>><?= e($label) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="admin-field">
              <label for="status">Status</label>
              <select class="admin-input" id="status" name="status">
                <?php foreach ($statuses as $val => $label): ?>
                  <option value="<?= e($val) ?>"<?= $form['status'] === $val ? ' selected' : '' ?>><?= e($label) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="admin-field">
              <label for="price_per_hour">Harga per Jam (Rp)</label>
              <input class="admin-input" id="price_per_hour" type="number" name="price_per_hour" min="0" step="1000"
                     value="<?= e($form['price_per_hour']) ?>" required>
            </div>
            <div class="admin-field">
              <label for="capacity">Kapasitas</label>
              <input class="admin-input" id="capacity" type="number" name="capacity" min="0"
                     value="<?= e($form['capacity']) ?>">
            </div>
            <div class="admin-field">
              <label for="surface">Permukaan</label>
              <input class="admin-input" id="surface" type="text" name="surface" value="<?= e($form['surface']) ?>" placeholder="Vinyl / Rumput / Semen">
            </div>
            <div class="admin-field">
              <label for="location">Lokasi</label>
              <input class="admin-input" id="location" type="text" name="location" value="<?= e($form['location']) ?>" required>
            </div>
            <div class="admin-field full">
              <label for="description">Deskripsi</label>
              <textarea class="admin-textarea" id="description" name="description"><?= e($form['description']) ?></textarea>
            </div>
            <div class="admin-field full">
              <label for="image">Gambar (JPG/PNG/WEBP, maks 2MB)</label>
              <input class="admin-input" id="image" type="file" name="image" accept="image/jpeg,image/png,image/webp">
              <?php $current = field_image_src($form['image'], '..'); ?>
              <?php if ($current): ?>
                <div class="admin-current-img">
                  <img class="admin-thumb" src="<?= e($current) ?>" alt="">
                  <span>Gambar saat ini</span>
                </div>
              <?php elseif (!empty($form['image'])): ?>
                <span class="admin-help">Gambar tercatat: <?= e($form['image']) ?> (file tidak ditemukan)</span>
              <?php endif; ?>
            </div>
          </div>

          <div class="admin-form-actions">
            <button class="admin-btn" type="submit">Simpan</button>
            <a class="admin-btn-ghost" href="fields.php">Batal</a>
          </div>
        </form>
      </section>

    <?php else: ?>

      <?php
      $fields = $pdo->query('SELECT * FROM fields ORDER BY created_at DESC')->fetchAll();

      $statusBadge = [
          'available'   => ['Tersedia', 'badge-available'],
          'maintenance' => ['Perawatan', 'badge-maintenance'],
          'inactive'    => ['Nonaktif', 'badge-inactive'],
      ];
      ?>

      <header class="admin-topbar">
        <div>
          <h1 class="admin-page-title">Kelola Lapangan</h1>
          <p class="admin-page-sub"><?= count($fields) ?> lapangan terdaftar</p>
        </div>
        <a class="admin-btn" href="fields.php?action=new">+ Tambah Lapangan</a>
      </header>

      <?php if ($msg = flash_get('ok')): ?>
        <div class="admin-flash is-ok"><?= e($msg) ?></div>
      <?php endif; ?>
      <?php if ($msg = flash_get('err')): ?>
        <div class="admin-flash is-err"><?= e($msg) ?></div>
      <?php endif; ?>

      <section class="admin-panel">
        <?php if (!$fields): ?>
          <div class="admin-empty">Belum ada lapangan. Tambahkan lapangan pertama.</div>
        <?php else: ?>
          <div class="admin-table-wrap">
            <table class="admin-table">
              <thead>
                <tr>
                  <th>Gambar</th>
                  <th>Nama</th>
                  <th>Kategori</th>
                  <th>Tipe</th>
                  <th>Harga / Jam</th>
                  <th>Status</th>
                  <th>Aksi</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($fields as $f): ?>
                  <?php
                  $src = field_image_src($f['image'], '..');
                  [$stLabel, $stClass] = $statusBadge[$f['status']] ?? [$f['status'], 'badge-inactive'];
                  ?>
                  <tr>
                    <td>
                      <?php if ($src): ?>
                        <img class="admin-thumb" src="<?= e($src) ?>" alt="">
                      <?php else: ?>
                        <span class="admin-thumb is-empty">N/A</span>
                      <?php endif; ?>
                    </td>
                    <td>
                      <?= e($f['name']) ?>
                      <div class="admin-sub"><?= e($f['location']) ?></div>
                    </td>
                    <td class="admin-muted"><?= e($f['category'] ?: '—') ?></td>
                    <td class="admin-muted"><?= e($fieldTypes[$f['field_type']] ?? $f['field_type']) ?></td>
                    <td>Rp <?= number_format((float) $f['price_per_hour'], 0, ',', '.') ?></td>
                    <td><span class="badge <?= e($stClass) ?>"><?= e($stLabel) ?></span></td>
                    <td>
                      <div class="admin-row-actions">
                        <a class="admin-btn-sm" href="fields.php?edit=<?= (int) $f['id'] ?>">Edit</a>
                        <form method="post" onsubmit="return confirm('Hapus lapangan ini?')">
                          <?= csrf_field() ?>
                          <input type="hidden" name="op" value="delete">
                          <input type="hidden" name="id" value="<?= (int) $f['id'] ?>">
                          <button class="admin-btn-sm is-danger" type="submit">Hapus</button>
                        </form>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </section>

    <?php endif; ?>
  </main>

</div>
</body>
</html>
