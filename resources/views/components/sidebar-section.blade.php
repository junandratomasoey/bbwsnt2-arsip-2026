{{-- resources/views/components/sidebar-section.blade.php --}}
@props(['label', 'collapsed' => false])
@if(!$collapsed)
<p class="px-3 pt-4 pb-1 text-[10px] font-semibold uppercase tracking-widest text-slate-500">{{ $label }}</p>
@else
<div class="mx-3 my-3 h-px bg-white/10"></div>
@endif
