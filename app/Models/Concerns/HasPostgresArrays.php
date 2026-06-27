<?php

namespace App\Models\Concerns;

/**
 * Trait untuk handle kolom PostgreSQL text[] di Eloquent.
 *
 * PHP cast 'array' men-serialize ke JSON ["a","b"] yang tidak dikenali
 * PostgreSQL text[]. Trait ini menyediakan helper untuk konversi
 * antara PHP array dan format PostgreSQL {a,b}.
 *
 * Cara pakai:
 *   protected array $pgArrayColumns = ['tags', 'foto_paths'];
 */
trait HasPostgresArrays
{
    /**
     * Boot trait — intercept setAttribute untuk kolom text[]
     */
    public static function bootHasPostgresArrays(): void
    {
        // Override setAttribute saat creating/updating
        static::creating(fn($m) => $m->convertPgArraysForSave());
        static::updating(fn($m) => $m->convertPgArraysForSave());
    }

    /**
     * Konversi semua kolom pg array ke format PostgreSQL sebelum save
     */
    protected function convertPgArraysForSave(): void
    {
        foreach ($this->pgArrayColumns ?? [] as $col) {
            if (array_key_exists($col, $this->attributes)) {
                $val = $this->attributes[$col];
                if (is_array($val)) {
                    $this->attributes[$col] = $this->phpArrayToPg($val);
                } elseif (is_string($val) && str_starts_with($val, '[')) {
                    // Sudah di-encode sebagai JSON array - konversi
                    $decoded = json_decode($val, true);
                    if (is_array($decoded)) {
                        $this->attributes[$col] = $this->phpArrayToPg($decoded);
                    }
                }
            }
        }
    }

    /**
     * PHP array → PostgreSQL text[] literal: {"a","b","c"}
     */
    protected function phpArrayToPg(array $arr): ?string
    {
        $arr = array_values(array_filter(array_map('trim', $arr)));
        if (empty($arr)) return null;
        $escaped = array_map(
            fn($v) => '"' . str_replace(['"', '\\'], ['\\"', '\\\\'], $v) . '"',
            $arr
        );
        return '{' . implode(',', $escaped) . '}';
    }

    /**
     * PostgreSQL text[] literal → PHP array
     * Handle format: {"a","b"} atau {a,b}
     */
    protected function pgToPhpArray(?string $value): array
    {
        if (is_null($value) || $value === '{}') return [];
        $value = trim($value, '{}');
        if (empty($value)) return [];
        return array_map(
            fn($v) => trim($v, '"'),
            str_getcsv($value)
        );
    }

    /**
     * Override getAttribute untuk decode text[] ke PHP array
     */
    public function getAttribute($key)
    {
        $value = parent::getAttribute($key);

        if (in_array($key, $this->pgArrayColumns ?? []) && is_string($value)) {
            return $this->pgToPhpArray($value);
        }

        return $value;
    }
}
