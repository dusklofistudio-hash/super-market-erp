<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = ['key', 'value', 'type', 'group'];

    public static function get(string $key, mixed $default = null): mixed
    {
        $row = Cache::rememberForever("setting:$key", fn () => static::query()->where('key', $key)->first());
        if (! $row) {
            return $default;
        }

        return match ($row->type) {
            'int', 'integer' => (int) $row->value,
            'bool', 'boolean' => filter_var($row->value, FILTER_VALIDATE_BOOLEAN),
            'json' => $row->value ? json_decode($row->value, true) : $default,
            default => $row->value,
        };
    }

    public static function put(string $key, mixed $value, string $type = 'string', string $group = 'general'): self
    {
        $stored = match ($type) {
            'json' => is_string($value) ? $value : json_encode($value),
            'bool', 'boolean' => $value ? '1' : '0',
            default => is_null($value) ? null : (string) $value,
        };
        $row = static::query()->updateOrCreate(['key' => $key], [
            'value' => $stored,
            'type' => $type,
            'group' => $group,
        ]);
        Cache::forget("setting:$key");

        return $row;
    }
}
