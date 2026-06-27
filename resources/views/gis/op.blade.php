@extends('layouts.app')
@section('title', 'Peta Sebaran OP')

@section('breadcrumb')
    <a href="{{ route('gis.index') }}" class="text-slate-500 hover:text-slate-700 text-sm">GIS</a>
    <i class="ti ti-chevron-right text-slate-300 text-xs"></i>
    <span class="text-slate-800 font-medium text-sm">Peta Sebaran OP</span>
@endsection

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
<style>
#map { height: calc(100vh - 200px); min-height: 500px; }
.leaflet-popup-content-wrapper { border-radius: 12px; }
</style>
@endpush

@section('content')
<div class="flex flex-col lg:flex-row gap-4">

    {{-- Filter --}}
    <div class="lg:w-72 flex-shrink-0 space-y-4">
        <div class="bg-white border border-slate-200 rounded-xl p-4">
            <h3 class="text-sm font-semibold text-slate-800 mb-3 flex items-center gap-2">
                <i class="ti ti-filter text-slate-400"></i> Filter Tahun OP
            </h3>
            <div class="space-y-3">
                <div>
                    <label class="text-xs font-medium text-slate-600 block mb-1">Tahun</label>
                    <select id="filter-tahun" class="w-full text-sm border border-slate-200 rounded-lg px-3 py-2
                                                      focus:outline-none focus:ring-2 focus:ring-sky-500">
                        @foreach(range(now()->year, 2015) as $t)
                        <option value="{{ $t }}" @selected($tahun == $t)>{{ $t }}</option>
                        @endforeach
                    </select>
                </div>
                <button onclick="applyFilter()"
                        class="w-full py-2 bg-sky-600 text-white text-sm rounded-lg hover:bg-sky-700">
                    Tampilkan
                </button>
            </div>
        </div>

        {{-- Legenda --}}
        <div class="bg-white border border-slate-200 rounded-xl p-4">
            <h3 class="text-sm font-semibold text-slate-800 mb-3">Legenda Realisasi OP</h3>
            <div class="space-y-2">
                @foreach([
                    ['#22c55e', '≥ 90% — Sangat Baik'],
                    ['#eab308', '70–89% — Baik'],
                    ['#f97316', '50–69% — Cukup'],
                    ['#ef4444', '< 50% — Kurang'],
                    ['#94a3b8', 'Belum Ada Data'],
                ] as [$color, $label])
                <div class="flex items-center gap-2.5">
                    <div class="w-4 h-4 rounded-full flex-shrink-0" style="background:{{ $color }}"></div>
                    <span class="text-xs text-slate-600">{{ $label }}</span>
                </div>
                @endforeach
            </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-xl p-4" id="map-stats">
            <h3 class="text-sm font-semibold text-slate-800 mb-2">Statistik</h3>
            <p class="text-xs text-slate-400">Memuat data...</p>
        </div>
    </div>

    {{-- Peta --}}
    <div class="flex-1 bg-white border border-slate-200 rounded-xl overflow-hidden">
        <div id="map" class="w-full"></div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
const map = L.map('map').setView([-9.5, 124.0], 9);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap', maxZoom: 18,
}).addTo(map);

let markersLayer = L.layerGroup().addTo(map);

function getColor(pct) {
    if (!pct) return '#94a3b8';
    if (pct >= 90) return '#22c55e';
    if (pct >= 70) return '#eab308';
    if (pct >= 50) return '#f97316';
    return '#ef4444';
}

function loadData(tahun) {
    markersLayer.clearLayers();
    const url = '{{ route("gis.geojson.op") }}' + '?tahun=' + tahun;
    fetch(url)
        .then(r => r.json())
        .then(data => {
            if (!data.features?.length) return;
            const bounds = [];
            data.features.forEach(f => {
                if (f.geometry?.type !== 'Point') return;
                const [lng, lat] = f.geometry.coordinates;
                const props = f.properties;
                const marker = L.circleMarker([lat, lng], {
                    radius: 8,
                    fillColor: getColor(props.avg_realisasi),
                    color: '#fff', weight: 2, fillOpacity: 0.9,
                });
                marker.bindPopup(`
                    <div style="min-width:220px">
                        <b style="font-size:14px;color:#1e293b">${props.nama}</b>
                        <p style="font-size:12px;color:#64748b;margin:4px 0">${props.jenis_aset || '-'} · ${props.satker || '-'}</p>
                        <p style="font-size:12px;color:#64748b">${props.kabupaten || '-'}</p>
                        <div style="margin-top:8px;display:flex;align-items:center;gap:8px">
                            <div style="width:10px;height:10px;border-radius:50%;background:${getColor(props.avg_realisasi)}"></div>
                            <span style="font-size:13px;font-weight:600">Rata-rata OP: ${props.avg_realisasi ? props.avg_realisasi + '%' : 'Belum ada data'}</span>
                        </div>
                        <div style="margin-top:8px">
                            <a href="${props.url}" style="display:inline-block;padding:5px 12px;background:#0284c7;color:#fff;border-radius:6px;font-size:12px;text-decoration:none">Detail →</a>
                        </div>
                    </div>
                `);
                markersLayer.addLayer(marker);
                bounds.push([lat, lng]);
            });
            if (bounds.length > 0) map.fitBounds(L.latLngBounds(bounds).pad(0.1));
            document.getElementById('map-stats').innerHTML = `
                <h3 style="font-size:14px;font-weight:600;margin-bottom:8px">Statistik OP ${tahun}</h3>
                <p style="font-size:24px;font-weight:700">${data.features.length}</p>
                <p style="font-size:12px;color:#64748b">Aset dengan data OP</p>`;
        })
        .catch(e => console.error(e));
}

function applyFilter() {
    loadData(document.getElementById('filter-tahun').value);
}

loadData({{ $tahun }});
</script>
@endpush
