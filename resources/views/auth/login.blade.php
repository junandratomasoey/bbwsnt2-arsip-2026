<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk — WIAKMS BBWS NT II</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        [x-cloak] { display: none !important; }
        .pu-sidebar {
            background: linear-gradient(170deg, #001f3f 0%, #003366 40%, #012a55 100%);
        }
        .pu-gold { color: #F4A81D; }
        .pu-gold-bg { background-color: #F4A81D; }
        .pu-blue { color: #003366; }
        .pu-blue-bg { background-color: #003366; }
        .teratai-glow {
            filter: drop-shadow(0 0 20px rgba(244,168,29,0.4));
        }
        @keyframes floatUp {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-8px); }
        }
        .float-anim { animation: floatUp 4s ease-in-out infinite; }
    </style>
</head>
<body class="h-full bg-slate-100 font-[Inter]" x-data>

<div class="min-h-full flex">

    {{-- ── PANEL KIRI — BRANDING KEMENPU ────────────────────────── --}}
    <div class="pu-sidebar hidden lg:flex flex-col w-[44%] relative overflow-hidden">

        {{-- Pola geometrik dekoratif --}}
        <div class="absolute inset-0 overflow-hidden pointer-events-none">
            {{-- Lingkaran besar transparan --}}
            <div class="absolute -top-32 -right-32 w-[500px] h-[500px] rounded-full border border-white/5"></div>
            <div class="absolute -bottom-20 -left-20 w-80 h-80 rounded-full border border-white/5"></div>
            {{-- Garis diagonal dekoratif --}}
            <div class="absolute bottom-0 left-0 right-0 h-1" style="background: linear-gradient(90deg, transparent, #F4A81D, transparent)"></div>
            {{-- Pola grid halus --}}
            <div class="absolute inset-0" style="background-image: radial-gradient(circle at 1px 1px, rgba(255,255,255,0.04) 1px, transparent 0); background-size: 32px 32px;"></div>
        </div>

        <div class="relative flex flex-col h-full p-10 xl:p-14">

            {{-- ── HEADER: Logo KemenPU ── --}}
            <div class="flex items-center gap-4 mb-auto">
                {{-- Logo KemenPU SVG --}}
                <div class="w-16 h-16 bg-white rounded-2xl flex items-center justify-center shadow-lg flex-shrink-0"
                     style="box-shadow: 0 0 0 2px #F4A81D, 0 8px 32px rgba(0,0,0,0.3)">
                    <img
                        src="{{ asset('images/logo-pu.png') }}"
                        alt="Logo Kementerian PU"
                        class="w-14 h-14 object-contain">
                </div>
                <div>
                    <p class="text-xs font-medium tracking-[0.2em] uppercase mb-0.5" style="color: rgba(255,255,255,0.5)">
                        Kementerian Pekerjaan Umum
                    </p>
                    <p class="text-white font-bold text-lg leading-tight">
                        Balai Besar Wilayah Sungai
                    </p>
                    <p class="font-bold text-lg leading-tight" style="color: #F4A81D">Nusa Tenggara II</p>
                </div>
            </div>

            {{-- ── KONTEN TENGAH ── --}}
            <div class="my-10 float-anim">
                {{-- Ikon air besar --}}
                <div class="mb-6">
                    <svg viewBox="0 0 120 80" class="w-32 teratai-glow opacity-80">
                        <path d="M10 70 Q30 20 60 10 Q90 20 110 70" stroke="#F4A81D" stroke-width="2" fill="none" opacity="0.6"/>
                        <path d="M20 70 Q40 30 60 20 Q80 30 100 70" stroke="#FDB913" stroke-width="1.5" fill="none" opacity="0.5"/>
                        <circle cx="60" cy="12" r="6" fill="#F4A81D" opacity="0.9"/>
                        <path d="M54 12 Q57 6 60 4 Q63 6 66 12" fill="#F4A81D" opacity="0.7"/>
                    </svg>
                </div>

                <h1 class="text-white text-3xl xl:text-4xl font-bold leading-tight mb-4">
                    Water Infrastructure<br>
                    <span style="color: #F4A81D">Asset & Knowledge</span><br>
                    Management System
                </h1>
                <div class="w-12 h-0.5 mb-4" style="background: #F4A81D"></div>
                <p class="text-sm xl:text-base leading-relaxed" style="color: rgba(255,255,255,0.6)">
                    Platform terintegrasi untuk pengelolaan aset infrastruktur,
                    monitoring proyek, operasi dan pemeliharaan,
                    serta manajemen dokumen BBWS Nusa Tenggara II.
                </p>
            </div>

            {{-- ── FITUR LIST ── --}}
            <div class="space-y-2.5">
                @foreach([
                    ['ti-building-bridge', 'Inventarisasi & penilaian kondisi aset'],
                    ['ti-timeline',        'Monitoring proyek & realisasi anggaran'],
                    ['ti-settings-2',      'Rekaman operasi & pemeliharaan'],
                    ['ti-files',           'Manajemen dokumen & arsip digital'],
                    ['ti-map-2',           'Peta GIS sebaran infrastruktur SDA'],
                ] as [$icon, $text])
                <div class="flex items-center gap-3">
                    <div class="w-7 h-7 rounded-lg flex items-center justify-center flex-shrink-0"
                         style="background: rgba(244,168,29,0.15); border: 1px solid rgba(244,168,29,0.3)">
                        <i class="ti {{ $icon }} text-sm" style="color: #F4A81D"></i>
                    </div>
                    <span class="text-sm" style="color: rgba(255,255,255,0.65)">{{ $text }}</span>
                </div>
                @endforeach
            </div>

            {{-- Footer sidebar --}}
            <div class="mt-8 pt-6 border-t border-white/10">
                <p class="text-xs" style="color: rgba(255,255,255,0.3)">
                    © {{ date('Y') }} Kementerian PU — BBWS NT II
                </p>
            </div>
        </div>
    </div>

    {{-- ── PANEL KANAN — FORM LOGIN ───────────────────────────────── --}}
    <div class="flex-1 flex items-center justify-center p-6 bg-white lg:bg-slate-50">
        <div class="w-full max-w-md">

            {{-- Mobile: logo --}}
            <div class="flex lg:hidden items-center gap-3 mb-8">
                <div class="w-12 h-12 rounded-xl bg-[#003366] flex items-center justify-center
                            shadow-lg" style="box-shadow: 0 0 0 2px #F4A81D">
                    <i class="ti ti-droplet-filled-2 text-white text-xl"></i>
                </div>
                <div>
                    <p class="font-bold text-slate-800">WIAKMS — BBWS NT II</p>
                    <p class="text-xs text-slate-500">Kementerian Pekerjaan Umum</p>
                </div>
            </div>

            {{-- Heading --}}
            <div class="mb-8">
                <div class="flex items-center gap-2 mb-2">
                    <div class="h-0.5 w-8" style="background:#F4A81D"></div>
                    <span class="text-xs font-semibold tracking-widest uppercase" style="color:#003366">
                        Portal Masuk
                    </span>
                </div>
                <h2 class="text-2xl font-bold text-slate-800">Selamat Datang</h2>
                <p class="text-slate-500 text-sm mt-1">Masukkan kredensial untuk mengakses sistem</p>
            </div>

            {{-- Errors --}}
            @if(session('error'))
            <div class="mb-4 flex items-center gap-3 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-sm">
                <i class="ti ti-circle-x text-red-500 flex-shrink-0"></i>
                {{ session('error') }}
            </div>
            @endif

            @if($errors->any())
            <div class="mb-4 bg-red-50 border border-red-200 rounded-xl px-4 py-3 text-sm text-red-700">
                @foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach
            </div>
            @endif

            {{-- Form --}}
            <form method="POST" action="{{ route('login') }}" class="space-y-4">
                @csrf

                {{-- Email --}}
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wide">
                        Email
                    </label>
                    <div class="relative">
                        <i class="ti ti-mail absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400"></i>
                        <input id="email" type="email" name="email" required autofocus
                               value="{{ old('email') }}"
                               placeholder="nama@instansi.go.id"
                               class="w-full pl-10 pr-4 py-3 border rounded-xl text-sm
                                      focus:outline-none focus:ring-2 transition-all
                                      @error('email') border-red-300 bg-red-50 @else border-slate-200 hover:border-slate-300 @enderror"
                               style="focus:border-color: #003366">
                    </div>
                </div>

                {{-- Password --}}
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="text-xs font-semibold text-slate-600 uppercase tracking-wide">
                            Password
                        </label>
                        @if(Route::has('password.request'))
                        <a href="{{ route('password.request') }}"
                           class="text-xs font-medium hover:underline" style="color:#003366">
                            Lupa password?
                        </a>
                        @endif
                    </div>
                    <div class="relative" x-data="{ show: false }">
                        <i class="ti ti-lock absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400"></i>
                        <input :type="show ? 'text' : 'password'" name="password" required
                               placeholder="••••••••"
                               class="w-full pl-10 pr-10 py-3 border border-slate-200 rounded-xl text-sm
                                      hover:border-slate-300 focus:outline-none transition-all
                                      @error('password') border-red-300 bg-red-50 @enderror">
                        <button type="button" @click="show = !show"
                                class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                            <i :class="show ? 'ti-eye-off' : 'ti-eye'" class="ti text-sm"></i>
                        </button>
                    </div>
                </div>

                {{-- Remember --}}
                <div class="flex items-center gap-2">
                    <input id="remember" type="checkbox" name="remember"
                           class="rounded border-slate-300 focus:ring-2"
                           style="color:#003366; focus:ring-color:#003366">
                    <label for="remember" class="text-sm text-slate-600">Ingat saya di perangkat ini</label>
                </div>

                {{-- Submit --}}
                <button type="submit"
                        class="w-full py-3 text-white text-sm font-bold rounded-xl
                               transition-all shadow-lg hover:shadow-xl hover:opacity-95 active:scale-[0.99]
                               tracking-wide"
                        style="background: linear-gradient(135deg, #003366, #1A5276);
                               box-shadow: 0 4px 20px rgba(0,51,102,0.35)">
                    <i class="ti ti-login mr-2"></i>
                    MASUK KE SISTEM
                </button>
            </form>

            {{-- Garis pemisah --}}
            <div class="flex items-center gap-3 my-6">
                <div class="flex-1 h-px bg-slate-200"></div>
                <span class="text-xs text-slate-400">atau</span>
                <div class="flex-1 h-px bg-slate-200"></div>
            </div>

            {{-- Register --}}
            @if(Route::has('register'))
            <a href="{{ route('register') }}"
               class="w-full flex items-center justify-center gap-2 py-3 border-2 rounded-xl
                      text-sm font-semibold transition-all hover:bg-slate-50"
               style="border-color:#003366; color:#003366">
                <i class="ti ti-user-plus"></i>
                Daftar Akun Baru
            </a>
            @endif

            {{-- Info --}}
            <div class="mt-6 bg-amber-50 border border-amber-200 rounded-xl p-4">
                <div class="flex items-start gap-2.5">
                    <i class="ti ti-info-circle flex-shrink-0 mt-0.5" style="color:#F4A81D"></i>
                    <div>
                        <p class="text-xs font-semibold text-amber-800 mb-1">Informasi Akses</p>
                        <p class="text-xs text-amber-700 leading-relaxed">
                            Akun baru memerlukan persetujuan administrator satker.
                            Gunakan email resmi instansi dan hubungi admin jika akun belum aktif.
                        </p>
                    </div>
                </div>
            </div>

            <p class="text-center text-xs text-slate-400 mt-6">
                © {{ date('Y') }} Kementerian Pekerjaan Umum RI
            </p>
        </div>
    </div>
</div>

</body>
</html>
