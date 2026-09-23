<?php
// expects $conn from index.php
// Adapted: support BOTH schemas (new: price_per_hour/field_type/status) and old (price + kategori)
// Filter: q/nama, jenis/kat/type (kategori numeric OR indoor/outdoor), tanggal/date (diteruskan ke booking)
$q          = trim($_GET['q'] ?? trim($_GET['nama'] ?? ''));
$jenisParam = trim($_GET['jenis'] ?? '');
$katParam   = trim($_GET['kat'] ?? '');
$typeParam  = trim($_GET['type'] ?? '');
$date       = trim($_GET['tanggal'] ?? trim($_GET['date'] ?? ''));

$catFilter = '';
$typeFilter = '';
if ($jenisParam !== '') {
    if (in_array(strtolower($jenisParam), ['indoor','outdoor'], true)) {
        $typeFilter = strtolower($jenisParam);
    } elseif (is_numeric($jenisParam)) {
        $catFilter = $jenisParam;
    } else {
        $catFilter = $jenisParam;
    }
}
if ($katParam !== '' && $catFilter === '' && $typeFilter === '') {
    $catFilter = $katParam;
}
if ($typeParam !== '' && $typeFilter === '' && $catFilter === '') {
    // type param explicit (indoor/outdoor from old browse)
    if (in_array(strtolower($typeParam), ['indoor','outdoor'], true)) {
        $typeFilter = strtolower($typeParam);
    }
}
// also allow typeParam to override if it's indoor/outdoor even when catFilter set numeric? keep priority: typeFilter wins for field_type
if ($typeParam !== '' && in_array(strtolower($typeParam), ['indoor','outdoor'], true)) {
    $typeFilter = strtolower($typeParam);
    if (!is_numeric($catFilter)) {
        // keep catFilter if numeric, otherwise clear duplicate
        if (!is_numeric($jenisParam)) $catFilter = '';
    }
}

$venues = [];
$dbError = '';
$isNumericCat = $catFilter !== '' && ctype_digit((string)$catFilter);
$needKatJoin = $isNumericCat && $typeFilter === '';

