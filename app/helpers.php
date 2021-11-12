<?php

if (! function_exists('localizedImageAsset')) {
    /**
     * Generate an asset path for a localized image.
     *
     * @param string    $path
     * @param bool|null $secure
     *
     * @return string
     */
    function localizedImageAsset(string $path, bool $secure = null): string
    {
        return \App\Models\Locale::imageAsset($path, $secure);
    }
}
