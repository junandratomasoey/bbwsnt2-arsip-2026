{{-- components/stat-card.blade.php --}}
@props(['label', 'value', 'icon', 'color' => 'blue', 'sub' => null, 'href' => null])

@php
$colorMap = [
    'sky'    => ['#EBF5FF', '#003366', '#1A5276'],
    'blue'   => ['#EBF5FF', '#003366', '#1A5276'],
    'green'  => ['#EDFAF1', '#1E8449', '#27AE60'],
    'emerald'=> ['#EDFAF1', '#1E8449', '#27AE60'],
    'red'    => ['#FDEDEC', '#B03A2E', '#E74C3C'],
    'amber'  => ['#FEF9E7', '#9A7D0A', '#D4AC0D'],
    'yellow' => ['#FEF9E7', '#9A7D0A', '#F4A81D'],
    'purple' => ['#F5EEF8', '#6C3483', '#9B59B6'],
    'slate'  => ['#F2F3F4', '#34495E', '#566573'],
    'teal'   => ['#E8F8F5', '#0E6655', '#17A589'],
];
$c = $colorMap[$color] ?? $colorMap['blue'];
$bgLight   = $c[0];
$textDark  = $c[1];
$textMid   = $c[2];
@endphp

@if($href)
<a href="{{ $href }}" class="block group">
@else
<div>
@endif

<div class="bg-white rounded-xl border border-slate-200 p-4 hover:shadow-md transition-all duration-200
            group-hover:border-slate-300"
     style="border-left: 3px solid {{ $textMid }}">
    <div class="flex items-start justify-between gap-3">
        <div class="min-w-0 flex-1">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 mb-1.5 truncate">
                {{ $label }}
            </p>
            <p class="text-2xl font-bold leading-none" style="color: {{ $textDark }}">
                {{ $value }}
            </p>
            @if($sub)
            <p class="text-xs text-slate-400 mt-1.5 truncate">{{ $sub }}</p>
            @endif
        </div>
        <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0"
             style="background: {{ $bgLight }}">
            <i class="ti {{ $icon }} text-xl" style="color: {{ $textMid }}"></i>
        </div>
    </div>
</div>

@if($href)
</a>
@else
</div>
@endif
