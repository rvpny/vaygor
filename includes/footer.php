<?php $active = $p ?? 'beranda'; ?>
<!-- Bottom bar mobile -->
<div class="sm:hidden fixed bottom-0 inset-x-0 z-40 border-t border-zinc-200 bg-white/95 backdrop-blur">
  <nav class="grid grid-cols-4">
    <a href="index.php" class="flex flex-col items-center gap-1 py-2.5 <?php echo $active==='beranda'?'text-vaygor-600':'text-zinc-500'; ?>" aria-label="Home">
      <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/></svg>
      <span class="text-[11px] font-semibold">Home</span>
    </a>
    <a href="index.php?p=browse" class="flex flex-col items-center gap-1 py-2.5 <?php echo $active==='browse'?'text-vaygor-600':'text-zinc-500'; ?>">
      <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><path d="M15.5 14h-.79l-.28-.27A6.47 6.47 0 0 0 16 9.5 6.5 6.5 0 1 0 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/></svg>
      <span class="text-[11px] font-semibold">Browse</span>
    </a>
    <span class="flex flex-col items-center gap-1 py-2.5 text-zinc-400" title="Segera hadir">
      <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V5h14v14zM7 10h2v7H7zm4-3h2v10h-2zm4 6h2v4h-2z"/></svg>
      <span class="text-[10px]">Order <em class="not-italic text-vaygor-600">Segera hadir</em></span>
    </span>
    <span class="flex flex-col items-center gap-1 py-2.5 text-zinc-400" title="Segera hadir">
      <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
      <span class="text-[10px]">Profile <em class="not-italic text-vaygor-600">Segera hadir</em></span>
    </span>
  </nav>
</div>
<div class="sm:hidden h-16"></div>

<footer class="border-t border-zinc-200 bg-white">
  <div class="mx-auto max-w-6xl px-6 sm:px-8 lg:px-12 py-10">
    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-6">
      <div>
        <div class="inline-flex items-center gap-1 font-spartan text-lg font-extrabold tracking-tight text-vaygor-600">
          <span>V</span><span>A</span><span>Y</span><span>G</span>
          <span class="inline-flex h-5 w-5 items-center justify-center rounded bg-vaygor-600 text-white text-[8px]">◉</span>
          <span>R</span>
        </div>
        <p class="mt-2 text-sm font-semibold text-zinc-900">PT VAYGOW</p>
        <p class="text-xs leading-relaxed text-zinc-500">Jl. Ahmad Yani, Kajoran Tengah, Tel Aviv, Israel City, Israel</p>
        <div class="mt-4 flex gap-2">
          <!-- TODO: ganti dengan URL sosmed asli -->
          <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-vaygor-600 text-white" title="Segera hadir"><svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M22.46 6c-.77.35-1.6.58-2.46.69A4.27 4.27 0 0 0 21.88 4.3A8.59 8.59 0 0 1 19.16 5.34A4.28 4.28 0 0 0 11.87 8.25A12.13 12.13 0 0 1 3.1 4.9A4.28 4.28 0 0 0 4.42 10.6A4.24 4.24 0 0 1 2.48 10v.05A4.28 4.28 0 0 0 5.91 14.24A4.3 4.3 0 0 1 3.98 14.31A4.28 4.28 0 0 0 7.98 17.28A8.58 8.58 0 0 1 2 19.54A12.08 12.08 0 0 0 8.56 21.46C17 21.46 21.76 14.93 21.76 9.34L21.75 8.78A8.72 8.72 0 0 0 23 6.5"/></svg></span>
          <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-vaygor-600 text-white" title="Segera hadir"><svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M7.8 2h8.4C19.4 2 22 4.6 22 7.8v8.4A5.8 5.8 0 0 1 16.2 22H7.8C4.6 22 2 19.4 2 16.2V7.8A5.8 5.8 0 0 1 7.8 2m-.2 2A3.6 3.6 0 0 0 4 7.6v8.8C4 18.39 5.61 20 7.6 20h8.8A3.6 3.6 0 0 0 3.6 16.4V7.6C20 5.61 18.39 4 16.4 4H7.6m9.65 1.5A1.25 1.25 0 1 1 0 2.5A1.25 1.25 0 0 1 0-2.5M12 7A5 5 0 1 1 0 10A5 5 0 0 1 0-10M12 9A3 3 0 1 0 0 6A3 3 0 0 0 0-6Z"/></svg></span>
          <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-vaygor-600 text-white" title="Segera hadir"><svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93C-3.95-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54C-.26-.81-1-1.39-1.9-1.39h-1v-3C0-.55-.45-1-1-1H8v-2h2C.55 0 1-.45 1-1V7h2C1.1 0 2-.9 2-2v-.41C2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39Z"/></svg></span>
        </div>
      </div>
      <div class="grid grid-cols-2 gap-6 text-sm sm:gap-10">
        <div><p class="font-semibold">Tentang Kami</p><ul class="mt-2 space-y-1 text-zinc-600"><li>Informasi</li><li>Kebijakan &amp; Privasi</li><li>Syarat dan Ketentuan</li></ul></div>
        <div><p class="font-semibold">Kontak</p><ul class="mt-2 space-y-1 text-zinc-600"><li>rvpryg@hotmail</li><li>+6256787881</li><li>Social Media</li></ul></div>
        <div><p class="font-semibold">Ekosistem</p><ul class="mt-2 space-y-1 text-zinc-600"><li>Hosting server</li><li>Afiliasi</li><li>Perlindungan</li></ul></div>
        <div><p class="font-semibold">Dukungan</p><ul class="mt-2 space-y-1 text-zinc-600"><li>Pusat bantuan</li><li>Dokumentasi</li><li>Daftarkan Akun</li></ul></div>
      </div>
    </div>
    <p class="mt-8 border-t border-zinc-100 pt-4 text-center text-xs text-zinc-400">© 2026 YPLOVRV Indonesia All Right Reserved</p>
  </div>
</footer>
