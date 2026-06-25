{{-- nav-section.blade.php --}}
@props(['label'])
<div x-show="!sidebarCollapsed"
     x-transition:enter="transition-opacity duration-150"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100">
    <p class="px-3 pt-5 pb-1.5 text-[10px] font-semibold uppercase tracking-widest text-slate-600">
        {{ $label }}
    </p>
</div>
<div x-show="sidebarCollapsed" class="mx-3 my-3 h-px bg-white/10"></div>
