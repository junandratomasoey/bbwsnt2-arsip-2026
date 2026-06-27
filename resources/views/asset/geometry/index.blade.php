@extends('layouts.app')
@section('title', 'Geometri GIS — ' . $asset->nama)

@section('breadcrumb')
    <a href="{{ route('assets.index') }}" class="text-slate-500 hover:text-slate-700 text-sm">Aset</a>
    <i class="ti ti-chevron-right text-slate-300 text-xs"></i>
    <a href="{{ route('assets.show', $asset) }}" class="text-slate-500 hover:text-slate-700 text-sm truncate max-w-32">
        {{ $asset->nama }}
    </a>
    <i class="ti ti-chevron-right text-slate-300 text-xs"></i>
    <span class="text-slate-800 font-medium text-sm">Geometri GIS</span>
@endsection

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
<style>#map-preview { height: 340px; }</style>
@endpush

@section('content')
<x-page-header :title="'Geometri — ' . $asset->nama" icon="ti-map-pin">
    <a href="{{ route('assets.show', $asset) }}"
       class="inline-flex items-center gap-1.5 px-4 py-2 bg-white border border-slate-200
              text-sm text-slate-700 rounded-xl hover:bg-slate-50">
        <i class="ti ti-arrow-left text-slate-400"></i> Kembali ke Aset
    </a>
</x-page-header>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

    {{-- Form tambah --}}
    @can('asset_geometry.create')
    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100">
            <h3 class="text-sm font-semibold text-slate-700">Tambah Geometri</h3>
        </div>
        <form method="POST" action="{{ route('assets.geometry.store', $asset) }}" class="p-5 space-y-4">
            @csrf
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Tipe <span class="text-red-500">*</span></label>
                    <select name="geom_type" id="geom-type" required
                            class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500">
                        <option value="point">Titik (Point)</option>
                        <option value="line">Garis (Line)</option>
                        <option value="polygon">Area (Polygon)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Label</label>
                    <input type="text" name="label" value="{{ old('label') }}"
                           placeholder="Pintu air, bendung..."
                           class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500">
                </div>
            </div>

            {{-- Koordinat point --}}
            <div id="input-point">
                <p class="text-xs font-medium text-slate-600 mb-2">Koordinat</p>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="text-xs text-slate-500 mb-1 block">Latitude</label>
                        <input type="number" name="lat" id="input-lat" step="any"
                               value="{{ old('lat') }}" placeholder="-9.123456"
                               class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-sky-500">
                    </div>
                    <div>
                        <label class="text-xs text-slate-500 mb-1 block">Longitude</label>
                        <input type="number" name="lng" id="input-lng" step="any"
                               value="{{ old('lng') }}" placeholder="124.123456"
                               class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-sky-500">
                    </div>
                </div>
                <p class="text-xs text-sky-600 mt-1.5 flex items-center gap-1">
                    <i class="ti ti-cursor-text"></i> Klik peta kanan untuk isi koordinat otomatis
                </p>
            </div>

            {{-- GeoJSON input --}}
            <div id="input-geojson" class="hidden">
                <label class="text-xs font-medium text-slate-600 mb-1 block">GeoJSON Geometry</label>
                <textarea name="geojson" rows="5" placeholder='{"type":"LineString","coordinates":[[124.1,-9.1],[124.2,-9.2]]}'
                          class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm font-mono resize-y focus:outline-none focus:ring-2 focus:ring-sky-500">{{ old('geojson') }}</textarea>
            </div>

            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Keterangan</label>
                <input type="text" name="keterangan" value="{{ old('keterangan') }}"
                       class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500">
            </div>

            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="is_primary" value="1" class="rounded border-slate-300 text-sky-600">
                <span class="text-sm text-slate-700">Jadikan geometri utama aset ini</span>
            </label>

            <button type="submit"
                    class="w-full py-2.5 bg-sky-600 text-white text-sm font-medium rounded-xl hover:bg-sky-700">
                <i class="ti ti-device-floppy mr-1.5"></i> Simpan Geometri
            </button>
        </form>
    </div>
    @endcan

    {{-- Peta --}}
    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
            <h3 class="text-sm font-semibold text-slate-700">Peta Lokasi</h3>
            <span class="text-xs text-slate-400"><i class="ti ti-click"></i> Klik peta untuk pilih titik</span>
        </div>
        <div id="map-preview"></div>
    </div>
</div>

