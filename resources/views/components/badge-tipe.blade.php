{{-- resources/views/components/badge-tipe.blade.php --}}
@props(['tipe'])
@php
$map = [
    'balai'  => ['label' => 'Balai',   'class' => 'bg-purple-100 text-purple-700'],
    'bagian' => ['label' => 'Bagian',  'class' => 'bg-blue-100 text-blue-700'],
    'bidang' => ['label' => 'Bidang',  'class' => 'bg-teal-100 text-teal-700'],
    'satker' => ['label' => 'Satker',  'class' => 'bg-amber-100 text-amber-700'],
    'ppk'    => ['label' => 'PPK',     'class' => 'bg-red-100 text-red-700'],
];
$info = $map[$tipe] ?? ['label' => $tipe, 'class' => 'bg-slate-100 text-slate-600'];
@endphp
<span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium {{ $info['class'] }}">
    {{ $info['label'] }}
</span>
