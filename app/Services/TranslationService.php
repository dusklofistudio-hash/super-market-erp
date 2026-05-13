<?php

namespace App\Services;

use App\Models\Language;
use App\Models\Translation;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;

/**
 * Loads translations for the front-end. Sources, in priority order:
 *   1) `translations` table (admin-editable overrides)
 *   2) `lang/{locale}/messages.php` static file (project defaults)
 *
 * The merged map is cached per-locale; cache is busted whenever an admin
 * edits a translation row.
 */
class TranslationService
{
    public function all(string $locale): array
    {
        return Cache::rememberForever("translations:$locale", function () use ($locale) {
            $base = $this->flatten($this->loadFile($locale));
            $overrides = Translation::query()
                ->where('language_code', $locale)
                ->pluck('value', 'key')
                ->all();

            return array_merge($base, $overrides);
        });
    }

    public function bust(string $locale): void
    {
        Cache::forget("translations:$locale");
    }

    public function bustAll(): void
    {
        foreach (Language::query()->pluck('code') as $code) {
            $this->bust($code);
        }
    }

    protected function loadFile(string $locale): array
    {
        $path = lang_path("$locale/messages.php");
        if (! File::exists($path)) {
            return [];
        }
        $data = require $path;

        return is_array($data) ? $data : [];
    }

    /** Flatten a nested array using dot notation. */
    protected function flatten(array $arr, string $prefix = ''): array
    {
        $out = [];
        foreach ($arr as $k => $v) {
            $key = $prefix === '' ? $k : "$prefix.$k";
            if (is_array($v)) {
                $out = array_merge($out, $this->flatten($v, $key));
            } else {
                $out[$key] = $v;
            }
        }

        return $out;
    }
}
