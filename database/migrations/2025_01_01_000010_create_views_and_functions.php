<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // ============================================================
        // HELPER FUNCTIONS
        // ============================================================

        DB::statement("
            CREATE OR REPLACE FUNCTION fn_asset_age(tahun_bangun smallint)
            RETURNS integer AS \$\$
            BEGIN
                RETURN EXTRACT(YEAR FROM CURRENT_DATE)::integer
                       - COALESCE(tahun_bangun, 0);
            END;
            \$\$ LANGUAGE plpgsql STABLE;
        ");

        DB::statement("
            CREATE OR REPLACE FUNCTION fn_asset_remaining_life(
                tahun_bangun smallint,
                umur_rencana smallint
            )
            RETURNS integer AS \$\$
            BEGIN
                RETURN COALESCE(umur_rencana, 50)
                       - (EXTRACT(YEAR FROM CURRENT_DATE)::integer
                          - COALESCE(tahun_bangun, 0));
            END;
            \$\$ LANGUAGE plpgsql STABLE;
        ");

        DB::statement("
            CREATE OR REPLACE FUNCTION fn_rci_to_kondisi(rci_score decimal)
            RETURNS char(1) AS \$\$
            BEGIN
                RETURN CASE
                    WHEN rci_score >= 80 THEN 'A'
                    WHEN rci_score >= 60 THEN 'B'
                    WHEN rci_score >= 40 THEN 'C'
                    ELSE 'D'
                END;
            END;
            \$\$ LANGUAGE plpgsql IMMUTABLE;
        ");

        // ============================================================
        // VIEW: v_asset_summary
        // entity_id di documents bertipe uuid — bandingkan langsung,
        // bukan via ::text cast
        // ============================================================
        DB::statement("
            CREATE OR REPLACE VIEW v_asset_summary AS
            SELECT
                a.id,
                a.asset_code,
                a.nama,
                at.nama                         AS jenis_aset,
                at.kategori,
                uk.nama                         AS unit_kerja,
                uk.singkatan                    AS satker,
                a.kabupaten,
                a.kecamatan,
                a.lifecycle_status,
                a.kondisi_terakhir,
                a.rci_score_terakhir,
                a.tgl_inspeksi_terakhir,
                fn_asset_age(a.tahun_bangun)                                    AS umur_tahun,
                fn_asset_remaining_life(a.tahun_bangun, a.umur_rencana_tahun)   AS sisa_umur_tahun,
                ag.geom                         AS geom_utama,
                (
                    SELECT COUNT(*) FROM documents d
                    WHERE d.entity_type = 'App\Models\Asset'
                    AND   d.entity_id::uuid = a.id
                    AND   d.deleted_at IS NULL
                ) AS jumlah_dokumen,
                (
                    SELECT opr.status FROM op_records opr
                    WHERE  opr.asset_id = a.id
                    AND    opr.periode_tahun  = EXTRACT(YEAR  FROM CURRENT_DATE)::int
                    AND    opr.periode_bulan  = EXTRACT(MONTH FROM CURRENT_DATE)::int
                    LIMIT 1
                ) AS status_op_bulan_ini,
                a.is_aktif,
                a.created_at,
                a.updated_at
            FROM       assets a
            LEFT JOIN  asset_types     at  ON at.id  = a.asset_type_id
            LEFT JOIN  unit_kerja      uk  ON uk.id  = a.unit_kerja_id
            LEFT JOIN  asset_geometries ag ON ag.asset_id = a.id
                                          AND ag.is_primary = true
            WHERE a.deleted_at IS NULL
        ");

        // ============================================================
        // VIEW: v_op_summary_tahunan
        // ============================================================
        DB::statement("
            CREATE OR REPLACE VIEW v_op_summary_tahunan AS
            SELECT
                a.id                                                        AS asset_id,
                a.asset_code,
                a.nama                                                      AS nama_aset,
                at.kategori                                                 AS jenis_aset,
                uk.singkatan                                                AS satker,
                opr.periode_tahun,
                COUNT(opr.id)                                               AS total_record_op,
                ROUND(AVG(opr.realisasi_pct)::numeric, 2)                  AS avg_realisasi_pct,
                SUM(CASE WHEN opr.status = 'selesai'          THEN 1 ELSE 0 END) AS bulan_op_selesai,
                SUM(CASE WHEN opr.status = 'tidak_terlaksana' THEN 1 ELSE 0 END) AS bulan_tidak_terlaksana,
                SUM(opr.realisasi_anggaran)                                 AS total_realisasi_anggaran,
                CASE
                    WHEN AVG(opr.realisasi_pct) >= 90 THEN 'Sangat Baik'
                    WHEN AVG(opr.realisasi_pct) >= 70 THEN 'Baik'
                    WHEN AVG(opr.realisasi_pct) >= 50 THEN 'Cukup'
                    ELSE 'Kurang'
                END AS kategori_kinerja_op
            FROM       assets     a
            LEFT JOIN  asset_types at  ON at.id = a.asset_type_id
            LEFT JOIN  unit_kerja  uk  ON uk.id = a.unit_kerja_id
            LEFT JOIN  op_records  opr ON opr.asset_id = a.id
            WHERE a.deleted_at IS NULL
            GROUP BY a.id, a.asset_code, a.nama, at.kategori, uk.singkatan, opr.periode_tahun
        ");

        // ============================================================
        // VIEW: v_document_checklist
        // entity_id di documents bertipe uuid — cast ke uuid saat compare
        // ============================================================
        DB::statement("
            CREATE OR REPLACE VIEW v_document_checklist AS
            SELECT
                a.id            AS asset_id,
                a.asset_code,
                a.nama          AS nama_aset,
                dt.kode         AS kode_dokumen,
                dt.nama         AS jenis_dokumen,
                dt.kategori,
                EXISTS (
                    SELECT 1 FROM documents d
                    WHERE  d.entity_type      = 'App\Models\Asset'
                    AND    d.entity_id::uuid  = a.id
                    AND    d.document_type_id = dt.id
                    AND    d.deleted_at       IS NULL
                    AND    d.status           = 'approved'
                ) AS sudah_ada,
                (
                    SELECT MAX(d.tgl_dokumen) FROM documents d
                    WHERE  d.entity_type      = 'App\Models\Asset'
                    AND    d.entity_id::uuid  = a.id
                    AND    d.document_type_id = dt.id
                    AND    d.deleted_at       IS NULL
                ) AS tgl_dokumen_terakhir
            FROM      assets         a
            CROSS JOIN document_types dt
            WHERE a.deleted_at IS NULL
            AND   dt.is_aktif  = true
        ");

        // ============================================================
        // VIEW: v_project_dashboard
        // EXTRACT(DAY FROM interval) fix: date - date = integer di PG
        // ============================================================
        DB::statement("
            CREATE OR REPLACE VIEW v_project_dashboard AS
            SELECT
                p.id,
                p.project_code,
                p.nama,
                p.jenis,
                p.lifecycle_phase,
                uk.singkatan            AS satker,
                a.nama                  AS nama_aset,
                p.tahun_anggaran,
                p.nilai_kontrak,
                p.realisasi_fisik_pct,
                p.realisasi_keuangan_pct,
                p.tgl_mulai_rencana,
                p.tgl_selesai_rencana,
                CASE
                    WHEN p.tgl_selesai_rencana IS NOT NULL
                    AND  p.tgl_selesai_rencana < CURRENT_DATE
                    AND  p.lifecycle_phase NOT IN ('selesai', 'dibatalkan')
                    THEN (CURRENT_DATE - p.tgl_selesai_rencana)
                    ELSE 0
                END AS keterlambatan_hari,
                CASE
                    WHEN p.lifecycle_phase IN ('selesai', 'dibatalkan')                         THEN 'closed'
                    WHEN p.tgl_selesai_rencana < CURRENT_DATE                                   THEN 'overdue'
                    WHEN p.realisasi_fisik_pct < 50
                     AND p.tgl_selesai_rencana < CURRENT_DATE + INTERVAL '30 days'              THEN 'at_risk'
                    ELSE 'on_track'
                END AS health_status,
                (
                    SELECT COUNT(*) FROM project_milestones pm
                    WHERE  pm.project_id = p.id AND pm.status = 'selesai'
                ) AS milestone_selesai,
                (
                    SELECT COUNT(*) FROM project_milestones pm
                    WHERE  pm.project_id = p.id
                ) AS total_milestone
            FROM      projects   p
            LEFT JOIN unit_kerja uk ON uk.id = p.unit_kerja_id
            LEFT JOIN assets     a  ON a.id  = p.asset_id
            WHERE p.deleted_at IS NULL
        ");

        // ============================================================
        // MATERIALIZED VIEW: mv_dashboard_stats
        // Dibuat kosong dulu (WITH NO DATA), lalu buat unique index,
        // baru di-populate — ini urutan yang benar untuk CONCURRENTLY
        // ============================================================
        DB::statement("
            CREATE MATERIALIZED VIEW mv_dashboard_stats AS
            SELECT
                (SELECT COUNT(*) FROM assets    WHERE deleted_at IS NULL AND is_aktif = true)      AS total_aset,
                (SELECT COUNT(*) FROM assets    WHERE kondisi_terakhir IN ('C','D') AND deleted_at IS NULL) AS aset_kondisi_buruk,
                (SELECT COUNT(*) FROM assets    WHERE lifecycle_status = 'operating' AND deleted_at IS NULL) AS aset_operasional,
                (SELECT COUNT(*) FROM projects  WHERE lifecycle_phase NOT IN ('selesai','dibatalkan') AND deleted_at IS NULL) AS proyek_aktif,
                (SELECT COUNT(*) FROM projects  WHERE lifecycle_phase = 'selesai'
                    AND tahun_anggaran = EXTRACT(YEAR FROM CURRENT_DATE)::int
                    AND deleted_at IS NULL) AS proyek_selesai_tahun_ini,
                (SELECT COUNT(DISTINCT asset_id) FROM op_records
                    WHERE periode_tahun = EXTRACT(YEAR  FROM CURRENT_DATE)::int
                    AND   periode_bulan = EXTRACT(MONTH FROM CURRENT_DATE)::int
                    AND   status = 'selesai') AS aset_op_bulan_ini,
                (SELECT COUNT(*) FROM documents WHERE deleted_at IS NULL)                           AS total_dokumen,
                (SELECT COUNT(*) FROM documents WHERE tgl_kedaluwarsa < CURRENT_DATE AND deleted_at IS NULL) AS dokumen_kadaluwarsa,
                (SELECT COUNT(*) FROM loans     WHERE status IN ('requested','borrowed'))           AS peminjaman_aktif,
                (SELECT COUNT(*) FROM users     WHERE status = 'pending' AND deleted_at IS NULL)    AS user_pending,
                NOW() AS last_refreshed
            WITH DATA
        ");

        // Unique index untuk CONCURRENTLY refresh
        DB::statement("
            CREATE UNIQUE INDEX idx_mv_dashboard_stats
            ON mv_dashboard_stats ((last_refreshed))
        ");

        // Fungsi refresh
        DB::statement("
            CREATE OR REPLACE FUNCTION refresh_mv_dashboard_stats()
            RETURNS void AS \$\$
            BEGIN
                REFRESH MATERIALIZED VIEW CONCURRENTLY mv_dashboard_stats;
            END;
            \$\$ LANGUAGE plpgsql;
        ");
    }

    public function down(): void
    {
        DB::statement('DROP FUNCTION  IF EXISTS refresh_mv_dashboard_stats()');
        DB::statement('DROP MATERIALIZED VIEW IF EXISTS mv_dashboard_stats');
        DB::statement('DROP VIEW IF EXISTS v_project_dashboard');
        DB::statement('DROP VIEW IF EXISTS v_document_checklist');
        DB::statement('DROP VIEW IF EXISTS v_op_summary_tahunan');
        DB::statement('DROP VIEW IF EXISTS v_asset_summary');
        DB::statement('DROP FUNCTION IF EXISTS fn_rci_to_kondisi(decimal)');
        DB::statement('DROP FUNCTION IF EXISTS fn_asset_remaining_life(smallint,smallint)');
        DB::statement('DROP FUNCTION IF EXISTS fn_asset_age(smallint)');
    }
};
