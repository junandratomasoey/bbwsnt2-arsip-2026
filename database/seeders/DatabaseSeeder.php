<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\UnitKerja;
use App\Models\AssetType;
use App\Models\DocumentType;
use App\Models\KnowledgeCategory;
use App\Models\Role;
use App\Models\Permission;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            PermissionSeeder::class,
            UnitKerjaSeeder::class,
            UserSeeder::class,
            AssetTypeSeeder::class,
            DocumentTypeSeeder::class,
            KnowledgeCategorySeeder::class,
        ]);

        $this->command->info('');
        $this->command->info('✅ WIAKMS seeding selesai!');
        $this->command->info('   Login: bbwsnt2@gmail.com');
        $this->command->warn('   Password: BbwsNT2@2025! — GANTI SEGERA!');
    }
}

// ============================================================
class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            // Users & Roles
            'user.view','user.create','user.edit','user.delete','user.approve',
            'role.view','role.create','role.edit','role.delete',
            'unit_kerja.view','unit_kerja.create','unit_kerja.edit','unit_kerja.delete',

            // Assets
            'asset.view','asset.create','asset.edit','asset.delete',
            'asset.view_valuasi',       // Lihat nilai aset (BMN)
            'asset_condition.view','asset_condition.create','asset_condition.edit',
            'asset_geometry.edit',      // Edit GIS geometry

            // Projects
            'project.view','project.create','project.edit','project.delete',
            'project_progress.create','project_progress.edit',

            // OP
            'op_record.view','op_record.create','op_record.edit','op_record.delete',
            'op_schedule.view','op_schedule.create','op_schedule.edit','op_schedule.approve',

            // Documents
            'document.view',
            'document.view_terbatas',   // Lihat dokumen klasifikasi terbatas
            'document.view_rahasia',    // Lihat dokumen rahasia
            'document.create','document.edit','document.delete',
            'document.download','document.upload',
            'document.approve',         // Setujui dokumen (ubah status ke approved)

            // Physical locations
            'physical_location.view','physical_location.create',
            'physical_location.edit','physical_location.delete',

            // Loans
            'loan.view','loan.create','loan.approve','loan.delete',

            // Knowledge
            'knowledge.view','knowledge.create','knowledge.edit','knowledge.delete','knowledge.publish',

            // Library
            'library.view','library.create','library.edit',
            'library_loan.view','library_loan.create','library_loan.approve',

            // Dashboard & Reports
            'dashboard.view',
            'report.view','report.export',
            'dashboard.executive',      // Dashboard pimpinan (ringkasan strategis)

            // System
            'audit_log.view',
            'system_config.edit',
            'workflow.manage',
        ];

        foreach ($permissions as $p) {
            Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
        }
        $this->command->info('✓ Permissions: ' . count($permissions) . ' dibuat');

        // ── ROLES ──────────────────────────────────────────────────────
        // Superadmin — bypass semua via Gate::before
        Role::firstOrCreate(['name' => 'superadmin', 'guard_name' => 'web']);

        // Admin Satker — kelola semua dalam satkernya
        $adminSatker = Role::firstOrCreate(['name' => 'admin_satker', 'guard_name' => 'web']);
        $adminSatker->syncPermissions([
            'user.view','user.approve',
            'unit_kerja.view',
            'asset.view','asset.create','asset.edit',
            'asset_condition.view','asset_condition.create','asset_condition.edit',
            'project.view','project.create','project.edit',
            'project_progress.create','project_progress.edit',
            'op_record.view','op_record.create','op_record.edit',
            'op_schedule.view','op_schedule.create','op_schedule.edit','op_schedule.approve',
            'document.view','document.view_terbatas','document.create',
            'document.edit','document.download','document.upload','document.approve',
            'physical_location.view','physical_location.create','physical_location.edit',
            'loan.view','loan.approve',
            'knowledge.view','knowledge.create','knowledge.edit','knowledge.publish',
            'library.view','library.create','library.edit',
            'library_loan.view','library_loan.approve',
            'dashboard.view','dashboard.executive',
            'report.view','report.export',
            'audit_log.view',
        ]);

        // Arsiparis — fokus dokumen & arsip fisik
        $arsiparis = Role::firstOrCreate(['name' => 'arsiparis', 'guard_name' => 'web']);
        $arsiparis->syncPermissions([
            'asset.view',
            'project.view',
            'op_record.view',
            'document.view','document.view_terbatas','document.create',
            'document.edit','document.download','document.upload',
            'physical_location.view','physical_location.create','physical_location.edit',
            'loan.view','loan.approve',
            'library.view','library.create','library.edit',
            'library_loan.view','library_loan.approve',
            'knowledge.view',
            'dashboard.view',
            'report.view',
        ]);

        // Operator Teknis — fokus aset, proyek, OP
        $opTeknis = Role::firstOrCreate(['name' => 'operator_teknis', 'guard_name' => 'web']);
        $opTeknis->syncPermissions([
            'asset.view','asset.create','asset.edit',
            'asset_condition.view','asset_condition.create','asset_condition.edit',
            'asset_geometry.edit',
            'project.view','project.create','project.edit',
            'project_progress.create','project_progress.edit',
            'op_record.view','op_record.create','op_record.edit',
            'op_schedule.view','op_schedule.create',
            'document.view','document.create','document.upload','document.download',
            'knowledge.view','knowledge.create',
            'dashboard.view',
            'report.view',
        ]);

        // Peminjam — hanya bisa pinjam dokumen & buku
        $peminjam = Role::firstOrCreate(['name' => 'peminjam', 'guard_name' => 'web']);
        $peminjam->syncPermissions([
            'document.view','document.download',
            'loan.view','loan.create',
            'library.view',
            'library_loan.view','library_loan.create',
            'knowledge.view',
            'dashboard.view',
        ]);

        // Viewer — hanya lihat, tidak bisa download
        $viewer = Role::firstOrCreate(['name' => 'viewer', 'guard_name' => 'web']);
        $viewer->syncPermissions([
            'asset.view','project.view','op_record.view',
            'document.view',
            'knowledge.view','library.view',
            'dashboard.view',
        ]);

        // Pimpinan — dashboard eksekutif + laporan, tidak perlu akses detail
        $pimpinan = Role::firstOrCreate(['name' => 'pimpinan', 'guard_name' => 'web']);
        $pimpinan->syncPermissions([
            'asset.view','asset.view_valuasi',
            'project.view','op_record.view',
            'document.view',
            'dashboard.view','dashboard.executive',
            'report.view','report.export',
        ]);

        $this->command->info('✓ Roles: 7 role dibuat dengan permissions');
    }
}

