{{-- components/nav-item.blade.php --}}
{{-- Sidebar nav item dengan aksen kuning KemenPU saat aktif --}}
@props(['route', 'icon', 'label'])
@php
    try {
        $active = request()->routeIs(rtrim($route, '.index') . '*')
               || request()->routeIs($route);
    } catch (\Exception $e) {
        $active = false;
    }
@endphp
<a href="{{ route($route) }}"
   :title="sidebarCollapsed ? '{{ $label }}' : ''"
   @class([
       'flex items-center gap-3 px-2.5 py-2 rounded-lg text-sm transition-all duration-150 group relative',
       'text-white font-medium sidebar-accent' => $active,
       'text-white/50 hover:text-white hover:bg-white/8' => !$active,
   ])>

    {{-- Indikator aktif --}}
    @if($active)
    <span class="absolute left-0 top-1/2 -translate-y-1/2 w-0.5 h-5 rounded-r-full"
          style="background:#F4A81D"></span>
    @endif

    <i class="ti {{ $icon }} text-base flex-shrink-0 {{ $active ? 'text-[#F4A81D]' : 'group-hover:text-white/80' }}"></i>
    <span x-show="!sidebarCollapsed"
          x-transition:enter="transition-opacity duration-150"
          x-transition:enter-start="opacity-0"
          x-transition:enter-end="opacity-100"
          class="truncate text-[13px]">{{ $label }}</span>
</a>
