<?php
namespace App\Http\Controllers\Document;
use App\Http\Controllers\Controller;
use App\Models\DocumentType;
use Illuminate\Http\Request;

class DocumentTypeController extends Controller
{
    public function index()
    {
        $types = DocumentType::withCount('documents')->orderBy('kategori')->orderBy('urutan')->get();
        return view('superadmin.document-types.index', compact('types'));
    }

    public function create()
    {
        return view('superadmin.document-types.form');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode'                  => 'required|string|max:20|unique:document_types,kode',
            'nama'                  => 'required|string|max:255',
            'kategori'              => 'required|string|max:100',
            'retensi_aktif_tahun'   => 'required|integer|min:1',
            'retensi_inaktif_tahun' => 'required|integer|min:1',
            'nasib_akhir'           => 'required|in:musnah,permanen,sampling',
        ]);
        DocumentType::create($validated + ['is_aktif' => true]);
        return redirect()->route('superadmin.document-types.index')
            ->with('success', "Jenis dokumen <strong>{$validated['nama']}</strong> berhasil ditambahkan.");
    }

    public function edit(DocumentType $documentType)
    {
        return view('superadmin.document-types.form', compact('documentType'));
    }

    public function update(Request $request, DocumentType $documentType)
    {
        $validated = $request->validate([
            'kode'     => "required|string|max:20|unique:document_types,kode,{$documentType->id}",
            'nama'     => 'required|string|max:255',
            'kategori' => 'required|string|max:100',
            'retensi_aktif_tahun'   => 'required|integer|min:1',
            'retensi_inaktif_tahun' => 'required|integer|min:1',
            'nasib_akhir' => 'required|in:musnah,permanen,sampling',
            'is_aktif'    => 'boolean',
        ]);
        $documentType->update($validated);
        return redirect()->route('superadmin.document-types.index')
            ->with('success', 'Jenis dokumen berhasil diperbarui.');
    }

    public function destroy(DocumentType $documentType)
    {
        if ($documentType->documents()->count() > 0) {
            return back()->with('error', 'Jenis dokumen masih digunakan.');
        }
        $documentType->delete();
        return redirect()->route('superadmin.document-types.index')->with('success', 'Jenis dokumen dihapus.');
    }
}