try {
    // Prefer new schema: SELECT f.* + status filter, price_per_hour handled in PHP
    // Build base query tolerant to both schemas via SELECT f.* 
    $sql = "SELECT f.* FROM fields f";
    $joins  = '';
    $wheres = [];
    $params = [];
    $types  = '';

    // status filter for new schema - try to include, fallback without if column missing
    // we add it as first where, but wrap execution in retry logic
    $hasStatusFilter = true;
    $wheres[] = "1=1";

    if ($needKatJoin) {
        $joins = " INNER JOIN fields_kat fk ON fk.id_field = f.id";
        $wheres[] = "fk.id_kat = ?";
        $params[] = (int) $catFilter;
        $types .= 'i';
    } elseif ($typeFilter !== '') {
        // field_type filter for new schema; if column missing, will be caught and retried without filter
        $wheres[] = "f.field_type = ?";
        $params[] = $typeFilter;
        $types .= 's';
    }

    if ($q !== '') {
        $wheres[] = "(f.name LIKE ? OR f.location LIKE ?)";
        $like = "%$q%";
        $params[] = $like;
        $params[] = $like;
        $types .= 'ss';
    }

    // Add status='available' if new schema
    if ($hasStatusFilter) {
        $wheres[] = "f.status = 'available'";
    }

    $sql .= $joins . " WHERE " . implode(' AND ', $wheres) . " ORDER BY f.id ASC";

    $stmt = $conn->prepare($sql);
    if ($stmt === false) throw new RuntimeException($conn->error);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $venues[] = $row;
    }
    $stmt->close();
} catch (Throwable $e) {
    // Fallback: retry without status/field_type/kat join that may not exist on old schema
    $msg = $e->getMessage();
    $isMissingCol = str_contains($msg, 'Unknown column') || str_contains($msg, 'doesn\'t exist') || str_contains($msg, 'no such column');
    if ($isMissingCol) {
        try {
            $sql2 = "SELECT f.* FROM fields f";
            $joins2 = '';
            $wheres2 = ['1=1'];
            $params2 = [];
            $types2 = '';
            // only keep kategori join if numeric and table likely exists; otherwise drop field_type filter
            if ($needKatJoin) {
                // try keep join, but if fails again we will drop it in next catch
                $joins2 = " INNER JOIN fields_kat fk ON fk.id_field = f.id";
                $wheres2[] = "fk.id_kat = ?";
                $params2[] = (int) $catFilter;
                $types2 .= 'i';
            }
            if ($q !== '') {
                $wheres2[] = "(f.name LIKE ? OR f.location LIKE ?)";
                $like2 = "%$q%";
                $params2[] = $like2;
                $params2[] = $like2;
                $types2 .= 'ss';
            }
            $sql2 .= $joins2 . " WHERE " . implode(' AND ', $wheres2) . " ORDER BY f.id ASC";
            $stmt2 = $conn->prepare($sql2);
            if ($stmt2 === false) throw new RuntimeException($conn->error);
            if (!empty($params2)) $stmt2->bind_param($types2, ...$params2);
            $stmt2->execute();
            $res2 = $stmt2->get_result();
            while ($row = $res2->fetch_assoc()) $venues[] = $row;
            $stmt2->close();
        } catch (Throwable $e2) {
            // final fallback: bare select without any join/filter except q
            try {
                $sql3 = "SELECT f.* FROM fields f WHERE 1=1";
                $params3 = []; $types3 = '';
                if ($q !== '') {
                    $sql3 .= " AND (f.name LIKE ? OR f.location LIKE ?)";
                    $like3 = "%$q%";
                    $params3 = [$like3, $like3];
                    $types3 = 'ss';
                }
                $sql3 .= " ORDER BY f.id ASC";
                $stmt3 = $conn->prepare($sql3);
                if ($stmt3 === false) throw new RuntimeException($conn->error);
                if (!empty($params3)) $stmt3->bind_param($types3, ...$params3);
                $stmt3->execute();
                $res3 = $stmt3->get_result();
                while ($row = $res3->fetch_assoc()) $venues[] = $row;
                $stmt3->close();
            } catch (Throwable $e3) {
                $dbError = 'Gagal memuat venue. Coba lagi.';
            }
        }
    } else {
        $dbError = 'Gagal memuat venue. Coba lagi.';
    }
}

// Peta kategori per field - best effort, ignore if tables missing
$catsByField = [];
$kats = [];
try {
    $qcat = mysqli_query($conn, "SELECT fk.id_field, k.name_kat FROM fields_kat fk JOIN kategori k ON k.id_kat = fk.id_kat ORDER BY k.name_kat");
    if ($qcat) {
        while ($c = mysqli_fetch_assoc($qcat)) {
            $catsByField[$c['id_field']][] = $c['name_kat'];
        }
    }
    $qall = mysqli_query($conn, "SELECT id_kat, name_kat FROM kategori ORDER BY id_kat");
    if ($qall) {
        while ($k = mysqli_fetch_assoc($qall)) {
            $kats[] = $k;
        }
    }
} catch (Throwable $e) {
}

// fallback chips for new schema when kategori table empty
if (empty($kats) && $typeFilter === '' && $catFilter === '') {
    // keep empty to show only "Semua" - UI already handles
}

