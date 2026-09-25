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
$schedId = (int) ($_GET['schedule'] ?? 0);
if ($schedId > 0 && $action === 'list') {
    $action = 'schedule';
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

    if ($op === 'save_schedule') {
        $schedErr    = [];
        $schedRepost = false;
        $schedId     = (int) ($_POST['field_id'] ?? 0);

        if ($schedId <= 0) {
            $schedErr[] = 'Lapangan tidak valid.';
        } else {
            $chk = $pdo->prepare('SELECT COUNT(*) FROM fields WHERE id = ?');
            $chk->execute([$schedId]);
            if ((int) $chk->fetchColumn() === 0) {
                $schedErr[] = 'Lapangan tidak ditemukan.';
                $schedId    = 0;
            }
        }

        $days  = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        $items = [];
        if ($schedId > 0) {
            $enabled = (array) ($_POST['enabled'] ?? []);
            $opens   = (array) ($_POST['open'] ?? []);
            $closes  = (array) ($_POST['close'] ?? []);
            foreach ($days as $day) {
                if (empty($enabled[$day])) {
                    continue;
                }
                $open  = trim((string) ($opens[$day] ?? ''));
                $close = trim((string) ($closes[$day] ?? ''));
                if (!preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $open)
                    || !preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $close)) {
                    $schedErr[] = 'Format jam tidak valid untuk hari ' . ucfirst(substr($day, 0, 3)) . '.';
                    continue;
                }
                if ($close <= $open) {
                    $schedErr[] = 'Jam tutup harus lebih akhir dari jam buka untuk hari ' . ucfirst(substr($day, 0, 3)) . '.';
                    continue;
                }
                $items[$day] = [$open . ':00', $close . ':00'];
            }
            if (!$schedErr && !$items) {
                $schedErr[] = 'Centang minimal satu hari yang buka.';
            }
        }

        if (!$schedErr && $schedId > 0) {
            try {
                $pdo->beginTransaction();
                $pdo->prepare('DELETE FROM field_schedules WHERE field_id = ?')->execute([$schedId]);
                $ins = $pdo->prepare('INSERT INTO field_schedules (field_id, day_of_week, open_time, close_time) VALUES (?, ?, ?, ?)');
                foreach ($items as $day => [$open, $close]) {
                    $ins->execute([$schedId, $day, $open, $close]);
                }
                $pdo->commit();
                flash_set('ok', 'Jadwal lapangan berhasil disimpan.');
                redirect('fields.php?schedule=' . $schedId);
            } catch (Throwable $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                $schedErr[] = 'Gagal menyimpan jadwal. Coba lagi.';
            }
        }

        if ($schedErr) {
            $schedRepost = true;
            $action      = 'schedule';
        }
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

$dayLabel = [
    'Monday'    => 'Senin',
    'Tuesday'   => 'Selasa',
    'Wednesday' => 'Rabu',
    'Thursday'  => 'Kamis',
    'Friday'    => 'Jumat',
    'Saturday'  => 'Sabtu',
    'Sunday'    => 'Minggu',
];

$schedErr    = $schedErr ?? [];
$schedRepost = $schedRepost ?? false;
$schedField  = null;
$schedRows   = [];

if ($action === 'schedule') {
    if ($schedId > 0) {
        $stmt = $pdo->prepare('SELECT name FROM fields WHERE id = ?');
        $stmt->execute([$schedId]);
        $schedField = $stmt->fetchColumn();
        if ($schedField === false) {
            $schedField = null;
            $action     = 'list';
            flash_set('err', 'Lapangan tidak ditemukan.');
        }
    } else {
        $action = 'list';
        flash_set('err', 'Lapangan tidak ditemukan.');
    }

    $existing = [];
    if ($schedField !== null) {
        $stmt = $pdo->prepare('SELECT day_of_week, open_time, close_time FROM field_schedules WHERE field_id = ?');
        $stmt->execute([$schedId]);
        foreach ($stmt->fetchAll() as $r) {
            $existing[$r['day_of_week']] = $r;
        }
    }

    foreach (array_keys($dayLabel) as $day) {
        if ($schedRepost) {
            $schedRows[$day] = [
                'on'    => isset($_POST['enabled'][$day]),
                'open'  => trim((string) ($_POST['open'][$day] ?? '')),
                'close' => trim((string) ($_POST['close'][$day] ?? '')),
            ];
        } elseif (isset($existing[$day])) {
            $schedRows[$day] = [
                'on'    => true,
                'open'  => substr($existing[$day]['open_time'], 0, 5),
                'close' => substr($existing[$day]['close_time'], 0, 5),
            ];
        } else {
            $schedRows[$day] = [
                'on'    => false,
                'open'  => '08:00',
                'close' => '22:00',
            ];
        }
    }
}

