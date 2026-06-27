{{-- components/page-header.blade.php --}}
@props(['title', 'desc' => null, 'icon' => null])

<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div class="flex items-center gap-3">
        @if($icon)
        <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0"
             style="background: rgba(0,51,102,0.08); border: 1px solid rgba(0,51,102,0.12)">
            <i class="ti {{ $icon }} text-xl" style="color: #003366"></i>
        </div>
        @endif
        <div>
            <h1 class="text-lg font-bold text-slate-800 leading-tight">{{ $title }}</h1>
            @if($desc)
            <p class="text-sm text-slate-500 mt-0.5">{{ $desc }}</p>
            @endif
        </div>
    </div>

    @if($slot->isNotEmpty())
    <div class="flex items-center gap-2 flex-shrink-0 flex-wrap">
        {{ $slot }}
    </div>
    @endif
</div>
