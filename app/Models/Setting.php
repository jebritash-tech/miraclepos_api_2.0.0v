<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = ['key', 'value', 'type', 'group', 'label', 'description'];

    protected static string $cacheKey = 'settings.all';
    protected static int $cacheTtl = 300; // 5 دقائق

    /* ============================================================
       Helpers
       ============================================================ */
    public static function get(string $key, mixed $default = null): mixed
    {
        $all = static::all_cached();
        if (!isset($all[$key])) return $default;

        return static::cast($all[$key]['value'], $all[$key]['type']);
    }

    public static function set(string $key, mixed $value): bool
    {
        $setting = static::where('key', $key)->first();
        if (!$setting) return false;

        $setting->value = is_array($value) || is_object($value)
            ? json_encode($value, JSON_UNESCAPED_UNICODE)
            : (string) $value;
        $setting->save();

        static::clearCache();
        return true;
    }

    public static function setMany(array $pairs): int
    {
        $updated = 0;
        foreach ($pairs as $key => $value) {
            if (static::set($key, $value)) $updated++;
        }
        return $updated;
    }

    public static function all_cached(): array
    {
        return Cache::remember(static::$cacheKey, static::$cacheTtl, function () {
            return static::query()
                ->get()
                ->keyBy('key')
                ->map(fn($s) => [
                    'value' => $s->value,
                    'type'  => $s->type,
                    'group' => $s->group,
                    'label' => $s->label,
                ])
                ->toArray();
        });
    }

    public static function byGroup(): array
    {
        return static::query()
            ->orderBy('id')
            ->get()
            ->groupBy('group')
            ->map(fn($items) => $items->map(fn($s) => [
                'key'         => $s->key,
                'value'       => static::cast($s->value, $s->type),
                'type'        => $s->type,
                'label'       => $s->label,
                'description' => $s->description,
            ])->values()->toArray())
            ->toArray();
    }

    public static function clearCache(): void
    {
        Cache::forget(static::$cacheKey);
    }

    protected static function cast(?string $value, string $type): mixed
    {
        return match ($type) {
            'boolean' => (bool) $value,
            'integer' => (int) $value,
            'json'    => json_decode($value ?: '{}', true),
            default   => $value,
        };
    }
}