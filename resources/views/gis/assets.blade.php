@extends('layouts.app')
@section('title', 'Peta Sebaran Aset')

@section('breadcrumb')
    <span class="text-slate-500 text-sm">GIS</span>
    <i class="ti ti-chevron-right text-slate-300 text-xs"></i>
    <span class="text-slate-800 font-medium text-sm">Peta Sebaran Aset</span>
@endsection

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
<style>
#map { height: calc(100vh - 200px); min-height: 500px; }
.leaflet-popup-content-wrapper { border-radius: 12px; box-shadow: 0 4px 24px rgba(0,0,0,0.15); }
.leaflet-popup-content { margin: 14px 16px; }
.map-popup-title { font-weight: 600; font-size: 14px; color: #1e293b; margin-bottom: 6px; }
.map-popup-row { font-size: 12px; color: #64748b; display: flex; gap: 6px; margin-bottom: 3px; }
.map-popup-badge { display: inline-block; padding: 2px 8px; border-radius: 6px; font-size: 11px; font-weight: 500; }
.kondisi-A { background: #dcfce7; color: #166534; }
.kondisi-B { background: #fef9c3; color: #713f12; }
.kondisi-C { background: #ffedd5; color: #7c2d12; }
.kondisi-D { background: #fee2e2; color: #7f1d1d; }
.kondisi-default { background: #f1f5f9; color: #475569; }
</style>
@endpush

@section('content')
<div class="flex flex-col lg:flex-row gap-4">

    {{-- Filter sidebar --}}
    <div class="lg:w-72 space-y-4 flex-shrink-0">
        <div class="bg-white border border-slate-200 rounded-xl p-4">
            <h3 class="text-sm font-semibold text-slate-800 mb-3 flex items-center gap-2">
                <i class="ti ti-filter text-slate-400"></i> Filter
            </h3>
            <div class="space-y-3">
                <div>
                    <label class="text-xs font-medium text-slate-600 block mb-1">Jenis Aset</label>
                    <select id="filter-type" class="w-full text-sm border border-slate-200 rounded-lg px-3 py-2
                                                    focus:outline-none focus:ring-2 focus:ring-sky-500">
                        <option value="">Semua jenis</option>
                        @foreach($assetTypes as $t)
                        <option value="{{ $t->id }}">{{ $t->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-xs font-medium text-slate-600 block mb-1">Kondisi</label>
                    <select id="filter-kondisi" class="w-full text-sm border border-slate-200 rounded-lg px-3 py-2
                                                       focus:outline-none focus:ring-2 focus:ring-sky-500">
                        <option value="">Semua kondisi</option>
                        <option value="A">A — Baik</option>
                        <option value="B">B — Sedang</option>
                        <option value="C">C — Rusak Ringan</option>
                        <option value="D">D — Rusak Berat</option>
                    </select>
                </div>
                <div>
                    <label class="text-xs font-medium text-slate-600 block mb-1">Satker</label>
                    <select id="filter-satker" class="w-full text-sm border border-slate-200 rounded-lg px-3 py-2
                                                      focus:outline-none focus:ring-2 focus:ring-sky-500">
                        <option value="">Semua satker</option>
                        @foreach($unitKerjas as $uk)
                        <option value="{{ $uk->id }}">{{ $uk->singkatan }}</option>
                        @endforeach
                    </select>
                </div>
                <button onclick="applyFilter()"
                        class="w-full py-2 bg-sky-600 text-white text-sm rounded-lg hover:bg-sky-700">
                    Terapkan Filter
                </button>
                <button onclick="resetFilter()"
                        class="w-full py-2 bg-slate-100 text-slate-600 text-sm rounded-lg hover:bg-slate-200">
                    Reset
                </button>
            </div>
        </div>

        {{-- Legenda --}}
        <div class="bg-white border border-slate-200 rounded-xl p-4">
            <h3 class="text-sm font-semibold text-slate-800 mb-3">Legenda Kondisi</h3>
            <div class="space-y-2">
                @foreach(['A' => ['Baik (>80%)', '#22c55e'], 'B' => ['Sedang (60-80%)', '#eab308'], 'C' => ['Rusak Ringan (40-60%)', '#f97316'], 'D' => ['Rusak Berat (<40%)', '#ef4444'], '-' => ['Belum Dinilai', '#94a3b8']] as $k => [$label, $color])
                <div class="flex items-center gap-2.5">
                    <div class="w-4 h-4 rounded-full flex-shrink-0" style="background: {{ $color }}"></div>
                    <span class="text-xs text-slate-600">
                        @if($k !== '-')<strong>{{ $k }}</strong> — @endif{{ $label }}
                    </span>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Stats --}}
        <div class="bg-white border border-slate-200 rounded-xl p-4" id="map-stats">
            <h3 class="text-sm font-semibold text-slate-800 mb-3">Statistik Peta</h3>
            <p class="text-xs text-slate-500">Memuat data...</p>
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
// ── Inisialisasi peta ─────────────────────────────────────────────
const map = L.map('map').setView(
    [{{ $gisConfig['center']['lat'] }}, {{ $gisConfig['center']['lng'] }}],
    {{ $gisConfig['zoom'] }}
);

// Base layer OSM
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
    maxZoom: 18,
}).addTo(map);

// Layer group untuk markers
let markersLayer = L.layerGroup().addTo(map);
let geojsonUrl   = '{{ $gisConfig['geojsonUrl'] }}';

// ── Fungsi marker custom ──────────────────────────────────────────
function createMarker(feature, latlng) {
    const props = feature.properties;
    return L.circleMarker(latlng, {
        radius:      8,
        fillColor:   props.marker_color || '#94a3b8',
        color:       '#fff',
        weight:      2,
        opacity:     1,
        fillOpacity: 0.9,
    });
}

// ── Popup content ─────────────────────────────────────────────────
function createPopup(props) {
    const kondisiClass = props.kondisi && props.kondisi !== '-'
        ? `kondisi-${props.kondisi}` : 'kondisi-default';

    return `
        <div style="min-width:240px">
            <div class="map-popup-title">${props.nama}</div>
            <div class="map-popup-row">
                <i style="color:#94a3b8;font-size:13px" class="ti ti-id"></i>
                <span>${props.asset_code}</span>
            </div>
            <div class="map-popup-row">
                <i style="color:#94a3b8;font-size:13px" class="ti ti-building-bridge"></i>
                <span>${props.jenis_aset || '-'}</span>
            </div>
            <div class="map-popup-row">
                <i style="color:#94a3b8;font-size:13px" class="ti ti-map-pin"></i>
                <span>${props.kabupaten || '-'}</span>
            </div>
            <div class="map-popup-row">
                <i style="color:#94a3b8;font-size:13px" class="ti ti-building"></i>
                <span>${props.satker || '-'}</span>
            </div>
            <div class="mt-2 flex items-center gap-2">
                <span class="map-popup-badge ${kondisiClass}">
                    Kondisi ${props.kondisi || 'Belum Dinilai'}
                </span>
                ${props.rci_score ? `<span class="text-xs text-slate-400">RCI: ${props.rci_score}</span>` : ''}
            </div>
            <div class="mt-3">
                <a href="${props.url}" style="display:inline-block;padding:5px 12px;background:#0284c7;
                   color:#fff;border-radius:6px;font-size:12px;text-decoration:none">
                    Lihat Detail →
                </a>
            </div>
        </div>
    `;
}

// ── Load GeoJSON ──────────────────────────────────────────────────
function loadGeoJson(url) {
    markersLayer.clearLayers();
    document.getElementById('map-stats').innerHTML =
        '<h3 style="font-size:14px;font-weight:600;color:#1e293b;margin-bottom:12px">Statistik Peta</h3>' +
        '<p style="font-size:12px;color:#94a3b8">Memuat data...</p>';

    fetch(url)
        .then(r => r.json())
        .then(data => {
            if (!data.features || data.features.length === 0) {
                document.getElementById('map-stats').innerHTML =
                    '<h3 style="font-size:14px;font-weight:600;color:#1e293b;margin-bottom:8px">Statistik Peta</h3>' +
                    '<p style="font-size:12px;color:#94a3b8">Tidak ada aset dengan geometri.</p>';
                return;
            }

            // Hitung stats
            const stats = { A: 0, B: 0, C: 0, D: 0, '-': 0 };
            const bounds = [];

            data.features.forEach(f => {
                const props = f.properties;
                const kondisi = props.kondisi || '-';
                stats[kondisi] = (stats[kondisi] || 0) + 1;

                if (f.geometry?.type === 'Point') {
                    const [lng, lat] = f.geometry.coordinates;
                    const marker = createMarker(f, [lat, lng]);
                    marker.bindPopup(createPopup(props), { maxWidth: 300 });
                    markersLayer.addLayer(marker);
                    bounds.push([lat, lng]);
                }
            });

            // Fit bounds
            if (bounds.length > 0) {
                map.fitBounds(L.latLngBounds(bounds).pad(0.1));
            }

            // Update stats panel
            const total = data.features.length;
            const kondisiColors = { A: '#22c55e', B: '#eab308', C: '#f97316', D: '#ef4444', '-': '#94a3b8' };
            let statsHtml = `
                <h3 style="font-size:14px;font-weight:600;color:#1e293b;margin-bottom:12px">
                    Statistik Peta
                </h3>
                <p style="font-size:24px;font-weight:700;color:#0f172a;margin-bottom:8px">${total}</p>
                <p style="font-size:12px;color:#64748b;margin-bottom:12px">Total aset ditampilkan</p>
            `;
            Object.entries(stats).filter(([,v]) => v > 0).forEach(([k, v]) => {
                statsHtml += `
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px">
                        <div style="display:flex;align-items:center;gap:8px">
                            <div style="width:10px;height:10px;border-radius:50%;background:${kondisiColors[k]}"></div>
                            <span style="font-size:12px;color:#475569">
                                ${k === '-' ? 'Belum dinilai' : 'Kondisi ' + k}
                            </span>
                        </div>
                        <span style="font-size:13px;font-weight:600;color:#1e293b">${v}</span>
                    </div>
                `;
            });
            document.getElementById('map-stats').innerHTML = statsHtml;
        })
        .catch(err => {
            console.error('GeoJSON load error:', err);
            document.getElementById('map-stats').innerHTML =
                '<p style="font-size:12px;color:#ef4444">Gagal memuat data peta.</p>';
        });
}

// ── Filter ────────────────────────────────────────────────────────
function applyFilter() {
    const params = new URLSearchParams();
    const type    = document.getElementById('filter-type').value;
    const kondisi = document.getElementById('filter-kondisi').value;
    const satker  = document.getElementById('filter-satker').value;
    if (type)    params.set('asset_type_id', type);
    if (kondisi) params.set('kondisi', kondisi);
    if (satker)  params.set('unit_kerja_id', satker);

    const base = '{{ route("gis.geojson.assets") }}';
    loadGeoJson(base + (params.toString() ? '?' + params.toString() : ''));
}

function resetFilter() {
    document.getElementById('filter-type').value    = '';
    document.getElementById('filter-kondisi').value = '';
    document.getElementById('filter-satker').value  = '';
    loadGeoJson('{{ route("gis.geojson.assets") }}');
}

// Load awal
loadGeoJson(geojsonUrl);
</script>
@endpush
