<?php

namespace App\Http\Controllers\Document;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\DocumentFile;
use App\Models\DocumentType;
use App\Models\PhysicalLocation;
use App\Models\UnitKerja;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class DocumentController extends Controller
{
    public function index(Request $request)
    {
        $user  = auth()->user();
        $query = Document::with(['documentType', 'uploadedBy', 'unitKerja', 'primaryFile'])
            ->aksesibel($user)
            ->when($request->search,          fn($q) => $q->search($request->search))
            ->when($request->document_type_id,fn($q) => $q->where('document_type_id', $request->document_type_id))
            ->when($request->unit_kerja_id,   fn($q) => $q->where('unit_kerja_id', $request->unit_kerja_id))
            ->when($request->status,          fn($q) => $q->where('status', $request->status))
            ->when($request->klasifikasi,     fn($q) => $q->where('klasifikasi', $request->klasifikasi))
            ->when($request->fase,            fn($q) => $q->where('entity_fase', $request->fase))
            ->when($request->filter === 'kadaluwarsa', fn($q) => $q->kadaluwarsa())
            ->when($request->filter === 'approved',    fn($q) => $q->approved())
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $docTypes   = DocumentType::aktif()->orderBy('kategori')->orderBy('urutan')->get();
        $unitKerjas = UnitKerja::satker()->aktif()->orderBy('nama')->get();

        $stats = [
            'total'      => Document::aksesibel($user)->count(),
            'draft'      => Document::aksesibel($user)->where('status', 'draft')->count(),
            'approved'   => Document::aksesibel($user)->approved()->count(),
            'kadaluwarsa'=> Document::aksesibel($user)->kadaluwarsa()->count(),
        ];

        return view('document.index', compact('query', 'docTypes', 'unitKerjas', 'stats'));
    }

    public function create(Request $request)
    {
        $docTypes  = DocumentType::aktif()->orderBy('kategori')->get();
        $locations = PhysicalLocation::orderBy('gedung')->get();
        $unitKerjas = UnitKerja::aktif()->whereIn('tipe', ['satker','ppk'])->orderBy('nama')->get();

        // Jika dari halaman aset/proyek, pre-fill entity
        $entityType = $request->entity_type;
        $entityId   = $request->entity_id;
        $entity     = null;
        if ($entityType && $entityId) {
            $modelClass = 'App\\Models\\' . class_basename($entityType);
            $entity     = $modelClass::find($entityId);
        }

        return view('document.create', compact('docTypes', 'locations', 'unitKerjas', 'entity', 'entityType', 'entityId'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'judul'              => 'required|string|max:255',
            'doc_number'         => 'nullable|string|max:50',
            'entity_type'        => 'nullable|string',
            'entity_id'          => 'nullable|uuid',
            'entity_fase'        => 'nullable|in:before,during,after,op,umum',
            'document_type_id'   => 'required|exists:document_types,id',
            'unit_kerja_id'      => 'nullable|exists:unit_kerja,id',
            'klasifikasi'        => 'required|in:biasa,terbatas,rahasia',
            'tgl_dokumen'        => 'nullable|date',
            'tgl_diterima'       => 'nullable|date',
            'tgl_kedaluwarsa'    => 'nullable|date|after:tgl_dokumen',
            'physical_location_id' => 'nullable|exists:physical_locations,id',
            'ada_fisik'          => 'boolean',
            'ada_digital'        => 'boolean',
            'deskripsi'          => 'nullable|string',
            'tags'               => 'nullable|string', // comma-separated
            'file'               => 'nullable|file|max:51200', // 50MB
        ]);

        // Convert tags string ke array
        if (!empty($validated['tags'])) {
            $validated['tags'] = array_map('trim', explode(',', $validated['tags']));
        }

        // Buat nomor dokumen otomatis jika kosong
        if (empty($validated['doc_number'])) {
            $type = DocumentType::find($validated['document_type_id']);
            $validated['doc_number'] = Document::generateNomor(
                $type?->kode ?? 'DOC',
                now()->year
            );
        }

        // Generate QR code unik
        $qrCode = Str::upper(Str::random(10));
        $validated['qr_code']     = $qrCode;
        $validated['uploaded_by'] = auth()->id();
        $validated['ada_fisik']   = $request->boolean('ada_fisik');
        $validated['ada_digital'] = $request->hasFile('file') || $request->boolean('ada_digital');

        DB::beginTransaction();
        try {
            $document = Document::create($validated);

            // Upload file
            if ($request->hasFile('file')) {
                $this->uploadFile($request, $document, true);
                $document->update(['ada_digital' => true]);
            }

            // Generate QR code image
            $this->generateQrCode($document);

            // Update kapasitas lokasi fisik
            if ($document->physical_location_id && $document->ada_fisik) {
                PhysicalLocation::find($document->physical_location_id)
                    ->increment('terisi_item');
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal menyimpan dokumen: ' . $e->getMessage());
        }

        return redirect()->route('documents.show', $document)
            ->with('success', "Dokumen <strong>{$document->judul}</strong> berhasil diunggah.");
    }

    public function show(Document $document)
    {
        $this->cekAkses($document);

        $document->load([
            'documentType', 'uploadedBy', 'approvedBy',
            'unitKerja', 'physicalLocation',
            'files', 'parentDoc',
            'loans' => fn($q) => $q->latest()->limit(5),
        ]);

        // Increment view count (async via job idealnya)
        $document->increment('view_count');

        // Audit log view
        DB::table('audit_logs')->insert([
            'user_id'      => auth()->id(),
            'user_name'    => auth()->user()->name,
            'action'       => 'read',
            'entity_type'  => 'Document',
            'entity_id'    => $document->id,
            'entity_label' => $document->judul,
            'ip_address'   => request()->ip(),
            'created_at'   => now(),
        ]);

        // Riwayat versi
        $riwayatVersi = collect();
        $current = $document;
        while ($current->parentDoc) {
            $riwayatVersi->push($current->parentDoc);
            $current = $current->parentDoc;
        }

        return view('document.show', compact('document', 'riwayatVersi'));
    }

    public function edit(Document $document)
    {
        $this->cekAkses($document);
        $docTypes   = DocumentType::aktif()->get();
        $locations  = PhysicalLocation::orderBy('gedung')->get();
        $unitKerjas = UnitKerja::aktif()->whereIn('tipe', ['satker','ppk'])->orderBy('nama')->get();

        return view('document.create', compact('document', 'docTypes', 'locations', 'unitKerjas'));
    }

    public function update(Request $request, Document $document)
    {
        $this->cekAkses($document);

        $validated = $request->validate([
            'judul'              => 'required|string|max:255',
            'doc_number'         => 'nullable|string|max:50',
            'entity_fase'        => 'nullable|in:before,during,after,op,umum',
            'document_type_id'   => 'required|exists:document_types,id',
            'unit_kerja_id'      => 'nullable|exists:unit_kerja,id',
            'klasifikasi'        => 'required|in:biasa,terbatas,rahasia',
            'tgl_dokumen'        => 'nullable|date',
            'tgl_kedaluwarsa'    => 'nullable|date',
            'physical_location_id' => 'nullable|exists:physical_locations,id',
            'ada_fisik'          => 'boolean',
            'deskripsi'          => 'nullable|string',
            'tags'               => 'nullable|string',
        ]);

        if (!empty($validated['tags'])) {
            $validated['tags'] = array_map('trim', explode(',', $validated['tags']));
        }

        $document->update($validated);

        return redirect()->route('documents.show', $document)
            ->with('success', "Dokumen <strong>{$document->judul}</strong> berhasil diperbarui.");
    }

    public function destroy(Document $document)
    {
        // Hapus file fisik
        foreach ($document->files as $file) {
            Storage::disk($file->file_disk)->delete($file->file_path);
        }
        if ($document->qr_code_path) {
            Storage::disk('public')->delete($document->qr_code_path);
        }

        $judul = $document->judul;
        $document->delete();

        return redirect()->route('documents.index')
            ->with('success', "Dokumen <strong>{$judul}</strong> berhasil dihapus.");
    }

    public function download(Document $document)
    {
        $this->cekAkses($document);

        $file = $document->primaryFile;
        if (!$file || !Storage::disk($file->file_disk)->exists($file->file_path)) {
            return back()->with('error', 'File tidak ditemukan di server.');
        }

        // Audit log download
        DB::table('audit_logs')->insert([
            'user_id'      => auth()->id(),
            'user_name'    => auth()->user()->name,
            'action'       => 'download',
            'entity_type'  => 'Document',
            'entity_id'    => $document->id,
            'entity_label' => $document->judul,
            'ip_address'   => request()->ip(),
            'created_at'   => now(),
        ]);

        $document->increment('download_count');

        return Storage::disk($file->file_disk)->download($file->file_path, $file->file_name);
    }

    public function approve(Document $document)
    {
        $document->update([
            'status'      => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return back()->with('success', "Dokumen <strong>{$document->judul}</strong> telah disetujui.");
    }

    public function newVersion(Request $request, Document $document)
    {
        $request->validate([
            'file'      => 'required|file|max:51200',
            'keterangan'=> 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            // Tandai versi lama sebagai superseded
            $document->update(['status' => 'superseded']);

            // Buat dokumen baru sebagai versi baru
            $newDoc = $document->replicate([
                'status', 'approved_by', 'approved_at', 'download_count', 'view_count',
            ]);
            $newDoc->parent_doc_id  = $document->id;
            $newDoc->versi_mayor    = $document->versi_mayor;
            $newDoc->versi_minor    = $document->versi_minor + 1;
            $newDoc->status         = 'draft';
            $newDoc->uploaded_by    = auth()->id();
            $newDoc->approved_by    = null;
            $newDoc->approved_at    = null;
            $newDoc->download_count = 0;
            $newDoc->view_count     = 0;
            $newDoc->save();

            $this->uploadFile($request, $newDoc, true);

            DB::commit();

            return redirect()->route('documents.show', $newDoc)
                ->with('success', "Versi baru {$newDoc->versiLabel()} berhasil diunggah.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal membuat versi baru: ' . $e->getMessage());
        }
    }

    public function qr(Document $document)
    {
        $this->cekAkses($document);
        return view('document.qr', compact('document'));
    }

    public function versions(Document $document)
    {
        $this->cekAkses($document);
        $document->load(['versions.uploadedBy', 'parentDoc']);
        return view('document.versions', compact('document'));
    }

    public function preview(Document $document)
    {
        $this->cekAkses($document);
        $file = $document->primaryFile;
        if (!$file) return back()->with('error', 'Tidak ada file untuk dipreview.');

        // Hanya PDF dan gambar yang bisa dipreview
        if (in_array($file->mime_type, ['application/pdf', 'image/jpeg', 'image/png', 'image/gif'])) {
            return response()->file(
                Storage::disk($file->file_disk)->path($file->file_path),
                ['Content-Disposition' => 'inline; filename="' . $file->file_name . '"']
            );
        }

        return $this->download($document);
    }

    // ── Helper private ────────────────────────────────────────────────
    private function cekAkses(Document $document): void
    {
        $user = auth()->user();
        if ($document->klasifikasi === 'rahasia' && !$user->can('document.view_rahasia')) {
            abort(403, 'Dokumen ini bersifat rahasia.');
        }
        if ($document->klasifikasi === 'terbatas' && !$user->can('document.view_terbatas')) {
            abort(403, 'Dokumen ini bersifat terbatas.');
        }
    }

    private function uploadFile(Request $request, Document $document, bool $isPrimary = false): DocumentFile
    {
        $file  = $request->file('file');
        $path  = $file->store("documents/{$document->id}", 'local');
        $hash  = hash_file('sha256', $file->getRealPath());

        return DocumentFile::create([
            'document_id'   => $document->id,
            'file_path'     => $path,
            'file_name'     => $file->getClientOriginalName(),
            'file_disk'     => 'local',
            'mime_type'     => $file->getMimeType(),
            'file_size'     => $file->getSize(),
            'file_hash'     => $hash,
            'is_primary'    => $isPrimary,
            'uploaded_by'   => auth()->id(),
        ]);
    }

    private function generateQrCode(Document $document): void
    {
        try {
            $url  = route('documents.show', $document);
            $path = "qr/documents/{$document->id}.svg";
            Storage::disk('public')->put($path, QrCode::format('svg')->size(200)->generate($url));
            $document->update(['qr_code_path' => $path]);
        } catch (\Throwable $e) {
            logger()->warning("QR gagal untuk dokumen {$document->id}: " . $e->getMessage());
        }
    }
}
