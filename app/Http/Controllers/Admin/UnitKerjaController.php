<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UnitKerja;
use Illuminate\Http\Request;

class UnitKerjaController extends Controller
{
    public function index(Request $request)
    {
        if (!$request->hasAny(['tipe', 'search'])) {
            $tree = UnitKerja::with(['allChildren'])->whereNull('parent_id')
                ->orderBy('urutan')->get();
            return view('admin.unit-kerja.index', ['tree' => $tree, 'viewMode' => 'tree']);
        }
        $rows = UnitKerja::with('parent')->withCount(['users','children'])
            ->when($request->tipe,   fn($q) => $q->where('tipe', $request->tipe))
            ->when($request->search, fn($q) => $q->where(function($q) use ($request) {
                $q->where('nama','ilike',"%{$request->search}%")
                  ->orWhere('kode','ilike',"%{$request->search}%");
            }))
            ->orderBy('tipe')->orderBy('urutan')->orderBy('nama')
            ->paginate(25)->withQueryString();
        return view('admin.unit-kerja.index', ['rows' => $rows, 'viewMode' => 'list']);
    }

    public function create(Request $request)
    {
        $tipeAnak = $request->tipe;
        $parents  = $this->getParents($tipeAnak);
        return view('admin.unit-kerja.form', compact('parents', 'tipeAnak'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateForm($request);

        if (!empty($validated['parent_id'])) {
            $parent = UnitKerja::findOrFail($validated['parent_id']);
            $boleh  = UnitKerja::parentYangBoleh($validated['tipe']);
            if (!in_array($parent->tipe, $boleh)) {
                return back()->withInput()->withErrors([
                    'parent_id' => "Tipe '{$parent->tipe}' tidak bisa menjadi induk dari '{$validated['tipe']}'."
                ]);
            }
        }

        $uk = UnitKerja::create($validated + ['is_aktif' => true]);
        return redirect()->route('superadmin.unit-kerja.index')
            ->with('success', "{$uk->labelTipe()} <strong>{$uk->nama}</strong> berhasil ditambahkan.");
    }

    public function show(UnitKerja $unitKerja)
    {
        $unitKerja->load(['parent','children.children','users.roles']);
        return view('admin.unit-kerja.show', compact('unitKerja'));
    }

    public function edit(UnitKerja $unitKerja)
    {
        $parents = $this->getParents($unitKerja->tipe, $unitKerja->id);
        return view('admin.unit-kerja.form', compact('unitKerja', 'parents'));
    }

    public function update(Request $request, UnitKerja $unitKerja)
    {
        $validated = $this->validateForm($request, $unitKerja->id);

        if (!empty($validated['parent_id'])) {
            $anakIds = $unitKerja->semuaIdAnak();
            if (in_array($validated['parent_id'], $anakIds) || $validated['parent_id'] === $unitKerja->id) {
                return back()->withInput()->withErrors(['parent_id' => 'Tidak boleh menjadi anak dari turunannya sendiri.']);
            }
        }

        $unitKerja->update($validated);
        return redirect()->route('superadmin.unit-kerja.index')
            ->with('success', "{$unitKerja->labelTipe()} <strong>{$unitKerja->nama}</strong> berhasil diperbarui.");
    }

    public function destroy(UnitKerja $unitKerja)
    {
        if ($unitKerja->children()->count()) return back()->with('error', 'Masih ada sub-unit di bawahnya.');
        if ($unitKerja->users()->count())    return back()->with('error', 'Masih ada pengguna di unit ini.');
        if ($unitKerja->assets()->count())   return back()->with('error', 'Masih ada aset di unit ini.');
        $nama = $unitKerja->namaLengkap();
        $unitKerja->delete();
        return redirect()->route('superadmin.unit-kerja.index')
            ->with('success', "<strong>{$nama}</strong> berhasil dihapus.");
    }

    public function createPpk(UnitKerja $unitKerja)
    {
        abort_unless($unitKerja->tipe === 'satker', 422, 'PPK hanya bisa di bawah Satker.');
        $parents = collect([$unitKerja]);
        return view('admin.unit-kerja.form', ['unitKerja' => null, 'parents' => $parents, 'tipeAnak' => 'ppk', 'parentFixed' => $unitKerja]);
    }

    public function storePpk(Request $request, UnitKerja $unitKerja)
    {
        abort_unless($unitKerja->tipe === 'satker', 422);
        $request->merge(['parent_id' => $unitKerja->id, 'tipe' => 'ppk']);
        return $this->store($request);
    }

    private function validateForm(Request $request, ?string $exceptId = null): array
    {
        return $request->validate([
            'parent_id'   => 'nullable|exists:unit_kerja,id',
            'tipe'        => 'required|in:balai,bagian,bidang,satker,ppk',
            'nama'        => 'required|string|max:255',
            'singkatan'   => 'nullable|string|max:50',
            'kode'        => "required|string|max:30|unique:unit_kerja,kode,{$exceptId}",
            'kepala_nama' => 'nullable|string|max:255',
            'kepala_nip'  => 'nullable|string|max:18',
            'telp'        => 'nullable|string|max:20',
            'email'       => 'nullable|email|max:255',
            'alamat'      => 'nullable|string',
            'tupoksi'     => 'nullable|string',
            'is_aktif'    => 'boolean',
            'urutan'      => 'nullable|integer|min:0',
        ]);
    }

    private function getParents(?string $tipeAnak, ?string $exceptId = null)
    {
        $boleh = $tipeAnak ? UnitKerja::parentYangBoleh($tipeAnak) : ['balai','bagian','bidang','satker'];
        return UnitKerja::whereIn('tipe', $boleh)
            ->when($exceptId, fn($q) => $q->where('id', '!=', $exceptId))
            ->orderBy('tipe')->orderBy('nama')->get();
    }
}
