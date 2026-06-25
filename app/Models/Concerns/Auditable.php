<?php

namespace App\Models\Concerns;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;

/**
 * Trait Auditable
 *
 * Menulis ke tabel audit_logs (immutable) setiap ada perubahan model.
 * Lebih ringan dari Spatie Activity Log karena langsung ke tabel sendiri.
 *
 * Cara pakai di Model:
 *   use Auditable;
 *   protected array $auditInclude = ['nama', 'status', 'kondisi']; // kolom yang diaudit
 */
trait Auditable
{
    public static function bootAuditable(): void
    {
        static::created(function ($model) {
            static::writeAuditLog('create', $model, [], $model->toArray());
        });

        static::updated(function ($model) {
            $dirty = $model->getDirty();
            if (empty($dirty)) return;

            // Filter hanya kolom yang ada di $auditInclude jika didefinisikan
            if (!empty($model->auditInclude)) {
                $dirty = array_intersect_key($dirty, array_flip($model->auditInclude));
            }

            if (empty($dirty)) return;

            $old = array_intersect_key($model->getOriginal(), $dirty);
            static::writeAuditLog('update', $model, $old, $dirty);
        });

        static::deleted(function ($model) {
            static::writeAuditLog('delete', $model, ['id' => $model->getKey()], []);
        });
    }

    protected static function writeAuditLog(
        string $action,
        $model,
        array $oldValues,
        array $newValues
    ): void {
        try {
            $user = auth()->user();

            DB::table('audit_logs')->insert([
                'user_id'       => $user?->id,
                'user_name'     => $user?->name,
                'user_email'    => $user?->email,
                'action'        => $action,
                'entity_type'   => class_basename($model),
                'entity_id'     => (string) $model->getKey(),
                'entity_label'  => method_exists($model, 'auditLabel')
                                    ? $model->auditLabel()
                                    : (string) $model->getKey(),
                'old_values'    => empty($oldValues) ? null : json_encode($oldValues),
                'new_values'    => empty($newValues) ? null : json_encode($newValues),
                'ip_address'    => Request::ip(),
                'user_agent'    => Request::userAgent(),
                'url'           => Request::fullUrl(),
                'method'        => Request::method(),
                'unit_kerja_id' => $user?->unit_kerja_id,
                'created_at'    => now(),
            ]);
        } catch (\Throwable $e) {
            // Jangan sampai gagal audit membuat operasi utama gagal
            logger()->error('Audit log gagal: ' . $e->getMessage());
        }
    }
}
