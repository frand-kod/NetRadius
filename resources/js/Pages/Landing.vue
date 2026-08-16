<template>
  <div class="min-h-screen bg-slate-50 text-slate-900 font-sans antialiased selection:bg-blue-600 selection:text-white">
    <!-- Navbar -->
    <nav class="sticky top-0 z-50 bg-white/90 backdrop-blur-md border-b border-slate-200">
      <div class="max-w-7xl mx-auto px-6 h-16 flex justify-between items-center">
        <div class="flex items-center gap-3">
          <div class="w-8 h-8 bg-blue-600 rounded flex items-center justify-center text-white font-bold text-lg">N</div>
          <span class="font-bold text-lg text-slate-900 tracking-tight">NuxBill</span>
        </div>
        <div class="flex items-center gap-4">
          <a href="/customer/login" class="text-sm font-medium text-slate-600 hover:text-blue-600 transition">Portal Pelanggan</a>
          <a href="/admin/login" class="text-sm font-medium bg-slate-900 text-white px-4 py-2 rounded hover:bg-slate-800 transition">Admin Login</a>
        </div>
      </div>
    </nav>

    <!-- Hero Section -->
    <header class="py-24 bg-white border-b border-slate-200">
      <div class="max-w-4xl mx-auto px-6 text-center">
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded bg-blue-50 text-blue-700 text-xs font-semibold mb-6 border border-blue-100">
          Laravel 13 Powered &bull; MikroTik & FreeRADIUS Ready
        </div>
        <h1 class="text-4xl sm:text-6xl font-extrabold text-slate-950 mb-6 tracking-tight leading-tight">
          Solusi Billing Hotspot untuk ISP Rumahan & Perumahan
        </h1>
        <p class="text-lg text-slate-600 mb-10 leading-relaxed max-w-2xl mx-auto">
          Kelola bandwidth, voucher, dan pelanggan hotspot Mikrotik Anda secara efisien, terstruktur, dan andal tanpa kompleksitas berlebih.
        </p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
          <a href="/customer/login" class="bg-blue-600 text-white px-6 py-3 rounded font-medium hover:bg-blue-700 transition text-center shadow-sm">
            Masuk ke Portal Pelanggan
          </a>
          <a href="/admin/login" class="bg-white text-slate-700 border border-slate-300 px-6 py-3 rounded font-medium hover:bg-slate-50 transition text-center">
            Login Admin Panel
          </a>
        </div>
      </div>
    </header>

    <!-- Target Audience / Who it is for -->
    <section class="py-20 max-w-6xl mx-auto px-6">
      <div class="text-center max-w-2xl mx-auto mb-16">
        <h2 class="text-3xl font-bold tracking-tight mb-4">Untuk Siapa NuxBill Dibuat?</h2>
        <p class="text-slate-600">Dirancang khusus untuk skala operasional personal dan menengah kebawah.</p>
      </div>
      <div class="grid md:grid-cols-2 gap-8">
        <div class="p-8 bg-white border border-slate-200 rounded-lg shadow-sm">
          <div class="w-10 h-10 bg-blue-50 text-blue-600 rounded flex items-center justify-center font-bold text-lg mb-4 border border-blue-100">01</div>
          <h3 class="text-xl font-bold text-slate-900 mb-3">ISP Rumahan / RT-RW Net</h3>
          <p class="text-slate-600 leading-relaxed">
            Sangat cocok bagi penyedia layanan internet skala kecil yang membutuhkan sistem manajemen pelanggan dan voucher yang langsung bisa digunakan tanpa setup rumit.
          </p>
        </div>
        <div class="p-8 bg-white border border-slate-200 rounded-lg shadow-sm">
          <div class="w-10 h-10 bg-blue-50 text-blue-600 rounded flex items-center justify-center font-bold text-lg mb-4 border border-blue-100">02</div>
          <h3 class="text-xl font-bold text-slate-900 mb-3">Single Administrator</h3>
          <p class="text-slate-600 leading-relaxed">
            Dioptimalkan untuk dikelola oleh satu orang admin. Otomatisasi cron job untuk pengecekan expired plan dan reminder WhatsApp/Telegram meringankan pekerjaan harian.
          </p>
        </div>
      </div>
    </section>

    <!-- Use Cases -->
    <section class="py-20 bg-slate-100 border-y border-slate-200">
      <div class="max-w-6xl mx-auto px-6">
        <div class="text-center max-w-2xl mx-auto mb-16">
          <h2 class="text-3xl font-bold tracking-tight mb-4">Use Cases Utama</h2>
          <p class="text-slate-600">Bagaimana NuxBill menyelesaikan kebutuhan operasional harian.</p>
        </div>
        <div class="grid md:grid-cols-3 gap-6">
          <div class="p-6 bg-white border border-slate-200 rounded-lg">
            <h4 class="font-bold text-slate-900 mb-2">Manajemen Hotspot Mikrotik</h4>
            <p class="text-sm text-slate-600 leading-relaxed">Provisioning otomatis user hotspot ke perangkat Mikrotik menggunakan interface terstruktur.</p>
          </div>
          <div class="p-6 bg-white border border-slate-200 rounded-lg">
            <h4 class="font-bold text-slate-900 mb-2">Otentikasi FreeRADIUS</h4>
            <p class="text-sm text-slate-600 leading-relaxed">Endpoint REST API untuk authenticate, authorize, dan accounting dengan FreeRADIUS.</p>
          </div>
          <div class="p-6 bg-white border border-slate-200 rounded-lg">
            <h4 class="font-bold text-slate-900 mb-2">Voucher & Cetak Massal</h4>
            <p class="text-sm text-slate-600 leading-relaxed">Generate kode voucher secara massal lengkap dengan halaman cetak siap print.</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Features: Included vs Excluded -->
    <section class="py-20 max-w-6xl mx-auto px-6">
      <div class="text-center max-w-2xl mx-auto mb-16">
        <h2 class="text-3xl font-bold tracking-tight mb-4">Transparansi Fitur</h2>
        <p class="text-slate-600">Apa saja yang tersedia dan apa yang sengaja kami sederhanakan.</p>
      </div>

      <div class="grid md:grid-cols-2 gap-8">
        <!-- Included -->
        <div class="p-8 bg-white border border-slate-200 rounded-lg">
          <h3 class="text-lg font-bold text-emerald-700 mb-6 flex items-center gap-2">
            <span class="w-2.5 h-2.5 bg-emerald-600 rounded-full"></span> Fitur yang Tersedia
          </h3>
          <ul class="space-y-4 text-sm text-slate-700">
            <li class="flex items-start gap-3">
              <span class="text-emerald-600 font-bold">&check;</span> Admin Panel (Filament/Custom) untuk CRUD Customer, Plan, Router & Voucher.
            </li>
            <li class="flex items-start gap-3">
              <span class="text-emerald-600 font-bold">&check;</span> Portal Self-Service Customer dengan riwayat order & transaksi.
            </li>
            <li class="flex items-start gap-3">
              <span class="text-emerald-600 font-bold">&check;</span> Sistem Invoice QR Statis dengan persetujuan manual admin.
            </li>
            <li class="flex items-start gap-3">
              <span class="text-emerald-600 font-bold">&check;</span> Notifikasi WhatsApp (via GOWA) dan Telegram otomatis.
            </li>
            <li class="flex items-start gap-3">
              <span class="text-emerald-600 font-bold">&check;</span> Cron scheduler untuk cek plan expired & reminder otomatis.
            </li>
          </ul>
        </div>

        <!-- Excluded -->
        <div class="p-8 bg-white border border-slate-200 rounded-lg">
          <h3 class="text-lg font-bold text-rose-700 mb-6 flex items-center gap-2">
            <span class="w-2.5 h-2.5 bg-rose-600 rounded-full"></span> Fitur yang Sengaja Dihapus (Out of Scope)
          </h3>
          <ul class="space-y-4 text-sm text-slate-700">
            <li class="flex items-start gap-3">
              <span class="text-rose-600 font-bold">&times;</span> Tidak ada fitur saldo/balance customer (disederhanakan langsung bayar per order).
            </li>
            <li class="flex items-start gap-3">
              <span class="text-rose-600 font-bold">&times;</span> Tidak ada payment gateway pihak ketiga (fokus pada QR statis & verifikasi manual).
            </li>
            <li class="flex items-start gap-3">
              <span class="text-rose-600 font-bold">&times;</span> Tidak ada mid-cycle plan switch atau perubahan paket di tengah periode aktif.
            </li>
          </ul>
        </div>
      </div>
    </section>

    <!-- Footer -->
    <footer class="bg-white border-t border-slate-200 py-8 text-center text-slate-500 text-sm">
      <div class="max-w-7xl mx-auto px-6 flex flex-col sm:flex-row justify-between items-center gap-4">
        <div>&copy; {{ new Date().getFullYear() }} NuxBill. Personal Laravel Rewrite.</div>
        <div class="flex gap-6">
          <a href="/admin/login" class="hover:text-slate-900 transition">Admin</a>
          <a href="/customer/login" class="hover:text-slate-900 transition">Customer</a>
        </div>
      </div>
    </footer>
  </div>
</template>

<script setup>
</script>
