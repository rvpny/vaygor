<?php
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/_upload.php';
require_role('admin');

$pdo = db();

$cats  = $pdo->query('SELECT id_kat, name_kat FROM kategori ORDER BY name_kat')->fetchAll();
$catId = array_column($cats, 'id_kat');

$action = (string) ($_GET['action'] ?? 'list');
if ($action === 'new') {
    $action = 'form';
}
$editId = (int) ($_GET['edit'] ?? 0);
if ($editId > 0 && $action === 'list') {
    $action = 'form';
}
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
            $pdo->prepare('DELETE FROM fields_kat WHERE id_field = ?')->execute([$id]);
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
        $capacity  = (int) ($_POST['capacity'] ?? 0);
        $price     = (string) ($_POST['price'] ?? '0');
        $desc      = trim((string) ($_POST['description'] ?? ''));
        $selected  = array_map('intval', (array) ($_POST['kategori'] ?? []));
        $selected  = array_values(array_intersect($selected, $catId));

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
        if (!is_numeric($price) || (float) $price < 0) {
            $errors[] = 'Harga harus angka tidak negatif.';
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
                    $capacity,
                    number_format((float) $price, 2, '.', ''),
                ];

                if ($id > 0) {
                    $sql = 'UPDATE fields SET name = ?, location = ?, description = ?, capacity = ?, price = ?';
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
                        'INSERT INTO fields (name, location, description, capacity, price, image)
                         VALUES (?, ?, ?, ?, ?, ?)'
                    )->execute($params);
                    $id = (int) $pdo->lastInsertId();
                }

                $pdo->prepare('DELETE FROM fields_kat WHERE id_field = ?')->execute([$id]);
                $stmt = $pdo->prepare('INSERT INTO fields_kat (id_field, id_kat) VALUES (?, ?)');
                foreach ($selected as $kat) {
                    $stmt->execute([$id, $kat]);
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
    'id'          => 0,
    'name'        => '',
    'location'    => '',
    'description' => '',
    'capacity'    => 10,
    'price'       => '',
    'image'       => null,
    'kategori'    => [],
];

if ($action === 'form') {
    if ($editId > 0) {
        $stmt = $pdo->prepare('SELECT * FROM fields WHERE id = ?');
        $stmt->execute([$editId]);
        $row = $stmt->fetch();
        if ($row) {
            $form = array_merge($form, $row);
            $stmt = $pdo->prepare('SELECT id_kat FROM fields_kat WHERE id_field = ?');
            $stmt->execute([$editId]);
            $form['kategori'] = array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
        } else {
            $action = 'list';
            flash_set('err', 'Lapangan tidak ditemukan.');
        }
    }
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $form = array_merge($form, [
            'id'          => $editId,
            'name'        => $name ?? '',
            'location'    => $location ?? '',
            'description' => $desc ?? '',
            'capacity'    => $capacity ?? 10,
            'price'       => $price ?? '',
            'kategori'    => $selected ?? [],
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
              <label for="price">Harga (Rp)</label>
              <input class="admin-input" id="price" type="number" name="price" min="0" step="1000"
                     value="<?= e($form['price']) ?>" required>
            </div>
            <div class="admin-field">
              <label for="capacity">Kapasitas</label>
              <input class="admin-input" id="capacity" type="number" name="capacity" min="0"
                     value="<?= e($form['capacity']) ?>">
            </div>
            <div class="admin-field">
              <label for="location">Lokasi</label>
              <input class="admin-input" id="location" type="text" name="location" value="<?= e($form['location']) ?>" required>
            </div>
            <div class="admin-field full">
              <label>Kategori</label>
              <div class="admin-check-group">
                <?php foreach ($cats as $c): ?>
                  <label class="admin-check">
                    <input type="checkbox" name="kategori[]" value="<?= (int) $c['id_kat'] ?>"
                           <?= in_array((int) $c['id_kat'], $form['kategori'], true) ? 'checked' : '' ?>>
                    <?= e($c['name_kat']) ?>
                  </label>
                <?php endforeach; ?>
              </div>
            </div>
            <div class="admin-field full">
              <label for="description">Deskripsi</label>
              <textarea class="admin-textarea" id="description" name="description"><?= e($form['description']) ?></textarea>
            </div>
            <div class="admin-field full">
              <label for="image">Gambar (JPG/PNG/WEBP, maks 2MB)</label>
              <input class="admin-input" id="image" type="file" name="image" accept="image/jpeg,image/png,image/webp">
              <?php $current = image_src($form['image'], '..'); ?>
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
      $fields = $pdo->query(
          'SELECT f.*, GROUP_CONCAT(k.name_kat ORDER BY k.name_kat SEPARATOR \', \') AS kategori
           FROM fields f
           LEFT JOIN fields_kat fk ON fk.id_field = f.id
           LEFT JOIN kategori k ON k.id_kat = fk.id_kat
           GROUP BY f.id
           ORDER BY f.id DESC'
      )->fetchAll();
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
                  <th>Kapasitas</th>
                  <th>Harga</th>
                  <th>Aksi</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($fields as $f): ?>
                  <?php $src = image_src($f['image'], '..'); ?>
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
                    <td class="admin-muted"><?= e((string) $f['kategori'] ?: '—') ?></td>
                    <td class="admin-muted"><?= (int) $f['capacity'] ?></td>
                    <td>Rp <?= number_format((float) $f['price'], 0, ',', '.') ?></td>
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