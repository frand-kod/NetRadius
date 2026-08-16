<template>
  <div class="min-h-screen bg-white text-slate-900 font-sans antialiased selection:bg-blue-100 selection:text-blue-900">
    <!-- Navigation -->
    <nav class="h-20 border-b border-slate-100 sticky top-0 bg-white/80 backdrop-blur-md z-50">
      <div class="max-w-7xl mx-auto px-8 h-full flex items-center justify-between">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 bg-slate-900 rounded flex items-center justify-center">
            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
          </div>
          <span class="text-xl font-bold tracking-tight uppercase">NuxBill</span>
        </div>
        <div class="hidden md:flex items-center gap-8">
          <a href="#features" class="text-sm font-medium text-slate-500 hover:text-slate-900 transition-colors">Fitur</a>
          <a href="#use-cases" class="text-sm font-medium text-slate-500 hover:text-slate-900 transition-colors">Penggunaan</a>
          <div class="h-4 w-px bg-slate-200"></div>
          <a href="/customer/login" class="text-sm font-medium text-slate-600 hover:text-slate-900 transition-colors">Portal Pelanggan</a>
          <a href="/admin/login" class="px-5 py-2.5 bg-slate-900 text-white text-sm font-semibold rounded hover:bg-slate-800 transition-all shadow-sm active:scale-95">
            Admin Login
          </a>
        </div>
      </div>
    </nav>

    <!-- Hero Section -->
    <header class="pt-32 pb-24 overflow-hidden border-b border-slate-50">
      <div class="max-w-7xl mx-auto px-8">
        <div class="max-w-3xl">
          <div class="inline-flex items-center gap-2 px-3 py-1 bg-slate-50 border border-slate-100 rounded text-[11px] font-bold uppercase tracking-widest text-slate-500 mb-8">
            Penyederhanaan Billing Hotspot
          </div>
          <h1 class="text-6xl md:text-7xl font-bold text-slate-900 leading-[1.1] tracking-tight mb-8">
            Manajemen Hotspot Mikrotik <span class="text-slate-400">Tanpa Setup Rumit.</span>
          </h1>
          <p class="text-xl text-slate-500 leading-relaxed mb-12 max-w-2xl">
            Solusi billing hotspot profesional yang dioptimalkan untuk ISP rumahan dan single admin. Menggabungkan Laravel 13, MikroTik, dan FreeRADIUS dalam satu alur kerja yang intuitif.
          </p>
          <div class="flex flex-wrap gap-4">
            <a href="/admin/login" class="px-8 py-4 bg-slate-900 text-white font-bold rounded hover:bg-slate-800 transition-all shadow-lg shadow-slate-200 active:scale-95">
              Mulai Sekarang
            </a>
            <a href="#features" class="px-8 py-4 bg-white border border-slate-200 text-slate-600 font-bold rounded hover:bg-slate-50 transition-all active:scale-95">
              Lihat Detail Fitur
            </a>
          </div>
        </div>
      </div>
    </header>

    <!-- Stats/Context -->
    <section class="py-20 bg-slate-900 text-white">
      <div class="max-w-7xl mx-auto px-8 grid md:grid-cols-3 gap-16">
        <div>
          <div class="text-4xl font-bold mb-4">01.</div>
          <h3 class="text-xl font-bold mb-4 text-slate-300">Skala Personal</h3>
          <p class="text-slate-400 leading-relaxed">Dikhususkan untuk ISP perumahan kecil atau RT-RW Net yang butuh kestabilan tanpa beban sistem yang berat.</p>
        </div>
        <div>
          <div class="text-4xl font-bold mb-4">02.</div>
          <h3 class="text-xl font-bold mb-4 text-slate-300">Single Admin</h3>
          <p class="text-slate-400 leading-relaxed">Dibuat untuk mempermudah pekerjaan admin tunggal melalui otomatisasi cron job dan notifikasi instan.</p>
        </div>
        <div>
          <div class="text-4xl font-bold mb-4">03.</div>
          <h3 class="text-xl font-bold mb-4 text-slate-300">Full Audit</h3>
          <p class="text-slate-400 leading-relaxed">Setiap aktivitas login, transaksi, dan perubahan data tercatat rapi dalam audit trail yang komprehensif.</p>
        </div>
      </div>
    </section>

    <!-- Features -->
    <section id="features" class="py-32 bg-white">
      <div class="max-w-7xl mx-auto px-8">
        <div class="mb-20">
          <span class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-4 block">Arsitektur</span>
          <h2 class="text-4xl font-bold text-slate-900 tracking-tight">Fitur Inti Sistem</h2>
        </div>

        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-1">
          <div v-for="feature in features" :key="feature.title" class="p-10 border border-slate-100 hover:border-slate-200 transition-all group">
            <div class="w-12 h-12 bg-slate-50 rounded flex items-center justify-center mb-8 group-hover:bg-slate-900 group-hover:text-white transition-all">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" v-html="feature.icon"></svg>
            </div>
            <h4 class="text-lg font-bold mb-4">{{ feature.title }}</h4>
            <p class="text-sm text-slate-500 leading-relaxed">{{ feature.desc }}</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Use Cases -->
    <section id="use-cases" class="py-32 bg-slate-50">
      <div class="max-w-7xl mx-auto px-8">
        <div class="grid lg:grid-cols-2 gap-24 items-center">
          <div>
            <span class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-4 block">Implementasi</span>
            <h2 class="text-5xl font-bold text-slate-900 tracking-tight mb-8 leading-tight">Dirancang untuk Alur Kerja yang <span class="text-slate-400">Realistis.</span></h2>
            <div class="space-y-12">
              <div v-for="usecase in usecases" :key="usecase.title" class="flex gap-6">
                <div class="flex-shrink-0 w-8 h-8 rounded-full border-2 border-slate-200 flex items-center justify-center text-xs font-bold text-slate-400">
                  {{ usecase.id }}
                </div>
                <div>
                  <h4 class="font-bold mb-2">{{ usecase.title }}</h4>
                  <p class="text-sm text-slate-500 leading-relaxed">{{ usecase.desc }}</p>
                </div>
              </div>
            </div>
          </div>
          <div class="relative">
            <div class="aspect-square bg-slate-900 rounded shadow-2xl flex items-center justify-center p-12 overflow-hidden">
               <div class="w-full grid grid-cols-4 gap-4 opacity-20">
                 <div v-for="i in 16" :key="i" class="h-24 bg-white/20 rounded"></div>
               </div>
               <div class="absolute inset-0 flex items-center justify-center text-white/10 font-black text-9xl select-none">
                 NB
               </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Footer -->
    <footer class="py-12 border-t border-slate-100">
      <div class="max-w-7xl mx-auto px-8 flex flex-col md:flex-row justify-between items-center gap-8">
        <div class="flex items-center gap-3 grayscale opacity-50">
          <div class="w-6 h-6 bg-slate-900 rounded flex items-center justify-center">
            <span class="text-[10px] text-white font-bold">N</span>
          </div>
          <span class="text-xs font-bold tracking-widest uppercase">NuxBill</span>
        </div>
        <div class="text-[11px] font-medium text-slate-400 uppercase tracking-widest">
          &copy; {{ new Date().getFullYear() }} Personal Project &bull; Laravel 13 &bull; SQLite
        </div>
      </div>
    </footer>
  </div>