// ============================================================
class UnitKerjaSeeder extends Seeder
{
    public function run(): void
    {
        // Root
        $balai = UnitKerja::firstOrCreate(['kode' => 'BBWSNT2'], [
            'parent_id'  => null,
            'tipe'       => 'balai',
            'nama'       => 'BBWS Nusa Tenggara II',
            'singkatan'  => 'BBWS NT II',
            'alamat'     => 'Kupang, Nusa Tenggara Timur',
            'is_aktif'   => true,
            'urutan'     => 0,
        ]);

        // Bagian
        $bagianUmum = UnitKerja::firstOrCreate(['kode' => 'BAG-UMUM-TU'], [
            'parent_id' => $balai->id, 'tipe' => 'bagian',
            'nama' => 'Umum dan Tata Usaha', 'singkatan' => 'Bag. Umum & TU',
            'is_aktif' => true, 'urutan' => 1,
        ]);

        // Bidang
        $bidKpisda = UnitKerja::firstOrCreate(['kode' => 'BID-KPISDA'], [
            'parent_id' => $balai->id, 'tipe' => 'bidang',
            'nama' => 'KPISDA', 'singkatan' => 'Bid. KPISDA',
            'is_aktif' => true, 'urutan' => 2,
        ]);
        $bidPelaks = UnitKerja::firstOrCreate(['kode' => 'BID-PELAKS'], [
            'parent_id' => $balai->id, 'tipe' => 'bidang',
            'nama' => 'Pelaksanaan', 'singkatan' => 'Bid. Pelaksanaan',
            'is_aktif' => true, 'urutan' => 3,
        ]);
        $bidOp = UnitKerja::firstOrCreate(['kode' => 'BID-OP'], [
            'parent_id' => $balai->id, 'tipe' => 'bidang',
            'nama' => 'OP', 'singkatan' => 'Bid. OP',
            'is_aktif' => true, 'urutan' => 4,
        ]);

        // Satker
        $satkers = [
            ['kode' => 'SK-BALAI',    'nama' => 'Balai',                'singkatan' => 'Satker Balai',    'urutan' => 5],
            ['kode' => 'SK-OP',       'nama' => 'OP',                   'singkatan' => 'Satker OP',       'urutan' => 6],
            ['kode' => 'SK-PJSA',     'nama' => 'PJSA',                 'singkatan' => 'Satker PJSA',     'urutan' => 7],
            ['kode' => 'SK-PJPA',     'nama' => 'PJPA',                 'singkatan' => 'Satker PJPA',     'urutan' => 8],
            ['kode' => 'SK-ATAB',     'nama' => 'Air Tanah dan Air Baku','singkatan' => 'Satker ATAB',    'urutan' => 9],
            ['kode' => 'SK-BEND-I',   'nama' => 'Bendungan I',          'singkatan' => 'Satker Bend. I',  'urutan' => 10],
            ['kode' => 'SK-BEND-II',  'nama' => 'Bendungan II',         'singkatan' => 'Satker Bend. II', 'urutan' => 11],
        ];

        $satkerModels = [];
        foreach ($satkers as $s) {
            $satkerModels[$s['kode']] = UnitKerja::firstOrCreate(['kode' => $s['kode']], [
                'parent_id' => $balai->id, 'tipe' => 'satker',
                'nama' => $s['nama'], 'singkatan' => $s['singkatan'],
                'is_aktif' => true, 'urutan' => $s['urutan'],
            ]);
        }

        // PPK
        $ppks = [
            ['kode' => 'PPK-IRIGASI-I',  'nama' => 'Irigasi I',          'satker' => 'SK-PJSA',   'urutan' => 1],
            ['kode' => 'PPK-IRIGASI-II', 'nama' => 'Irigasi II',         'satker' => 'SK-PJSA',   'urutan' => 2],
            ['kode' => 'PPK-SUNGAI',     'nama' => 'Sungai dan Pantai',  'satker' => 'SK-PJSA',   'urutan' => 3],
            ['kode' => 'PPK-JARINGAN',   'nama' => 'Jaringan Irigasi',   'satker' => 'SK-PJPA',   'urutan' => 1],
            ['kode' => 'PPK-AIR-BAKU',   'nama' => 'Air Baku',           'satker' => 'SK-ATAB',   'urutan' => 1],
            ['kode' => 'PPK-AIR-TANAH',  'nama' => 'Air Tanah',          'satker' => 'SK-ATAB',   'urutan' => 2],
            ['kode' => 'PPK-BEND-I',     'nama' => 'Bendungan I',        'satker' => 'SK-BEND-I', 'urutan' => 1],
            ['kode' => 'PPK-BEND-II',    'nama' => 'Bendungan II',       'satker' => 'SK-BEND-II','urutan' => 1],
            ['kode' => 'PPK-OP-IRIGASI', 'nama' => 'OP Irigasi',         'satker' => 'SK-OP',     'urutan' => 1],
            ['kode' => 'PPK-OP-SUNGAI',  'nama' => 'OP Sungai & Pantai', 'satker' => 'SK-OP',     'urutan' => 2],
        ];

        foreach ($ppks as $p) {
            UnitKerja::firstOrCreate(['kode' => $p['kode']], [
                'parent_id' => $satkerModels[$p['satker']]->id,
                'tipe' => 'ppk', 'nama' => $p['nama'],
                'singkatan' => 'PPK ' . $p['nama'],
                'is_aktif' => true, 'urutan' => $p['urutan'],
            ]);
        }

        $total = UnitKerja::count();
        $this->command->info("✓ Unit Kerja: {$total} record (1 balai, 1 bagian, 3 bidang, 7 satker, 10 PPK)");
    }
}

