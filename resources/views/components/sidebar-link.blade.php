{{-- resources/views/components/sidebar-link.blade.php --}}
@props(['route', 'icon', 'label' => ''])
@php $active = request()->routeIs($route . '*'); @endphp
<a href="{{ route($route) }}"
   title="{{ $label ?: $route }}"
   class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition-colors
          {{ $active
             ? 'bg-sky-500/20 text-sky-300 font-medium'
             : 'text-slate-400 hover:bg-white/8 hover:text-slate-200' }}">
    <i class="ti {{ $icon }} text-base flex-shrink-0 {{ $active ? 'text-sky-400' : '' }}"></i>
    @if($label)
    <span class="truncate">{{ $label }}</span>
    @endif
</a>