</template>

<script setup>
const features = [
  {
    title: 'Admin Panel Modern',
    desc: 'Manajemen penuh Customer, Plan, Bandwidth, dan Voucher menggunakan Vue 3 + Inertia yang responsif.',
    icon: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01" />'
  },
  {
    title: 'Self-Service Portal',
    desc: 'Memberikan akses mandiri bagi pelanggan untuk melihat riwayat transaksi dan status layanan.',
    icon: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />'
  },
  {
    title: 'Otentikasi RADIUS',
    desc: 'Dukungan FreeRADIUS via REST API untuk autentikasi yang aman dan accounting yang akurat.',
    icon: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />'
  },
  {
    title: 'Voucher Engine',
    desc: 'Pembuatan voucher massal dengan template cetak yang profesional dan fungsional.',
    icon: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />'
  },
  {
    title: 'Multi-Channel Alert',
    desc: 'Notifikasi otomatis melalui WhatsApp (GOWA) dan Telegram untuk setiap aktivitas penting.',
    icon: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />'
  },
  {
    title: 'Cron & Otomatisasi',
    desc: 'Cron job memantau masa aktif paket, menonaktifkan akses otomatis saat habis, dan mengirim pengingat berkala.',
    icon: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />'
  }
]

const usecases = [
  {
    id: '01',
    title: 'Pendaftaran & Aktivasi',
    desc: 'Admin mendaftarkan customer, sistem secara otomatis menyiapkan kredensial di Mikrotik/RADIUS.'
  },
  {
    id: '02',
    title: 'Siklus Penagihan',
    desc: 'Sistem mengirim invoice QR statis, pelanggan membayar, dan admin melakukan aktivasi paket secara manual atau via voucher.'
  },
  {
    id: '03',
    title: 'Manajemen Masa Aktif',
    desc: 'Cron job memantau paket aktif dan secara otomatis menonaktifkan akses saat masa berlaku habis.'
  }
]
</script>