function vaygor_img($row)
{
    $c = $row['image'] ?? '';
    if ($c !== '') {
        if (file_exists(__DIR__ . '/../assets/uploads/' . $c)) return 'assets/uploads/' . $c;
        if (file_exists(__DIR__ . '/../assets/images/' . $c)) return 'assets/images/' . $c;
        if (file_exists(__DIR__ . '/../assets/uploads/pancuranmas.jpg')) return 'assets/uploads/pancuranmas.jpg';
    }
    if (file_exists(__DIR__ . '/../assets/images/lapangan.png')) return 'assets/images/lapangan.png';
    if (file_exists(__DIR__ . '/../assets/images/lapangan-pancuran.png')) return 'assets/images/lapangan-pancuran.png';
    return 'assets/images/wGrqDff8QHtt4PXzMsos8WVXJI_1.png';
}
function vaygor_price($n)
{
    return 'Rp ' . number_format((float) $n, 0, ',', '.');
}
function vaygor_resolve_price($row) {
    return $row['price_per_hour'] ?? $row['price'] ?? $row['price_per_hour'] ?? 0;
}
function chip_href($opts = [])
{
    $parts = ['index.php?p=browse'];
    if (!empty($opts['q'])) $parts[] = 'q=' . urlencode($opts['q']);
    if (!empty($opts['jenis'])) $parts[] = 'jenis=' . urlencode($opts['jenis']);
    if (!empty($opts['tanggal'])) $parts[] = 'tanggal=' . urlencode($opts['tanggal']);
    // keep backward compat for old links
    if (!empty($opts['type'])) $parts[] = 'type=' . urlencode($opts['type']);
    if (!empty($opts['date'])) $parts[] = 'date=' . urlencode($opts['date']);
    return implode('&', $parts);
}

