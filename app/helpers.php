<?php

if (! function_exists('asset_v')) {
    /**
     * URL asset publik dengan cache-bust berdasarkan filemtime.
     *
     * @param  string  $path  Path relatif dari folder public/, contoh: css/app.css
     */
    function asset_v(string $path): string
    {
        static $versions = [];

        $normalized = ltrim(str_replace('\\', '/', $path), '/');

        if (! array_key_exists($normalized, $versions)) {
            $fullPath = public_path($normalized);
            $versions[$normalized] = is_file($fullPath)
                ? (string) filemtime($fullPath)
                : (string) config('app.version', '1');
        }

        return asset($normalized).'?v='.$versions[$normalized];
    }
}
