<?php
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/_upload.php';
require_role('admin');

$pdo = db();

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
        $stmt = $pdo->prepare('SELECT logo_kat FROM kategori WHERE id_kat = ?');
        $stmt->execute([$id]);
        $logo = $stmt->fetchColumn();
        if ($logo !== false) {
            $pdo->prepare('DELETE FROM fields_kat WHERE id_kat = ?')->execute([$id]);
            $pdo->prepare('DELETE FROM kategori WHERE id_kat = ?')->execute([$id]);
            delete_upload($logo ?: null);
            flash_set('ok', 'Kategori dihapus.');
        } else {
            flash_set('err', 'Kategori tidak ditemukan.');
        }
        redirect('categories.php');
    }

    if ($op === 'save') {
        $id    = (int) ($_POST['id'] ?? 0);
        $name  = trim((string) ($_POST['name_kat'] ?? ''));

        if ($name === '') {
            $errors[] = 'Nama kategori wajib diisi.';
        } elseif (mb_strlen($name) > 50) {
            $errors[] = 'Nama kategori maksimal 50 karakter.';
        }

        $newLogo = $errors ? null : save_upload($errors, 'assets/images/');

        if (!$errors) {
            $oldLogo = null;
            if ($id > 0) {
                $stmt = $pdo->prepare('SELECT logo_kat FROM kategori WHERE id_kat = ?');
                $stmt->execute([$id]);
                $oldLogo = $stmt->fetchColumn();
                if ($oldLogo === false) {
                    $errors[] = 'Kategori tidak ditemukan.';
                }
            }

            if (!$errors) {
                if ($id > 0) {
                    $sql = 'UPDATE kategori SET name_kat = ?';
                    $params = [$name];
                    if ($newLogo) {
                        $sql     .= ', logo_kat = ?';
                        $params[] = $newLogo;
                    }
                    $sql     .= ' WHERE id_kat = ?';
                    $params[] = $id;
                    $pdo->prepare($sql)->execute($params);
                    if ($newLogo) {
                        delete_upload($oldLogo ?: null);
                    }
                } else {
                    $pdo->prepare('INSERT INTO kategori (name_kat, logo_kat) VALUES (?, ?)')
                        ->execute([$name, $newLogo !== null ? $newLogo : '']);
                }
                flash_set('ok', 'Kategori berhasil disimpan.');
                redirect('categories.php');
            }
        }

        $action = 'form';
        $editId = $id;
    }
}

$form = [
    'id_kat'   => 0,
    'name_kat' => '',
    'logo_kat' => null,
];

if ($action === 'form') {
    if ($editId > 0) {
        $stmt = $pdo->prepare('SELECT * FROM kategori WHERE id_kat = ?');
        $stmt->execute([$editId]);
        $row = $stmt->fetch();
        if ($row) {
            $form = array_merge($form, $row);
        } else {
            $action = 'list';
            flash_set('err', 'Kategori tidak ditemukan.');
        }
    }
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $form = array_merge($form, [
            'id_kat'   => $editId,
            'name_kat' => $name ?? '',
        ]);
    }
}

$base       = '..';
$pageTitle  = 'Kelola Kategori - Admin VAYGOR';
$adminShell = true;
require __DIR__ . '/../includes/head.php';
?>
<div class="admin-shell">

  <?php $adminNav = 'kategori'; require __DIR__ . '/_sidebar.php'; ?>

  <main class="admin-main">
    <?php if ($action === 'form'): ?>

      <header class="admin-topbar">
        <div>
          <h1 class="admin-page-title"><?= $form['id_kat'] > 0 ? 'Edit Kategori' : 'Tambah Kategori' ?></h1>
          <p class="admin-page-sub">Kategori lapangan yang dipakai di VAYGOR</p>
        </div>
        <a class="admin-btn-ghost" href="categories.php">&larr; Kembali</a>
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
          <input type="hidden" name="id" value="<?= (int) $form['id_kat'] ?>">

          <div class="admin-form-grid">
            <div class="admin-field">
              <label for="name_kat">Nama Kategori</label>
              <input class="admin-input" id="name_kat" type="text" name="name_kat" value="<?= e($form['name_kat']) ?>" required>
            </div>
            <div class="admin-field full">
              <label for="image">Logo (JPG/PNG/WEBP, maks 2MB)</label>
              <input class="admin-input" id="image" type="file" name="image" accept="image/jpeg,image/png,image/webp">
              <?php $current = image_src($form['logo_kat'], '..'); ?>
              <?php if ($current): ?>
                <div class="admin-current-img">
                  <img class="admin-thumb" src="<?= e($current) ?>" alt="">
                  <span>Logo saat ini</span>
                </div>
              <?php elseif (!empty($form['logo_kat'])): ?>
                <span class="admin-help">Logo tercatat: <?= e($form['logo_kat']) ?> (file tidak ditemukan)</span>
              <?php endif; ?>
            </div>
          </div>

          <div class="admin-form-actions">
            <button class="admin-btn" type="submit">Simpan</button>
            <a class="admin-btn-ghost" href="categories.php">Batal</a>
          </div>
        </form>
      </section>

    <?php else: ?>

      <?php
      $categories = $pdo->query(
          'SELECT k.*, COUNT(fk.id_field) AS lapangan
           FROM kategori k
           LEFT JOIN fields_kat fk ON fk.id_kat = k.id_kat
           GROUP BY k.id_kat
           ORDER BY k.name_kat'
      )->fetchAll();
      ?>

      <header class="admin-topbar">
        <div>
          <h1 class="admin-page-title">Kelola Kategori</h1>
          <p class="admin-page-sub"><?= count($categories) ?> kategori terdaftar</p>
        </div>
        <a class="admin-btn" href="categories.php?action=new">+ Tambah Kategori</a>
      </header>

      <?php if ($msg = flash_get('ok')): ?>
        <div class="admin-flash is-ok"><?= e($msg) ?></div>
      <?php endif; ?>
      <?php if ($msg = flash_get('err')): ?>
        <div class="admin-flash is-err"><?= e($msg) ?></div>
      <?php endif; ?>

      <section class="admin-panel">
        <?php if (!$categories): ?>
          <div class="admin-empty">Belum ada kategori. Tambahkan kategori pertama.</div>
        <?php else: ?>
          <div class="admin-table-wrap">
            <table class="admin-table">
              <thead>
                <tr>
                  <th>Logo</th>
                  <th>Nama</th>
                  <th>Lapangan</th>
                  <th>Aksi</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($categories as $c): ?>
                  <?php $src = image_src($c['logo_kat'], '..'); ?>
                  <tr>
                    <td>
                      <?php if ($src): ?>
                        <img class="admin-thumb" src="<?= e($src) ?>" alt="">
                      <?php else: ?>
                        <span class="admin-thumb is-empty">N/A</span>
                      <?php endif; ?>
                    </td>
                    <td><?= e($c['name_kat']) ?></td>
                    <td class="admin-muted"><?= (int) $c['lapangan'] ?> lapangan</td>
                    <td>
                      <div class="admin-row-actions">
                        <a class="admin-btn-sm" href="categories.php?edit=<?= (int) $c['id_kat'] ?>">Edit</a>
                        <form method="post" onsubmit="return confirm('Hapus kategori ini?')">
                          <?= csrf_field() ?>
                          <input type="hidden" name="op" value="delete">
                          <input type="hidden" name="id" value="<?= (int) $c['id_kat'] ?>">
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