$countText = count($venues) . ' venue';
if ($q !== '' || $catFilter !== '' || $typeFilter !== '') $countText .= ' ditemukan';
?>
<section class="relative mt-[-25px]">
  <!-- Hero strip -->
  <div class="bg-vaygor-600 text-white py-8">
    <div class="relative mx-auto max-w-6xl px-6 sm:px-8 lg:px-12 py-15">
      <p class="inline-flex rounded-full bg-white/15 px-3 py-1 text-[11px] font-bold tracking-widest uppercase ring-1 ring-white/20">Jelajahi Lapangan</p>
      <h1 class="mt-3 font-spartan text-3xl sm:text-4xl font-extrabold tracking-tight">Cari yang pas buat timmu</h1>
      <p class="mt-2 text-sm text-white/90">Hasil untuk <span class="font-semibold"><?php echo $q !== '' ? htmlspecialchars($q, ENT_QUOTES, 'UTF-8') : 'semua venue'; ?></span> - <?php echo htmlspecialchars($countText, ENT_QUOTES, 'UTF-8'); ?></p>
    </div>
  </div>

  <!-- Search -->
  <div class="mx-auto max-w-6xl px-6 sm:px-8 lg:px-12 -mt-6 relative z-10">
    <form action="index.php" method="get" class="rounded-2xl border border-zinc-200 bg-white p-4 sm:p-5 shadow-[0_12px_32px_rgba(0,0,0,0.08)]">
      <input type="hidden" name="p" value="browse">
      <div class="grid gap-3 sm:grid-cols-[1.5fr_1fr_1fr_auto] sm:items-end">
        <div>
          <label for="bq" class="block text-xs font-semibold text-zinc-700">Cari</label>
          <div class="mt-1.5 relative">
            <svg class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-zinc-400" viewBox="0 0 24 24" fill="currentColor">
              <path d="M15.5 14h-.79l-.28-.27A6.47 6.47 0 0 0 16 9.5 6.5 6.5 0 1 0 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z" />
            </svg>
            <input id="bq" type="text" name="q" value="<?php echo htmlspecialchars($q, ENT_QUOTES, 'UTF-8'); ?>" placeholder="Cari nama atau lokasi" class="w-full rounded-xl border border-zinc-200 bg-white pl-9 pr-4 py-3 text-sm focus:border-vaygor-600 focus:outline-none focus:ring-2 focus:ring-vaygor-600/20">
          </div>
        </div>
        <div>
          <label for="bjenis" class="block text-xs font-semibold text-zinc-700">Jenis Olahraga</label>
          <select id="bjenis" name="jenis" class="mt-1.5 w-full cursor-pointer rounded-xl border border-zinc-200 bg-white px-4 py-3 text-sm focus:border-vaygor-600 focus:outline-none focus:ring-2 focus:ring-vaygor-600/20">
            <option value="">Semua olahraga</option>
            <?php if (!empty($kats)): ?>
              <?php foreach ($kats as $kk): ?>
                <option value="<?php echo (int) $kk['id_kat']; ?>" <?php echo $catFilter === (string) $kk['id_kat'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($kk['name_kat'], ENT_QUOTES, 'UTF-8'); ?></option>
              <?php endforeach; ?>
            <?php else: ?>
              <option value="indoor" <?php echo $typeFilter==='indoor'?'selected':''; ?>>Indoor</option>
              <option value="outdoor" <?php echo $typeFilter==='outdoor'?'selected':''; ?>>Outdoor</option>
            <?php endif; ?>
          </select>
        </div>
        <div>
          <label for="bdate" class="block text-xs font-semibold text-zinc-700">Tanggal</label>
          <input id="bdate" type="date" name="tanggal" value="<?php echo htmlspecialchars($date, ENT_QUOTES, 'UTF-8'); ?>" class="mt-1.5 w-full rounded-xl border border-zinc-200 bg-white px-4 py-3 text-sm focus:border-vaygor-600 focus:outline-none focus:ring-2 focus:ring-vaygor-600/20">
        </div>
        <button type="submit" class="h-[46px] rounded-xl bg-vaygor-600 px-6 text-sm font-bold text-white hover:bg-vaygor-700">Cari Lapangan</button>
      </div>
      <p class="mt-2 text-xs text-zinc-500">Tanggal diteruskan ke booking (ketersediaan jam dicek saat booking).</p>
    </form>
  </div>

  <!-- Chips kategori / tipe -->
  <div class="mx-auto max-w-6xl px-6 sm:px-8 lg:px-12 mt-5">
    <div class="flex flex-wrap gap-2">
      <?php
      $allHref = chip_href(['q' => $q, 'tanggal' => $date]);
      $allActive = $catFilter === '' && $typeFilter === '';
      ?>
      <a href="<?php echo htmlspecialchars($allHref, ENT_QUOTES, 'UTF-8'); ?>" class="rounded-full border px-4 py-2 text-sm font-semibold <?php echo $allActive ? 'border-vaygor-600 bg-vaygor-600 text-white' : 'border-zinc-200 bg-white text-zinc-700 hover:border-vaygor-600 hover:text-vaygor-600'; ?>">Semua</a>
      <?php if (!empty($kats)): ?>
        <?php foreach ($kats as $kk): $kkActive = $catFilter === (string) $kk['id_kat']; ?>
          <a href="<?php echo htmlspecialchars(chip_href(['q' => $q, 'jenis' => $kk['id_kat'], 'tanggal' => $date]), ENT_QUOTES, 'UTF-8'); ?>" class="rounded-full border px-4 py-2 text-sm font-semibold <?php echo $kkActive ? 'border-vaygor-600 bg-vaygor-600 text-white' : 'border-zinc-200 bg-white text-zinc-700 hover:border-vaygor-600 hover:text-vaygor-600'; ?>"><?php echo htmlspecialchars($kk['name_kat'], ENT_QUOTES, 'UTF-8'); ?></a>
        <?php endforeach; ?>
      <?php else: ?>
        <?php foreach (['indoor'=>'Indoor','outdoor'=>'Outdoor'] as $tv=>$tl): $tActive = $typeFilter===$tv; ?>
          <a href="<?php echo htmlspecialchars(chip_href(['q'=>$q,'jenis'=>$tv,'tanggal'=>$date]), ENT_QUOTES, 'UTF-8'); ?>" class="rounded-full border px-4 py-2 text-sm font-semibold <?php echo $tActive ? 'border-vaygor-600 bg-vaygor-600 text-white' : 'border-zinc-200 bg-white text-zinc-700 hover:border-vaygor-600 hover:text-vaygor-600'; ?>"><?php echo $tl; ?></a>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>

  <!-- Event banner -->
  <div class="mx-auto max-w-6xl px-6 sm:px-8 lg:px-12 mt-6">
    <div class="rounded-2xl bg-gradient-to-br from-vaygor-600 to-vaygor-700 text-white p-6 sm:p-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div>
        <p class="inline-flex rounded-full bg-white/15 px-3 py-1 text-[11px] font-bold tracking-widest uppercase ring-1 ring-white/20">Segera hadir</p>
        <h2 class="mt-2 font-spartan text-xl sm:text-2xl font-bold">HAORNAS 2026</h2>
        <p class="mt-1 text-sm text-white/80">Turnamen komunitas - info jadwal menyusul.</p>
      </div>
      <span class="inline-flex rounded-xl bg-[#B6F500] px-5 py-2.5 text-sm font-bold text-zinc-900">Segera hadir</span>
    </div>
  </div>

  <!-- Results -->
  <div class="mx-auto max-w-6xl px-6 sm:px-8 lg:px-12 mt-8 pb-12">
    <?php if ($dbError !== ''): ?>
      <div role="alert" class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"><?php echo htmlspecialchars($dbError, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php elseif (empty($venues)): ?>
      <div class="rounded-2xl border border-zinc-200 bg-white p-8 text-center">
        <p class="font-spartan text-lg font-bold text-zinc-900">Tidak ada venue yang cocok</p>
        <p class="mt-1 text-sm text-zinc-600">Coba ubah kata kunci atau kategori. Contoh: kosongkan pencarian untuk lihat semua.</p>
        <a href="index.php?p=browse" class="mt-4 inline-flex rounded-xl bg-vaygor-600 px-5 py-2.5 text-sm font-bold text-white hover:bg-vaygor-700">Lihat semua venue</a>
      </div>
    <?php else: ?>
      <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
        <?php foreach ($venues as $v):
          $vcats = $catsByField[$v['id']] ?? [];
        ?>
          <a href="index.php?p=produk&id=<?php echo (int) $v['id']; ?>" class="group overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm hover:shadow-md transition">
            <div class="h-36 sm:h-40 overflow-hidden bg-zinc-100">
              <img src="<?php echo htmlspecialchars(vaygor_img($v), ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($v['name'], ENT_QUOTES, 'UTF-8'); ?>" class="h-full w-full object-cover group-hover:scale-[1.02] transition" loading="lazy">
            </div>
            <div class="p-4">
              <div class="flex flex-wrap gap-2 text-[11px] text-zinc-500">
                <?php if ($vcats): ?>
                  <?php foreach ($vcats as $cn): ?>
                    <span class="rounded-full bg-vaygor-50 text-vaygor-700 px-2 py-1"><?php echo htmlspecialchars($cn, ENT_QUOTES, 'UTF-8'); ?></span>
                  <?php endforeach; ?>
                <?php elseif (!empty($v['field_type'])): ?>
                  <span class="rounded-full bg-zinc-100 px-2 py-1"><?php echo htmlspecialchars(ucfirst($v['field_type']), ENT_QUOTES, 'UTF-8'); ?></span>
                <?php else: ?>
                  <span class="rounded-full bg-zinc-100 px-2 py-1">Lapangan</span>
                <?php endif; ?>
              </div>
              <h3 class="mt-2 font-spartan text-base font-bold text-zinc-900"><?php echo htmlspecialchars($v['name'], ENT_QUOTES, 'UTF-8'); ?></h3>
              <p class="text-xs text-zinc-500"><?php echo htmlspecialchars($v['location'], ENT_QUOTES, 'UTF-8'); ?></p>
              <div class="mt-3 flex items-center justify-between">
                <span class="text-sm font-bold text-vaygor-600"><?php echo htmlspecialchars(vaygor_price(vaygor_resolve_price($v)), ENT_QUOTES, 'UTF-8'); ?>/jam</span>
                <span class="text-xs font-semibold text-zinc-900 group-hover:text-vaygor-600">Lihat →</span>
              </div>
            </div>
          </a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>