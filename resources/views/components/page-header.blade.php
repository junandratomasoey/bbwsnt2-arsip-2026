{{-- resources/views/components/page-header.blade.php --}}
@props(['title', 'desc' => null, 'icon' => null])
<div class="mb-6 flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
    <div class="flex items-start gap-3">
        @if($icon)
        <div class="mt-0.5 w-10 h-10 rounded-xl bg-sky-100 flex items-center justify-center flex-shrink-0">
            <i class="ti {{ $icon }} text-sky-600 text-xl"></i>
        </div>
        @endif
        <div>
            <h1 class="text-xl font-semibold text-slate-800">{{ $title }}</h1>
            @if($desc)
            <p class="mt-0.5 text-sm text-slate-500">{{ $desc }}</p>
            @endif
        </div>
    </div>
    @if($slot->isNotEmpty())
    <div class="flex items-center gap-2 flex-shrink-0">{{ $slot }}</div>
    @endif
</div>
