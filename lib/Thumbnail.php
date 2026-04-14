<?php
/**
 * Thumbnail URL helper
 *
 * Provides a single point of truth for generating thumbnail URLs.
 * If the static cache file already exists on disk, returns the direct
 * static URL so the browser never needs to hit PHP at all.
 * Falls back to createthumb.php only when the cache is cold.
 *
 * Usage:
 *   use SLiMS\Thumbnail;
 *   echo Thumbnail::url('images/docs/01838.png', 120, 65);
 *   // → https://example.com/images/cache/_slims_img_cache_120_x_65_01838.png
 *   //   or /lib/minigalnano/createthumb.php?filename=images/docs/01838.png&width=120&height=65
 *
 * @author Sandikodev <androxoss@hotmail.com>
 */

namespace SLiMS;

class Thumbnail
{
    /**
     * Cache filename prefix — must match Thumb::__construct() default.
     */
    private const CACHE_PREFIX = '_slims_img_cache_';

    /**
     * Return the best available URL for a thumbnail.
     *
     * @param  string   $filename  Relative path, e.g. "images/docs/01838.png"
     * @param  int      $width
     * @param  int      $height    0 = auto (proportional)
     * @return string
     */
    public static function url(string $filename, int $width = 120, int $height = 0): string
    {
        $basename  = basename($filename);
        $cacheFile = IMGBS . 'cache/' . self::CACHE_PREFIX . $width . '_x_' . $height . '_' . $basename;

        if (file_exists($cacheFile)) {
            return SWB . 'images/cache/' . self::CACHE_PREFIX . $width . '_x_' . $height . '_' . $basename;
        }

        $query = 'filename=' . urlencode($filename) . '&width=' . $width;
        if ($height > 0) {
            $query .= '&height=' . $height;
        }

        return SWB . 'lib/minigalnano/createthumb.php?' . $query;
    }

    /**
     * Convenience wrapper — returns a full <img> tag.
     *
     * @param  string $filename
     * @param  int    $width
     * @param  int    $height
     * @param  array  $attrs     Extra HTML attributes, e.g. ['class' => 'img-fluid', 'alt' => '...']
     * @return string
     */
    public static function img(string $filename, int $width = 120, int $height = 0, array $attrs = []): string
    {
        $src = htmlspecialchars(self::url($filename, $width, $height), ENT_QUOTES);

        $defaults = ['loading' => 'lazy', 'alt' => ''];
        $attrs    = array_merge($defaults, $attrs);

        $attrStr = '';
        foreach ($attrs as $k => $v) {
            $attrStr .= ' ' . htmlspecialchars($k, ENT_QUOTES) . '="' . htmlspecialchars($v, ENT_QUOTES) . '"';
        }

        return '<img src="' . $src . '" width="' . $width . '"' . ($height > 0 ? ' height="' . $height . '"' : '') . $attrStr . '>';
    }
}
