<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class Uploads
{
    public static function store(?UploadedFile $file, string $folder): ?string
    {
        if (! $file) {
            return null;
        }
        $filename = Str::random(20).'.'.$file->getClientOriginalExtension();

        return $file->storeAs("uploads/$folder", $filename, 'public');
    }
}
