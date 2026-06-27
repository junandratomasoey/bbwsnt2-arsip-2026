{{-- components/nav-section.blade.php --}}
@props(['label'])
<div x-show="!sidebarCollapsed"
     x-transition:enter="transition-opacity duration-150"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     class="pt-4 pb-1 px-2.5">
    <p class="text-[9px] font-bold uppercase tracking-[0.2em]" style="color: rgba(244,168,29,0.5)">
        {{ $label }}
    </p>
</div>
<div x-show="sidebarCollapsed" class="mx-2.5 my-3">
    <div class="h-px" style="background: rgba(244,168,29,0.2)"></div>
</div>
