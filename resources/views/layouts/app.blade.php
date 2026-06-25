<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'WIAKMS') — BBWS NT II</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="h-full bg-slate-50 font-[Inter] antialiased"
      x-data="{
          sidebarOpen: false,
          sidebarCollapsed: $persist(false).as('sidebar_collapsed'),
      }">

{{-- Overlay mobile --}}
<div x-show="sidebarOpen" x-cloak @click="sidebarOpen = false"
     class="fixed inset-0 z-20 bg-black/50 backdrop-blur-sm lg:hidden"></div>

{{-- ── SIDEBAR ─────────────────────────────────────────────────── --}}
<aside class="fixed inset-y-0 left-0 z-30 flex flex-col bg-[#0B2545] border-r border-white/5
               shadow-2xl transition-all duration-300 -translate-x-full lg:translate-x-0"
       :class="sidebarCollapsed ? 'w-[68px]' : 'w-64'"
       :style="sidebarOpen ? 'transform: translateX(0)' : ''">

    {{-- Logo --}}
    <div class="flex items-center gap-3 px-4 py-[18px] border-b border-white/10 flex-shrink-0 min-w-0">
        <div class="flex-shrink-0 w-9 h-9 rounded-xl bg-gradient-to-br from-sky-400 to-blue-600
                    flex items-center justify-center shadow-lg">
            <i class="ti ti-droplet-filled-2 text-white text-lg"></i>
        </div>
        <div x-show="!sidebarCollapsed" x-cloak class="overflow-hidden">
            <p class="text-white font-semibold text-[13px] leading-tight">WIAKMS</p>
            <p class="text-sky-400 text-[11px] mt-0.5">BBWS Nusa Tenggara II</p>
        </div>
    </div>

    {{-- Nav --}}
    <nav class="flex-1 overflow-y-auto py-3 px-2 space-y-0.5">

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
        <x-nav-item route="superadmin.users.index"     icon="ti-users"       label="Pengguna" />
        @endcan
        @can('role.view')
        <x-nav-item route="superadmin.roles.index"     icon="ti-shield-lock" label="Role & Akses" />
        @endcan
        @can('unit_kerja.view')
        <x-nav-item route="superadmin.unit-kerja.index" icon="ti-sitemap"    label="Unit Kerja" />
        @endcan
        @role('superadmin')
        <x-nav-item route="superadmin.activity-log"    icon="ti-history"     label="Audit Log" />
        @endrole

    </nav>

    {{-- Collapse button --}}
    <div class="flex-shrink-0 p-2 border-t border-white/10">
        <button @click="sidebarCollapsed = !sidebarCollapsed"
                class="w-full p-2 flex items-center justify-center rounded-lg
                       text-slate-400 hover:text-white hover:bg-white/10 transition-colors">
            <i :class="sidebarCollapsed ? 'ti-chevrons-right' : 'ti-chevrons-left'" class="ti text-sm"></i>
        </button>
    </div>
</aside>

{{-- ── MAIN AREA ─────────────────────────────────────────────────── --}}
{{-- Pakai CSS variable via style binding agar tidak bergantung Tailwind safelist --}}
<div class="flex flex-col min-h-full transition-all duration-300 lg:pl-64"
     :style="sidebarCollapsed ? 'padding-left: 68px' : 'padding-left: 256px'"
     style="padding-left: 0">

    {{-- TOPBAR --}}
    <header class="sticky top-0 z-10 bg-white/95 backdrop-blur border-b border-slate-200 h-14">
        <div class="h-full flex items-center gap-3 px-4 lg:px-6">

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
                    <input type="text" name="q" placeholder="Cari aset, dokumen, knowledge..."
                           class="pl-9 pr-4 py-1.5 text-sm bg-slate-100 border border-transparent rounded-lg
                                  focus:outline-none focus:bg-white focus:border-sky-300 w-64 transition-all">
                </div>
            </form>

            {{-- Notifikasi --}}
            @php $unreadCount = auth()->user()->unreadNotifications()->count(); @endphp
            <div class="relative" x-data="{ open: false }">
                <button @click="open = !open" @click.outside="open = false"
                        class="relative p-2 text-slate-500 hover:text-slate-700 hover:bg-slate-100 rounded-lg transition-colors">
                    <i class="ti ti-bell text-lg"></i>
                    @if($unreadCount > 0)
                    <span class="absolute -top-0.5 -right-0.5 w-4 h-4 bg-red-500 text-white text-[10px]
                                 font-bold rounded-full flex items-center justify-center">
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
                            <button class="text-xs text-sky-600 hover:underline">Tandai semua dibaca</button>
                        </form>
                        @endif
                    </div>
                    @forelse(auth()->user()->notifications()->limit(6)->get() as $notif)
                    <div class="px-4 py-3 hover:bg-slate-50 {{ !$notif->is_read ? 'bg-sky-50/40' : '' }}">
                        <p class="text-sm font-medium text-slate-800">{{ $notif->title }}</p>
                        <p class="text-xs text-slate-500 mt-0.5 line-clamp-2">{{ $notif->message }}</p>
                        <p class="text-xs text-slate-400 mt-1">{{ $notif->created_at->diffForHumans() }}</p>
                    </div>
                    @empty
                    <p class="px-4 py-6 text-center text-sm text-slate-400">Tidak ada notifikasi</p>
                    @endforelse
                    <div class="px-4 py-2 border-t border-slate-100">
                        <a href="{{ route('notifications.index') }}" class="text-xs text-sky-600 hover:underline">
                            Lihat semua →
                        </a>
                    </div>
                </div>
            </div>

            {{-- User menu --}}
            <div class="relative" x-data="{ open: false }">
                <button @click="open = !open" @click.outside="open = false"
                        class="flex items-center gap-2 px-2 py-1.5 rounded-lg hover:bg-slate-100 transition-colors">
                    <div class="w-7 h-7 rounded-full bg-gradient-to-br from-sky-500 to-blue-600
                                flex items-center justify-center text-white text-xs font-bold">
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

    {{-- KONTEN --}}
    <main class="flex-1 p-4 lg:p-6">

        {{-- Pending alert --}}
        @role('superadmin|admin_satker')
        @php $pendingCount = \App\Models\User::pending()->count(); @endphp
        @if($pendingCount > 0)
        <div class="mb-4 flex items-center gap-3 bg-amber-50 border border-amber-200
                    rounded-xl px-4 py-3 text-sm text-amber-800">
            <i class="ti ti-user-clock text-amber-500 text-lg flex-shrink-0"></i>
            <p><strong>{{ $pendingCount }} akun</strong> menunggu persetujuan.</p>
            <a href="{{ route('admin.approvals') }}"
               class="ml-auto flex-shrink-0 text-xs font-medium text-amber-700 hover:underline">
                Proses sekarang →
            </a>
        </div>
        @endif
        @endrole

        {{-- Flash messages --}}
        @foreach(['success' => ['emerald','ti-circle-check'], 'error' => ['red','ti-circle-x'], 'warning' => ['amber','ti-alert-triangle']] as $type => [$color, $icon])
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
</div>

@stack('scripts')
</body>
</html>
