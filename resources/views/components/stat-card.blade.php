{{-- resources/views/components/stat-card.blade.php --}}
@props(['label', 'value', 'icon', 'color' => 'sky', 'sub' => null, 'href' => null, 'trend' => null])
@php
$colorMap = [
    'sky'    => ['bg' => 'bg-sky-50',    'border' => 'border-sky-100',   'icon' => 'text-sky-600',    'val' => 'text-sky-700'],
    'green'  => ['bg' => 'bg-emerald-50','border' => 'border-emerald-100','icon' => 'text-emerald-600','val' => 'text-emerald-700'],
    'amber'  => ['bg' => 'bg-amber-50',  'border' => 'border-amber-100', 'icon' => 'text-amber-600',  'val' => 'text-amber-700'],
    'red'    => ['bg' => 'bg-red-50',    'border' => 'border-red-100',   'icon' => 'text-red-600',    'val' => 'text-red-700'],
    'purple' => ['bg' => 'bg-purple-50', 'border' => 'border-purple-100','icon' => 'text-purple-600', 'val' => 'text-purple-700'],
    'slate'  => ['bg' => 'bg-slate-50',  'border' => 'border-slate-200', 'icon' => 'text-slate-500',  'val' => 'text-slate-700'],
];
$c = $colorMap[$color] ?? $colorMap['sky'];
$tag = $href ? 'a' : 'div';
@endphp

<{{ $tag }} {{ $href ? "href={$href}" : '' }}
    class="bg-white border border-slate-200 rounded-xl p-5 flex items-start gap-4
           {{ $href ? 'hover:border-sky-200 hover:shadow-sm transition-all cursor-pointer' : '' }}">
    <div class="flex-shrink-0 w-11 h-11 rounded-xl {{ $c['bg'] }} border {{ $c['border'] }}
                flex items-center justify-center">
        <i class="ti {{ $icon }} text-xl {{ $c['icon'] }}"></i>
    </div>
    <div class="min-w-0 flex-1">
        <p class="text-xs font-medium text-slate-500 uppercase tracking-wide">{{ $label }}</p>
        <p class="text-2xl font-semibold text-slate-800 mt-1">{{ $value }}</p>
        @if($sub)
        <p class="text-xs text-slate-400 mt-1">{{ $sub }}</p>
        @endif
    </div>
    @if($trend !== null)
    <div class="flex-shrink-0 text-right">
        <span class="text-xs {{ $trend >= 0 ? 'text-emerald-600' : 'text-red-600' }}">
            <i class="ti {{ $trend >= 0 ? 'ti-trending-up' : 'ti-trending-down' }}"></i>
            {{ abs($trend) }}%
        </span>
    </div>
    @endif
</{{ $tag }}>
