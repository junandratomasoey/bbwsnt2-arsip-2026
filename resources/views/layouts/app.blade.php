<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'WIAKMS') — BBWS NT II</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
    <style>
        /* ── Warna KemenPU ── */
        :root {
            --pu-kuning: #F4A81D;
            --pu-kuning-muda: #FDB913;
            --pu-biru: #003366;
            --pu-biru-medium: #1A5276;
            --pu-biru-terang: #1F618D;
        }
        .sidebar-pu { background: linear-gradient(180deg, #003366 0%, #012a55 60%, #011e3d 100%); }
        .sidebar-accent { border-left: 3px solid #F4A81D; background: rgba(244,168,29,0.12); }
        .logo-pu-ring { box-shadow: 0 0 0 2px #F4A81D; }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="h-full bg-[#F0F4F8] font-[Inter] antialiased"
      x-data="{
          sidebarOpen: false,
          sidebarCollapsed: $persist(false).as('sidebar_collapsed'),
      }">

{{-- Overlay mobile --}}
<div x-show="sidebarOpen" x-cloak @click="sidebarOpen = false"
     class="fixed inset-0 z-20 bg-black/60 backdrop-blur-sm lg:hidden"></div>

{{-- ── SIDEBAR ──────────────────────────────────────────────────────── --}}
<aside class="sidebar-pu fixed inset-y-0 left-0 z-30 flex flex-col
               shadow-2xl transition-all duration-300 -translate-x-full lg:translate-x-0"
       :class="sidebarCollapsed ? 'w-[68px]' : 'w-64'"
       :style="sidebarOpen ? 'transform: translateX(0)' : ''">

    {{-- ── LOGO / HEADER SIDEBAR ── --}}
    <div class="flex items-center gap-3 px-3 py-3 border-b border-white/10 flex-shrink-0 bg-black/20">
        {{-- Logo KemenPU SVG --}}
        <div class="flex-shrink-0 w-10 h-10 rounded-lg logo-pu-ring bg-white flex items-center justify-center overflow-hidden">
            {{-- Logo PU --}}
            <img src="{{ asset('images/logo-pu.png') }}" alt="Logo Kementerian PU" class="w-8 h-8 object-contain">
        </div>
        <div x-show="!sidebarCollapsed" x-cloak class="overflow-hidden min-w-0">
            <p class="text-white font-bold text-[13px] leading-tight tracking-wide">WIAKMS</p>
            <p class="text-[10px] mt-0.5 leading-tight" style="color:#F4A81D">BBWS Nusa Tenggara II</p>
        </div>
    </div>

    {{-- ── STRIP KUNING TIPIS DI BAWAH LOGO ── --}}
    <div class="h-0.5 flex-shrink-0" style="background: linear-gradient(90deg, #F4A81D, #FDB913, transparent)"></div>

    {{-- Nav --}}
    <nav class="flex-1 overflow-y-auto py-2 px-1.5 space-y-0.5 scrollbar-thin">

        <x-nav-item route="dashboard" icon="ti-home-2" label="Dashboard" />

        @can('dashboard.executive')
        <x-nav-item route="dashboard.executive" icon="ti-chart-bar" label="Eksekutif" />
        @endcan

        @can('asset.view')
        <x-nav-section label="Infrastruktur & Aset" />
        <x-nav-item route="assets.index"       icon="ti-building-bridge" label="Aset Infrastruktur" />
        <x-nav-item route="gis.index"          icon="ti-map-2"           label="Peta GIS" />
        @endcan

        @can('project.view')
        <x-nav-section label="Proyek & OP" />
        <x-nav-item route="projects.index"     icon="ti-timeline"        label="Proyek" />
        @endcan
        @can('op_record.view')
        <x-nav-item route="op.records.index"   icon="ti-settings-2"      label="Rekaman OP" />
        <x-nav-item route="op.schedules.index" icon="ti-calendar-event"  label="Jadwal OP" />
        <x-nav-item route="op.map"             icon="ti-map-pin-filled"  label="Peta OP" />
        @endcan

        @can('document.view')
        <x-nav-section label="Dokumen & Arsip" />
        <x-nav-item route="documents.index"    icon="ti-files"           label="Semua Dokumen" />
        <x-nav-item route="locations.index"    icon="ti-box"             label="Lokasi Fisik" />
        @endcan
        @can('loan.view')
        <x-nav-item route="loans.index"        icon="ti-book-download"   label="Peminjaman" />
        @endcan

        @can('knowledge.view')
        <x-nav-section label="Pengetahuan" />
        <x-nav-item route="knowledge.index"    icon="ti-brain"           label="Knowledge Base" />
        @endcan
        @can('library.view')
        <x-nav-item route="library.index"      icon="ti-books"           label="Perpustakaan" />
        @endcan

        @can('report.view')
        <x-nav-section label="Laporan" />
        <x-nav-item route="reports.index"      icon="ti-chart-dots"      label="Rekap & Laporan" />
        @endcan

        @role('superadmin|admin_satker')
        <x-nav-section label="Administrasi" />
        @endrole
        @can('user.view')
        <x-nav-item route="superadmin.users.index"      icon="ti-users"       label="Pengguna" />
        @endcan
        @can('role.view')
        <x-nav-item route="superadmin.roles.index"      icon="ti-shield-lock" label="Role & Akses" />
        @endcan
        @can('unit_kerja.view')
        <x-nav-item route="superadmin.unit-kerja.index" icon="ti-sitemap"     label="Unit Kerja" />
        @endcan
        @role('superadmin')
        <x-nav-section label="Data Referensi" />
        <x-nav-item route="superadmin.asset-types.index"    icon="ti-building-bridge"  label="Jenis Aset" />
        <x-nav-item route="superadmin.document-types.index" icon="ti-file-description" label="Jenis Dokumen" />
        <x-nav-item route="superadmin.activity-log"         icon="ti-history"          label="Audit Log" />
        
        @endrole
    </nav>

    {{-- ── COLLAPSE BUTTON ── --}}
    <div class="flex-shrink-0 p-2 border-t border-white/10">
        <button @click="sidebarCollapsed = !sidebarCollapsed"
                class="w-full p-2 flex items-center justify-center rounded-lg
                       text-white/40 hover:text-white hover:bg-white/10 transition-colors">
            <i :class="sidebarCollapsed ? 'ti-chevrons-right' : 'ti-chevrons-left'" class="ti text-sm"></i>
        </button>
    </div>
</aside>

{{-- ── MAIN AREA ─────────────────────────────────────────────────────── --}}
<div class="flex flex-col min-h-full transition-all duration-300"
     :style="sidebarCollapsed ? 'padding-left: 68px' : 'padding-left: 256px'"
     style="padding-left: 0">

    {{-- ── TOPBAR ── --}}
    <header class="sticky top-0 z-10 bg-white border-b border-slate-200 shadow-sm h-14">

        {{-- Strip kuning tipis di atas topbar --}}
        <div class="absolute top-0 left-0 right-0 h-0.5" style="background: #F4A81D"></div>

        <div class="h-full flex items-center gap-3 px-4 lg:px-6 pt-0.5">

            {{-- Toggle mobile --}}
            <button class="lg:hidden text-slate-500 hover:text-slate-700 p-1"
                    @click="sidebarOpen = !sidebarOpen">
                <i class="ti ti-menu-2 text-xl"></i>
            </button>

            {{-- Breadcrumb --}}
            <nav class="flex-1 flex items-center gap-1.5 text-sm min-w-0 overflow-hidden">
                @yield('breadcrumb')
            </nav>

            {{-- Search --}}
            <form action="{{ route('search') }}" method="GET" class="hidden md:flex">
                <div class="relative">
                    <i class="ti ti-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                    <input type="text" name="q" placeholder="Cari aset, dokumen..."
                           class="pl-9 pr-4 py-1.5 text-sm bg-slate-100 border border-transparent rounded-lg
                                  focus:outline-none focus:bg-white focus:border-blue-300 w-52 transition-all">
                </div>
            </form>

            {{-- Notifikasi --}}
            @php $unreadCount = auth()->user()->unreadNotifications()->count(); @endphp
            <div class="relative" x-data="{ open: false }">
                <button @click="open = !open" @click.outside="open = false"
                        class="relative p-2 text-slate-500 hover:text-slate-700 hover:bg-slate-100 rounded-lg transition-colors">
                    <i class="ti ti-bell text-lg"></i>
                    @if($unreadCount > 0)
                    <span class="absolute -top-0.5 -right-0.5 w-4 h-4 text-white text-[10px]
                                 font-bold rounded-full flex items-center justify-center"
                          style="background:#F4A81D">
                        {{ $unreadCount > 9 ? '9+' : $unreadCount }}
                    </span>
                    @endif
                </button>
                <div x-show="open" x-cloak
                     class="absolute right-0 top-full mt-1 w-80 bg-white border border-slate-200
                            rounded-xl shadow-xl py-1 z-50 max-h-96 overflow-y-auto">
                    <div class="px-4 py-2.5 border-b border-slate-100 flex items-center justify-between">
                        <p class="text-sm font-semibold text-slate-800">Notifikasi</p>
                        @if($unreadCount > 0)
                        <form action="{{ route('notifications.read-all') }}" method="POST">
                            @csrf
                            <button class="text-xs hover:underline" style="color:#003366">Tandai semua dibaca</button>
                        </form>
                        @endif
                    </div>
                    @forelse(auth()->user()->notifications()->limit(6)->get() as $notif)
                    <div class="px-4 py-3 hover:bg-slate-50 {{ !$notif->is_read ? 'bg-amber-50/40' : '' }}">
                        <p class="text-sm font-medium text-slate-800">{{ $notif->title }}</p>
                        <p class="text-xs text-slate-500 mt-0.5 line-clamp-2">{{ $notif->message }}</p>
                        <p class="text-xs text-slate-400 mt-1">{{ $notif->created_at->diffForHumans() }}</p>
                    </div>
                    @empty
                    <p class="px-4 py-6 text-center text-sm text-slate-400">Tidak ada notifikasi</p>
                    @endforelse
                    <div class="px-4 py-2 border-t border-slate-100">
                        <a href="{{ route('notifications.index') }}" class="text-xs hover:underline" style="color:#003366">
                            Lihat semua →
                        </a>
                    </div>
                </div>
            </div>

            {{-- User menu --}}
            <div class="relative" x-data="{ open: false }">
                <button @click="open = !open" @click.outside="open = false"
                        class="flex items-center gap-2 px-2 py-1.5 rounded-lg hover:bg-slate-100 transition-colors">
                    {{-- Avatar dengan warna PU --}}
                    <div class="w-7 h-7 rounded-full flex items-center justify-center
                                text-white text-xs font-bold flex-shrink-0"
                         style="background: linear-gradient(135deg, #003366, #1A5276)">
                        {{ auth()->user()->inisial() }}
                    </div>
                    <div class="hidden sm:block text-left">
                        <p class="text-sm font-medium text-slate-800 leading-tight max-w-[120px] truncate">
                            {{ auth()->user()->name }}
                        </p>
                        <p class="text-xs text-slate-500 leading-tight">{{ auth()->user()->namaRole() }}</p>
                    </div>
                    <i class="ti ti-chevron-down text-slate-400 text-xs hidden sm:block"></i>
                </button>
                <div x-show="open" x-cloak
                     class="absolute right-0 top-full mt-1 w-52 bg-white border border-slate-200
                            rounded-xl shadow-lg py-1 z-50">
                    <div class="px-4 py-3 border-b border-slate-100">
                        <p class="text-sm font-medium text-slate-800 truncate">{{ auth()->user()->name }}</p>
                        <p class="text-xs text-slate-500 mt-0.5">{{ auth()->user()->namaRole() }}</p>
                    </div>
                    <a href="{{ route('profile.index') }}"
                       class="flex items-center gap-2.5 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">
                        <i class="ti ti-user text-slate-400"></i> Profil Saya
                    </a>
                    <div class="border-t border-slate-100 mt-1">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit"
                                    class="w-full flex items-center gap-2.5 px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                                <i class="ti ti-logout text-red-400"></i> Keluar
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </header>

    {{-- ── KONTEN ── --}}
    <main class="flex-1 p-4 lg:p-6">

        {{-- Pending alert --}}
        @role('superadmin|admin_satker')
        @php $pendingCount = \App\Models\User::pending()->count(); @endphp
        @if($pendingCount > 0)
        <div class="mb-4 flex items-center gap-3 bg-amber-50 border border-amber-300
                    rounded-xl px-4 py-3 text-sm text-amber-800">
            <i class="ti ti-user-clock text-amber-500 text-lg flex-shrink-0"></i>
            <p><strong>{{ $pendingCount }} akun</strong> menunggu persetujuan.</p>
            <a href="{{ route('admin.approvals') }}"
               class="ml-auto flex-shrink-0 text-xs font-semibold hover:underline" style="color:#003366">
                Proses sekarang →
            </a>
        </div>
        @endif
        @endrole

        {{-- Flash messages --}}
        @foreach(['success'=>['green','ti-circle-check'], 'error'=>['red','ti-circle-x'], 'warning'=>['amber','ti-alert-triangle']] as $type=>[$color,$icon])
        @if(session($type))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
             class="mb-4 flex items-start gap-3 bg-{{ $color }}-50 border border-{{ $color }}-200
                    text-{{ $color }}-800 px-4 py-3 rounded-xl text-sm">
            <i class="ti {{ $icon }} text-{{ $color }}-500 text-lg flex-shrink-0 mt-0.5"></i>
            <span class="flex-1">{!! session($type) !!}</span>
            <button @click="show = false" class="text-{{ $color }}-400 hover:text-{{ $color }}-600 ml-2">
                <i class="ti ti-x text-sm"></i>
            </button>
        </div>
        @endif
        @endforeach

        @if($errors->any())
        <div class="mb-4 bg-red-50 border border-red-200 rounded-xl px-4 py-3 text-sm text-red-800">
            <p class="font-medium mb-2 flex items-center gap-2">
                <i class="ti ti-alert-circle text-red-500"></i>
                Terdapat {{ $errors->count() }} kesalahan:
            </p>
            <ul class="list-disc list-inside space-y-1 text-red-700">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        @yield('content')
    </main>

    {{-- ── FOOTER ── --}}
    <footer class="border-t border-slate-200 bg-white px-6 py-3">
        <div class="flex items-center justify-between text-xs text-slate-400">
            <span>© {{ date('Y') }} Kementerian Pekerjaan Umum — BBWS Nusa Tenggara II</span>
            <span>WIAKMS v1.0</span>
        </div>
    </footer>
</div>

@stack('scripts')
</body>
</html>
