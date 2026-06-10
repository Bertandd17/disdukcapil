<?php

namespace App\Support;

class AssetVersion
{
    public function url(string $path): string
    {
        $fullPath = public_path($path);
        $version = @filemtime($fullPath);

        if ($version === false) {
            $version = time();
        }

        return asset($path).'?v='.$version;
    }
}