// ============================================================
class UserSeeder extends Seeder
{
    public function run(): void
    {
        $balai = UnitKerja::where('kode', 'BBWSNT2')->first();

        $superAdmin = User::firstOrCreate(['email' => 'bbwsnt2@gmail.com'], [
            'name'              => 'Super Admin BBWS NT II',
            'nip'               => '000000000000000000',
            'jabatan_struktural'=> 'Administrator Sistem',
            'unit_kerja_id'     => $balai?->id,
            'status'            => 'aktif',
            'password'          => Hash::make('BbwsNT2@2025!'),
            'email_verified_at' => now(),
            'approved_at'       => now(),
        ]);
        $superAdmin->syncRoles(['superadmin']);
        $this->command->info('✓ Superadmin: bbwsnt2@gmail.com dibuat');
    }
}

// ============================================================
class AssetTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            [
                'kode' => 'BDG', 'nama' => 'Bendung', 'kategori' => 'bendung',
                'standar_op' => 'Permen PUPR No. 12/2015',
                'atribut_teknis_template' => [
                    'lebar_mercu_m' => null, 'tinggi_bendung_m' => null,
                    'luas_daerah_irigasi_ha' => null, 'debit_rencana_m3s' => null,
                    'jumlah_pintu_air' => null,
                ],
                'checklist_dokumen_wajib' => [
                    'before' => ['kontrak','gambar_rencana','spesifikasi_teknis','rab','amdal'],
                    'during' => ['laporan_bulanan','berita_acara_serah_terima'],
                    'after'  => ['gambar_asbuilt','berita_acara_serah_terima','laporan_akhir'],
                    'op'     => ['manual_op','laporan_op_bulanan'],
                ],
            ],
            [
                'kode' => 'EMB', 'nama' => 'Embung', 'kategori' => 'embung',
                'standar_op' => 'Permen PUPR No. 12/2015',
                'atribut_teknis_template' => [
                    'kapasitas_tampung_m3' => null, 'luas_genangan_ha' => null,
                    'tinggi_tubuh_embung_m' => null, 'panjang_mercu_m' => null,
                ],
                'checklist_dokumen_wajib' => [
                    'before' => ['kontrak','gambar_rencana','rab','amdal'],
                    'after'  => ['gambar_asbuilt','berita_acara_serah_terima','laporan_akhir'],
                    'op'     => ['manual_op'],
                ],
            ],
            [
                'kode' => 'WDK', 'nama' => 'Waduk', 'kategori' => 'waduk',
                'standar_op' => 'Permen PUPR No. 27/2015',
                'atribut_teknis_template' => [
                    'kapasitas_total_juta_m3' => null, 'kapasitas_efektif_juta_m3' => null,
                    'tinggi_bendungan_m' => null, 'luas_genangan_ha' => null,
                    'tipe_bendungan' => null,
                ],
                'checklist_dokumen_wajib' => [
                    'before' => ['kontrak','gambar_rencana','rab','amdal','izin_lingkungan'],
                    'after'  => ['gambar_asbuilt','berita_acara_serah_terima','laporan_akhir','sertifikat_laik_operasi'],
                    'op'     => ['manual_op','rtow','laporan_op_bulanan'],
                ],
            ],
            [
                'kode' => 'SLR', 'nama' => 'Saluran Irigasi', 'kategori' => 'saluran_irigasi',
                'standar_op' => 'Permen PUPR No. 12/2015',
                'atribut_teknis_template' => [
                    'panjang_total_m' => null, 'kapasitas_debit_m3s' => null,
                    'tipe_saluran' => null, 'luas_daerah_layanan_ha' => null,
                ],
                'checklist_dokumen_wajib' => [
                    'before' => ['kontrak','gambar_rencana','rab'],
                    'after'  => ['gambar_asbuilt','berita_acara_serah_terima'],
                    'op'     => ['laporan_op_bulanan'],
                ],
            ],
            [
                'kode' => 'JAB', 'nama' => 'Jaringan Air Baku', 'kategori' => 'air_baku',
                'standar_op' => 'Permen PUPR No. 4/2015',
                'atribut_teknis_template' => [
                    'kapasitas_m3_hari' => null, 'panjang_pipa_m' => null,
                    'jumlah_sambungan_rumah' => null,
                ],
                'checklist_dokumen_wajib' => [
                    'before' => ['kontrak','gambar_rencana','rab'],
                    'after'  => ['gambar_asbuilt','berita_acara_serah_terima','laporan_akhir'],
                    'op'     => ['manual_op'],
                ],
            ],
            [
                'kode' => 'TGL', 'nama' => 'Tanggul', 'kategori' => 'tanggul',
                'atribut_teknis_template' => [
                    'panjang_m' => null, 'tinggi_m' => null, 'material' => null,
                ],
                'checklist_dokumen_wajib' => [
                    'before' => ['kontrak','gambar_rencana','rab'],
                    'after'  => ['gambar_asbuilt','berita_acara_serah_terima'],
                ],
            ],
            [
                'kode' => 'DRN', 'nama' => 'Drainase', 'kategori' => 'drainase',
                'atribut_teknis_template' => [
                    'panjang_m' => null, 'kapasitas_m3s' => null, 'tipe' => null,
                ],
                'checklist_dokumen_wajib' => [
                    'before' => ['kontrak','gambar_rencana','rab'],
                    'after'  => ['gambar_asbuilt','berita_acara_serah_terima'],
                ],
            ],
        ];

        foreach ($types as $i => $t) {
            AssetType::firstOrCreate(['kode' => $t['kode']], array_merge($t, [
                'urutan'   => $i + 1,
                'is_aktif' => true,
            ]));
        }

        $this->command->info('✓ Asset Types: ' . count($types) . ' jenis aset dibuat');
    }
}

