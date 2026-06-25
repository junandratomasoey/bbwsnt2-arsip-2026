{{-- nav-item.blade.php --}}
{{-- collapsed dibaca dari Alpine parent via x-bind, tidak perlu PHP prop --}}
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
   class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition-all group
          {{ $active
              ? 'bg-sky-500/15 text-sky-300 font-medium'
              : 'text-slate-400 hover:bg-white/5 hover:text-slate-200' }}">
    <i class="ti {{ $icon }} text-base flex-shrink-0
              {{ $active ? 'text-sky-400' : 'group-hover:text-slate-200' }}"></i>
    <span x-show="!sidebarCollapsed"
          x-transition:enter="transition-opacity duration-150"
          x-transition:enter-start="opacity-0"
          x-transition:enter-end="opacity-100"
          class="truncate">{{ $label }}</span>
    @if($active)
    <span x-show="!sidebarCollapsed"
          class="ml-auto w-1.5 h-1.5 rounded-full bg-sky-400 flex-shrink-0"></span>
    @endif
</a>