$base      = '..';
$pageTitle = 'Kelola Lapangan - Admin VAYGOR';
$adminShell = true;
require __DIR__ . '/../includes/head.php';

function vaygor_admin_stars($avg): string
{
    $avg  = (float) $avg;
    $full = min(5, max(0, (int) round($avg)));
    $out  = '';
    for ($i = 1; $i <= 5; $i++) {
        $out .= '<svg class="admin-star' . ($i <= $full ? ' is-on' : '') . '" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 17.27 18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>';
    }
    return $out;
}
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

    <?php elseif ($action === 'schedule'): ?>

      <header class="admin-topbar">
        <div>
          <h1 class="admin-page-title">Jadwal Operasional</h1>
          <p class="admin-page-sub"><?= $schedField !== null ? e((string) $schedField) : '' ?> — jam buka per hari buat halaman booking</p>
        </div>
        <a class="admin-btn-ghost" href="fields.php">&larr; Kembali</a>
      </header>

      <?php if ($msg = flash_get('ok')): ?>
        <div class="admin-flash is-ok"><?= e($msg) ?></div>
      <?php endif; ?>
      <?php if ($msg = flash_get('err')): ?>
        <div class="admin-flash is-err"><?= e($msg) ?></div>
      <?php endif; ?>
      <?php if ($schedErr): ?>
        <div class="admin-flash is-err">
          <?php foreach ($schedErr as $i => $msg): ?>
            <?= $i > 0 ? '<br>' : '' ?><?= e($msg) ?>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <section class="admin-panel">
        <div class="admin-panel-head">
          <h2 class="admin-panel-title">Jam buka <?= $schedField !== null ? e((string) $schedField) : '' ?></h2>
          <span class="admin-top-date">Berlaku tiap minggu</span>
        </div>

        <?php if ($schedField === null): ?>
          <div class="admin-empty">Lapangan tidak ditemukan.</div>
        <?php else: ?>
          <form class="admin-form" method="post" data-sched-root>
            <?= csrf_field() ?>
            <input type="hidden" name="op" value="save_schedule">
            <input type="hidden" name="field_id" value="<?= (int) $schedId ?>">

            <div class="admin-table-wrap">
              <table class="admin-table admin-sched-table">
                <thead>
                  <tr>
                    <th>Hari</th>
                    <th class="admin-sched-col-buka">Buka</th>
                    <th>Jam Buka</th>
                    <th>Jam Tutup</th>
                    <th class="admin-sched-state">Status</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($dayLabel as $day => $nama): ?>
                    <?php $r = $schedRows[$day] ?? ['on' => false, 'open' => '08:00', 'close' => '22:00']; ?>
                    <tr>
                      <th scope="row" class="admin-sched-day"><?= e($nama) ?></th>
                      <td class="admin-sched-col-buka">
                        <input class="admin-sched-check" type="checkbox" name="enabled[<?= e($day) ?>]" value="1"
                               <?= $r['on'] ? 'checked' : '' ?>>
                      </td>
                      <td>
                        <input class="admin-input admin-input-time" type="time" name="open[<?= e($day) ?>]"
                               value="<?= e($r['open']) ?>" <?= $r['on'] ? '' : 'disabled' ?>>
                      </td>
                      <td>
                        <input class="admin-input admin-input-time" type="time" name="close[<?= e($day) ?>]"
                               value="<?= e($r['close']) ?>" <?= $r['on'] ? '' : 'disabled' ?>>
                      </td>
                      <td class="admin-sched-state">
                        <?php if ($r['on']): ?>
                          <span class="admin-sched-open">Buka</span>
                        <?php else: ?>
                          <span class="admin-sched-closed">Libur</span>
                        <?php endif; ?>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>

            <div class="admin-form-actions">
              <button class="admin-btn" type="submit">Simpan Jadwal</button>
              <a class="admin-btn-ghost" href="fields.php">Batal</a>
            </div>
          </form>
        <?php endif; ?>
      </section>

      <script>
      (function () {
        var root = document.querySelector('form[data-sched-root]');
        if (!root) return;
        root.querySelectorAll('tr').forEach(function (tr) {
          var check = tr.querySelector('input.admin-sched-check');
          if (!check) return;
          var times = tr.querySelectorAll('input[type="time"]');
          function sync() {
            times.forEach(function (t) { t.disabled = !check.checked; });
          }
          check.addEventListener('change', sync);
          sync();
        });
      })();
      </script>

    <?php else: ?>

      <?php
      $fields = $pdo->query(
          "SELECT f.*,
             (SELECT GROUP_CONCAT(k.name_kat ORDER BY k.name_kat SEPARATOR ', ')
              FROM fields_kat fk
              JOIN kategori k ON k.id_kat = fk.id_kat
              WHERE fk.id_field = f.id) AS kategori,
             (SELECT AVG((b.rate_service + b.rate_comfort + b.rate_place) / 3)
              FROM bookings b
              WHERE b.id_field = f.id
                AND b.rate_service > 0 AND b.rate_comfort > 0 AND b.rate_place > 0) AS rating_avg,
             (SELECT COUNT(*)
              FROM bookings b
              WHERE b.id_field = f.id
                AND b.rate_service > 0 AND b.rate_comfort > 0 AND b.rate_place > 0) AS rating_count
           FROM fields f
           ORDER BY f.id DESC"
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

      <section>
        <div class="admin-panel-head">
          <h2 class="text-xl font-bold">Daftar Lapangan</h2>
          <span class="admin-top-date"><?= count($fields) ?> lapangan</span>
        </div>
        <?php if (!$fields): ?>
          <div class="admin-empty">Belum ada lapangan. Tambahkan lapangan pertama.</div>
        <?php else: ?>
          <div class="admin-cards">
            <?php foreach ($fields as $f): ?>
              <?php
                $src = image_src($f['image'], '..');
                $avg = (float) $f['rating_avg'];
                $cnt = (int) $f['rating_count'];
                $tags = array_filter(array_map('trim', explode(',', (string) $f['kategori'])));
              ?>
              <article class="admin-card">
                <div class="admin-card-media">
                  <?php if ($src): ?>
                    <img src="<?= e($src) ?>" alt="<?= e($f['name']) ?>" loading="lazy">
                  <?php else: ?>
                    <div class="admin-card-photo">
                      <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 5h16v14H4zM4 3a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2z"/></svg>
                      Tanpa gambar
                    </div>
                  <?php endif; ?>
                  <span class="admin-card-price">Rp <?= number_format((float) $f['price'], 0, ',', '.') ?></span>
                  <span class="admin-card-cap"><?= (int) $f['capacity'] ?> org</span>
                </div>

                <div class="admin-card-body">
                  <h3 class="admin-card-title"><?= e($f['name']) ?></h3>
                  <p class="admin-card-loc">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2a7 7 0 0 0-7 7c0 5.25 7 13 7 13s7-7.75 7-13a7 7 0 0 0-7-7zm0 9.5A2.5 2.5 0 1 1 12 6.5a2.5 2.5 0 0 1 0 5z"/></svg>
                    <?= e($f['location']) ?>
                  </p>

                  <div class="admin-card-rate">
                    <span class="admin-stars"><?= vaygor_admin_stars($avg) ?></span>
                    <?php if ($cnt > 0): ?>
                      <b><?= number_format($avg, 1, ',', '.') ?></b>
                      <span class="admin-card-rate-cnt">(<?= $cnt ?> ulasan)</span>
                    <?php else: ?>
                      <span class="admin-card-rate-empty">Belum ada ulasan</span>
                    <?php endif; ?>
                  </div>

                  <?php if ($tags): ?>
                    <div class="admin-card-tags">
                      <?php foreach ($tags as $t): ?>
                        <span class="admin-card-tag"><?= e($t) ?></span>
                      <?php endforeach; ?>
                    </div>
                  <?php endif; ?>

                  <?php if ($f['description']): ?>
                    <p class="admin-card-desc"><?= e(mb_strimwidth(trim((string) $f['description']), 0, 130, '…')) ?></p>
                  <?php endif; ?>

                  <div class="admin-row-actions">
                    <a class="admin-btn-sm" href="fields.php?edit=<?= (int) $f['id'] ?>">Edit</a>
                    <a class="admin-btn-sm" href="fields.php?schedule=<?= (int) $f['id'] ?>">Jadwal</a>
                    <form method="post" onsubmit="return confirm('Hapus lapangan ini?')">
                      <?= csrf_field() ?>
                      <input type="hidden" name="op" value="delete">
                      <input type="hidden" name="id" value="<?= (int) $f['id'] ?>">
                      <button class="admin-btn-sm is-danger" type="submit">Hapus</button>
                    </form>
                  </div>
                </div>
              </article>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </section>

    <?php endif; ?>
  </main>

</div>
</body>
</html>