{{-- Daftar geometri --}}
<div class="mt-6 bg-white border border-slate-200 rounded-xl overflow-hidden">
    <div class="px-5 py-4 border-b border-slate-100">
        <h3 class="text-sm font-semibold text-slate-700">Geometri Tersimpan ({{ $geometries->count() }})</h3>
    </div>
    @if($geometries->isEmpty())
    <div class="py-10 text-center">
        <i class="ti ti-map-pin-off text-3xl text-slate-200 block mb-2"></i>
        <p class="text-sm text-slate-400">Belum ada geometri. Tambahkan lewat form di atas.</p>
    </div>
    @else
    <table class="w-full text-sm">
        <thead class="bg-slate-50 border-b border-slate-200 text-xs font-medium text-slate-500 uppercase">
            <tr>
                <th class="px-5 py-3 text-left">Tipe</th>
                <th class="px-5 py-3 text-left">Label</th>
                <th class="px-5 py-3 text-left hidden md:table-cell">Keterangan</th>
                <th class="px-5 py-3 text-center">Utama</th>
                <th class="px-5 py-3 text-center">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @foreach($geometries as $geom)
            <tr class="hover:bg-slate-50">
                <td class="px-5 py-3">
                    @php $ic = match($geom->geom_type){ 'line'=>'ti-route','polygon'=>'ti-polygon',default=>'ti-map-pin' }; @endphp
                    <span class="inline-flex items-center gap-1 text-xs px-2 py-0.5 rounded bg-sky-50 text-sky-700">
                        <i class="ti {{ $ic }}"></i> {{ ucfirst($geom->geom_type) }}
                    </span>
                </td>
                <td class="px-5 py-3 text-slate-700">{{ $geom->label ?: '—' }}</td>
                <td class="px-5 py-3 hidden md:table-cell text-xs text-slate-500">{{ $geom->keterangan ?: '—' }}</td>
                <td class="px-5 py-3 text-center">
                    @if($geom->is_primary)
                    <span class="text-xs text-amber-500 font-medium"><i class="ti ti-star-filled"></i> Utama</span>
                    @else
                    @can('asset_geometry.edit')
                    <form action="{{ route('assets.geometry.update', [$asset, $geom]) }}" method="POST">
                        @csrf @method('PUT')
                        <input type="hidden" name="is_primary" value="1">
                        <button class="text-xs text-slate-400 hover:text-amber-600 hover:underline">Set utama</button>
                    </form>
                    @endcan
                    @endif
                </td>
                <td class="px-5 py-3">
                    <div class="flex items-center justify-center gap-1">
                        @can('asset_geometry.delete')
                        <form action="{{ route('assets.geometry.destroy', [$asset, $geom]) }}" method="POST"
                              onsubmit="return confirm('Hapus geometri ini?')">
                            @csrf @method('DELETE')
                            <button class="p-1.5 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg">
                                <i class="ti ti-trash text-sm"></i>
                            </button>
                        </form>
                        @endcan
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
const map = L.map('map-preview').setView([-9.5, 124.0], 9);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap', maxZoom: 18,
}).addTo(map);

let clickMarker = null;

// Klik peta → isi input lat/lng
map.on('click', function(e) {
    const lat = e.latlng.lat.toFixed(6);
    const lng = e.latlng.lng.toFixed(6);
    document.getElementById('input-lat').value = lat;
    document.getElementById('input-lng').value = lng;
    if (clickMarker) map.removeLayer(clickMarker);
    clickMarker = L.marker([lat, lng]).addTo(map)
        .bindPopup(`Lat: ${lat}<br>Lng: ${lng}`).openPopup();
});

// Render geometri tersimpan
@foreach($geometries as $geom)
@if($geom->geojson)
(function(){
    try {
        const g = {!! json_encode(json_decode($geom->geojson)) !!};
        if (!g) return;
        if (g.type === 'Point') {
            const [lng, lat] = g.coordinates;
            L.circleMarker([lat, lng], {
                radius: 8,
                fillColor: '{{ $geom->is_primary ? "#F4A81D" : "#0ea5e9" }}',
                color: '#fff', weight: 2, fillOpacity: 0.9
            }).addTo(map).bindPopup('{{ addslashes($geom->label ?: $asset->nama) }}');
        } else {
            L.geoJSON(g, { style: { color: '#0ea5e9', weight: 2.5, fillOpacity: 0.15 } }).addTo(map);
        }
    } catch(e) { console.warn('Geometri error:', e); }
})();
@endif
@endforeach

// Toggle point vs geojson input
document.getElementById('geom-type').addEventListener('change', function() {
    const isPoint = this.value === 'point';
    document.getElementById('input-point').classList.toggle('hidden', !isPoint);
    document.getElementById('input-geojson').classList.toggle('hidden', isPoint);
});
</script>
@endpush