// ============================================================
class DocumentTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            // Teknis
            ['kode'=>'KTR',  'nama'=>'Kontrak',              'kategori'=>'teknis',       'retensi_aktif_tahun'=>10,'nasib_akhir'=>'permanen'],
            ['kode'=>'ADDR',  'nama'=>'Addendum',             'kategori'=>'teknis',       'retensi_aktif_tahun'=>10,'nasib_akhir'=>'permanen'],
            ['kode'=>'GBR',  'nama'=>'Gambar Rencana',        'kategori'=>'teknis',       'retensi_aktif_tahun'=>20,'nasib_akhir'=>'permanen'],
            ['kode'=>'GABT', 'nama'=>'Gambar As-Built',       'kategori'=>'teknis',       'retensi_aktif_tahun'=>30,'nasib_akhir'=>'permanen'],
            ['kode'=>'SPEK', 'nama'=>'Spesifikasi Teknis',    'kategori'=>'teknis',       'retensi_aktif_tahun'=>10,'nasib_akhir'=>'permanen'],
            ['kode'=>'RAB',  'nama'=>'RAB / BOQ',             'kategori'=>'keuangan',     'retensi_aktif_tahun'=>10,'nasib_akhir'=>'musnah'],
            ['kode'=>'LPH',  'nama'=>'Laporan Harian',        'kategori'=>'laporan',      'retensi_aktif_tahun'=>5, 'nasib_akhir'=>'musnah'],
            ['kode'=>'LPM',  'nama'=>'Laporan Mingguan',      'kategori'=>'laporan',      'retensi_aktif_tahun'=>5, 'nasib_akhir'=>'musnah'],
            ['kode'=>'LPB',  'nama'=>'Laporan Bulanan',       'kategori'=>'laporan',      'retensi_aktif_tahun'=>10,'nasib_akhir'=>'sampling'],
            ['kode'=>'LPA',  'nama'=>'Laporan Akhir',         'kategori'=>'laporan',      'retensi_aktif_tahun'=>20,'nasib_akhir'=>'permanen'],
            ['kode'=>'BAP',  'nama'=>'Berita Acara',          'kategori'=>'administrasi', 'retensi_aktif_tahun'=>10,'nasib_akhir'=>'permanen'],
            ['kode'=>'FOTO', 'nama'=>'Foto Dokumentasi',      'kategori'=>'dokumentasi',  'retensi_aktif_tahun'=>10,'nasib_akhir'=>'sampling'],
            ['kode'=>'SRFT', 'nama'=>'Sertifikat',            'kategori'=>'legalitas',    'retensi_aktif_tahun'=>30,'nasib_akhir'=>'permanen'],
            ['kode'=>'IZN',  'nama'=>'Izin / Perizinan',      'kategori'=>'legalitas',    'retensi_aktif_tahun'=>20,'nasib_akhir'=>'permanen'],
            ['kode'=>'AMDI', 'nama'=>'AMDAL / UKL-UPL',      'kategori'=>'lingkungan',   'retensi_aktif_tahun'=>20,'nasib_akhir'=>'permanen'],
            ['kode'=>'MOP',  'nama'=>'Manual OP',             'kategori'=>'teknis',       'retensi_aktif_tahun'=>20,'nasib_akhir'=>'permanen'],
            ['kode'=>'RTOW', 'nama'=>'RTOW (Rule Curve)',     'kategori'=>'teknis',       'retensi_aktif_tahun'=>20,'nasib_akhir'=>'permanen'],
            ['kode'=>'LOPO', 'nama'=>'Laporan OP Bulanan',    'kategori'=>'laporan',      'retensi_aktif_tahun'=>10,'nasib_akhir'=>'sampling'],
            ['kode'=>'SLO',  'nama'=>'Sertifikat Laik Operasi','kategori'=>'legalitas',   'retensi_aktif_tahun'=>30,'nasib_akhir'=>'permanen'],
            ['kode'=>'LIN',  'nama'=>'Lainnya',               'kategori'=>'lainnya',      'retensi_aktif_tahun'=>5, 'nasib_akhir'=>'musnah'],
        ];

        foreach ($types as $i => $t) {
            DocumentType::firstOrCreate(['kode' => $t['kode']], array_merge($t, [
                'retensi_inaktif_tahun' => ($t['retensi_aktif_tahun'] ?? 5) * 2,
                'urutan'   => $i + 1,
                'is_aktif' => true,
            ]));
        }

        $this->command->info('✓ Document Types: ' . count($types) . ' jenis dokumen dibuat');
    }
}

