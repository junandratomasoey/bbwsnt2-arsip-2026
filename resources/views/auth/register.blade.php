<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun — WIAKMS BBWS NT II</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full bg-slate-100 font-[Inter] flex items-center justify-center p-4">

<div class="w-full max-w-lg">

    {{-- Header --}}
    <div class="text-center mb-6">
        <div class="inline-flex w-16 h-16 rounded-2xl bg-[#003366] items-center justify-center
                    shadow-xl mb-4" style="box-shadow: 0 0 0 3px #F4A81D, 0 8px 32px rgba(0,51,102,0.3)">
            <svg viewBox="0 0 40 40" fill="none" class="w-12 h-12">
                <g transform="translate(20,22)">
                    <ellipse cx="0" cy="0" rx="10" ry="4" fill="#F4A81D" opacity="0.9"/>
                    <ellipse cx="-7" cy="-5" rx="4" ry="8" fill="#F4A81D" opacity="0.85" transform="rotate(-30,-7,-5)"/>
                    <ellipse cx="7" cy="-5" rx="4" ry="8" fill="#F4A81D" opacity="0.85" transform="rotate(30,7,-5)"/>
                    <ellipse cx="-4" cy="-11" rx="3" ry="7" fill="#F4A81D" opacity="0.8" transform="rotate(-15,-4,-11)"/>
                    <ellipse cx="4" cy="-11" rx="3" ry="7" fill="#F4A81D" opacity="0.8" transform="rotate(15,4,-11)"/>
                    <ellipse cx="0" cy="-14" rx="2.5" ry="6" fill="#FDB913"/>
                    <rect x="-1" y="0" width="2" height="5" fill="#F4A81D" rx="1"/>
                </g>
            </svg>
        </div>
        <div class="flex items-center justify-center gap-2 mb-1">
            <div class="h-px w-8" style="background:#F4A81D"></div>
            <span class="text-xs font-bold tracking-widest uppercase" style="color:#003366">
                Kementerian Pekerjaan Umum
            </span>
            <div class="h-px w-8" style="background:#F4A81D"></div>
        </div>
        <h1 class="text-xl font-bold text-slate-800">Daftar Akun WIAKMS</h1>
        <p class="text-sm text-slate-500 mt-1">BBWS Nusa Tenggara II</p>
    </div>

    {{-- Info pending --}}
    <div class="bg-amber-50 border border-amber-300 rounded-xl px-4 py-3 mb-5 flex items-start gap-2.5">
        <i class="ti ti-alert-triangle flex-shrink-0 mt-0.5" style="color:#F4A81D"></i>
        <p class="text-sm text-amber-800">
            Akun baru akan berstatus <strong>pending</strong> hingga disetujui administrator satker.
            Gunakan email resmi instansi.
        </p>
    </div>

    @if($errors->any())
    <div class="mb-4 bg-red-50 border border-red-200 rounded-xl px-4 py-3 text-sm text-red-700">
        @foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach
    </div>
    @endif

    {{-- Form --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        {{-- Strip kuning --}}
        <div class="h-1" style="background: linear-gradient(90deg, #003366, #F4A81D)"></div>

        <form method="POST" action="{{ route('register') }}" class="p-6 space-y-4">
            @csrf

            <div class="grid grid-cols-2 gap-4">
                <div class="col-span-2">
                    <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wide mb-1.5">
                        Nama Lengkap <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="name" required value="{{ old('name') }}"
                           placeholder="Nama sesuai SK jabatan"
                           class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm
                                  hover:border-slate-300 focus:outline-none focus:border-blue-400 focus:ring-1 focus:ring-blue-200 transition-all
                                  @error('name') border-red-300 @enderror">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wide mb-1.5">NIP</label>
                    <input type="text" name="nip" value="{{ old('nip') }}"
                           placeholder="18 digit (opsional)"
                           maxlength="18"
                           class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm font-mono
                                  hover:border-slate-300 focus:outline-none focus:border-blue-400 transition-all">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wide mb-1.5">No. HP</label>
                    <input type="text" name="no_hp" value="{{ old('no_hp') }}"
                           placeholder="08xx-xxxx-xxxx"
                           class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm
                                  hover:border-slate-300 focus:outline-none focus:border-blue-400 transition-all">
                </div>
                <div class="col-span-2">
                    <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wide mb-1.5">
                        Email <span class="text-red-500">*</span>
                    </label>
                    <input type="email" name="email" required value="{{ old('email') }}"
                           placeholder="nama@instansi.go.id"
                           class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm
                                  hover:border-slate-300 focus:outline-none focus:border-blue-400 transition-all
                                  @error('email') border-red-300 @enderror">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wide mb-1.5">
                        Jabatan
                    </label>
                    <input type="text" name="jabatan_struktural" value="{{ old('jabatan_struktural') }}"
                           placeholder="Jabatan struktural"
                           class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm
                                  hover:border-slate-300 focus:outline-none focus:border-blue-400 transition-all">
                </div>
                <div></div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wide mb-1.5">
                        Password <span class="text-red-500">*</span>
                    </label>
                    <input type="password" name="password" required placeholder="Min. 8 karakter"
                           class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm
                                  hover:border-slate-300 focus:outline-none focus:border-blue-400 transition-all
                                  @error('password') border-red-300 @enderror">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wide mb-1.5">
                        Konfirmasi <span class="text-red-500">*</span>
                    </label>
                    <input type="password" name="password_confirmation" required placeholder="Ulangi password"
                           class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm
                                  hover:border-slate-300 focus:outline-none focus:border-blue-400 transition-all">
                </div>
            </div>

            <button type="submit"
                    class="w-full mt-2 py-3 text-white text-sm font-bold rounded-xl tracking-wide
                           transition-all hover:opacity-95 active:scale-[0.99]"
                    style="background: linear-gradient(135deg, #003366, #1A5276);
                           box-shadow: 0 4px 16px rgba(0,51,102,0.3)">
                <i class="ti ti-user-plus mr-2"></i>
                DAFTAR AKUN
            </button>

            <p class="text-center text-sm text-slate-500 pt-1">
                Sudah punya akun?
                <a href="{{ route('login') }}" class="font-semibold hover:underline" style="color:#003366">
                    Masuk di sini
                </a>
            </p>
        </form>
    </div>

    <p class="text-center text-xs text-slate-400 mt-5">
        © {{ date('Y') }} Kementerian Pekerjaan Umum RI — BBWS Nusa Tenggara II
    </p>
</div>
</body>
</html>
