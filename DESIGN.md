# DESIGN.md — VAYGOR

> Lapangan futsal Magelang. Energi lapangan siang, bukan neon malam.

## Identity
VAYGOR = gerbang ke lapangan. Nada: pemain lokal yang cari lapangan cepat, lihat harga jelas, booking tanpa basa-basi. Bahasa visual: pinggir lapangan siang cerah — rumput hijau segar, garis putih tegas, cahaya matahari yang mantul di lantai.

## Palette (2-3 core + 1 accent, R-29)
- **Hutan gol** `#1B703A` (core 600) — header, tombol utama, nav active. Alasan: hijau rumput dalam bayangan, kontras 7.1:1 di atas putih, jadi hierarchy yang tenang tapi tegas.
- **Rumput siang** `#16A34A` (core 500) — hover, gradien hero sisi terang. Alasan: hijau segar siang hari, membedakan level elevasi dari 600.
- **Putih** `#FFFFFF` + **Arang lapangan** `#121212` / `#1E1E1E` (netral) — kartu, teks. Alasan: netral tidak dihitung core, jaga legibilitas.
- **Satu aksen: Lime bola** `#B6F500` — tagline, highlight harga, dot fokus. Alasan: warna pentil bola dan marka, dipakai kikir hanya di momen kunci (R-13 dose cap), bukan di semua ikon.

Gradien hanya di hero (`#16A34A` ke hitam 60%) untuk kedalaman, bukan di seluruh halaman (R-01).

## Typography
- **League Spartan** — heading, angka harga, tagline. Alasan: sporty, condensed, tracking minus, cocok untuk nama lapangan yang tegas.
- **Inter** — body, label, navigation. Alasan: netral, keterbacaan tinggi di kartu kecil dan form.
- Keputusan: tetap pasangan yang sudah dipakai (jawaban user), jadi konsisten tanpa ganti font.

## Motif & Texture
- **Pentagon bola** samar sebagai mask radial di hero saja (R-07: identitas produk). Alasan: bola = alasan situs ada, tapi hanya di pintu masuk, tidak diulang di card/footer.
- **Foto hero.png sunset di panel login kiri** — override eksplisit dari user, menggantikan pola titik generik. Alasan: aksi bola mengisi kekosongan panel (R-22) dengan energi nyata; overlay gradien hijau menjaga kontras teks (R-25). Alt jujur tanpa klaim venue spesifik (R-38).
- **Browse strip kompak** — hero hijau pendek dengan motif titik samar (gema beranda, bukan duplikat). Alasan: hierarki RHYTHM 2, beranda yang jadi statement hero utama.
- Tanpa grid blueprint, tanpa glow menyebar (R-07, R-13).

## Copy verdicts (final, terkunci)
- Footer company: PT VAYGOW (logo tetap VAYGOR, copyright YPLOVRV — disengaja tiga nama, tercatat).
- Alamat, kontak, copyright, kolom Ekosistem pajangan, rules, review, skor 4.4/25/4.00/3.70/4.50, tag 24 Hour + luas 25x11, P. Agus, HAORNAS 2026: semua KEEP sebagai data real (R-38).
- Heading produk: Aturan Pak Agus (ganti dari Rules By Nael).
- Sosmed: opsi A Segera hadir (tanpa comot link tempat lain, R-36).
- Booking extras: upload bukti untuk pending+confirmed (belum paid), cancel hanya pending milik sendiri.

## Dials
- **ENERGY 2 / RHYTHM 2 / MOTION 1**
- ENERGY 2: menyapa tegas tapi tidak berisik — hero besar, card rapat, bukan Awwwards eksperimental.
- RHYTHM 2: konsisten dengan jeda — hero variasi, form search, lalu grid venue.
- MOTION 1: hover dan transisi 200ms saja, tanpa scroll-reveal berlebihan.

## Decisions (R-31, one-line)
- Hijau 600 sebagai primary: kontras dan asosiasi rumput bayangan.
- Lime hanya aksen: biar harga dan CTA yang bicara.
- Pentagon hero-only: identitas tanpa mengulang dekorasi.
- Radius 12–20–28 scale: hierarki input < card < hero.
- Shadow hanya di kartu mengambang: elevasi, bukan default.