// ============================================================
class KnowledgeCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['nama'=>'Teknis Irigasi',    'slug'=>'teknis-irigasi',    'ikon'=>'ti-droplet',       'warna'=>'blue'],
            ['nama'=>'Teknis Bendungan',  'slug'=>'teknis-bendungan',  'ikon'=>'ti-building-dam',  'warna'=>'teal'],
            ['nama'=>'OP & Pemeliharaan', 'slug'=>'op-pemeliharaan',   'ikon'=>'ti-settings-2',    'warna'=>'amber'],
            ['nama'=>'Regulasi & Hukum',  'slug'=>'regulasi-hukum',    'ikon'=>'ti-scale',         'warna'=>'red'],
            ['nama'=>'Manajemen Proyek',  'slug'=>'manajemen-proyek',  'ikon'=>'ti-clipboard-list','warna'=>'purple'],
            ['nama'=>'K3 & Lingkungan',   'slug'=>'k3-lingkungan',     'ikon'=>'ti-leaf',          'warna'=>'green'],
            ['nama'=>'Umum',              'slug'=>'umum',              'ikon'=>'ti-book',          'warna'=>'gray'],
        ];

        foreach ($categories as $i => $c) {
            KnowledgeCategory::firstOrCreate(['slug' => $c['slug']], array_merge($c, [
                'urutan' => $i + 1, 'is_aktif' => true,
            ]));
        }

        $this->command->info('✓ Knowledge Categories: ' . count($categories) . ' kategori dibuat');
    }
}
