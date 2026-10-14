<?php

namespace App\Helpers;

use Illuminate\Support\Str;

class Helper
{
    // ! File or Image Upload
    public static function fileUpload($file, string $folder, string $name): ?string
    {
        if (! $file->isValid()) {
            return null;
        }

        $imageName = Str::slug($name).'.'.$file->extension();
        $path = public_path('uploads/'.$folder);
        if (! file_exists($path)) {
            mkdir($path, 0755, true);
        }
        $file->move($path, $imageName);

        return 'uploads/'.$folder.'/'.$imageName;
    }

    // ! File or Image Delete
    public static function fileDelete(string $path): void
    {
        $fullPath = file_exists($path) ? $path : public_path($path);
        if (file_exists($fullPath) && is_file($fullPath)) {
            @unlink($fullPath);
        }
    }

    // ! Generate username
    public static function makeUsername($model, string $title): string
    {
        $username = Str::slug($title);
        while ($model::where('username', $username)->exists()) {
            $randomString = Str::random(5);
            $username = Str::slug($title).'-'.$randomString;
        }

        return $username;
    }

    // ! Generate Slug
    public static function makeSlug($model, string $title): string
    {
        $slug = Str::slug($title);
        while ($model::where('slug', $slug)->exists()) {
            $randomString = Str::random(5);
            $slug = Str::slug($title).'-'.$randomString;
        }

        return $slug;
    }
}
