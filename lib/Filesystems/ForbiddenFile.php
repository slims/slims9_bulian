<?php
/**
 * @desc Hard upload denylist for server-executable files.
 *
 * Upload whitelists (allowed_file_att, allowed_images) are configurable, so
 * they must never be the only barrier against uploading code the web server
 * may run. This list is fixed and checked regardless of configuration.
 */

namespace SLiMS\Filesystems;

final class ForbiddenFile
{
    public const EXTENSIONS = [
        'php', 'php3', 'php4', 'php5', 'php7', 'php8', 'pht', 'phtml', 'phps', 'phar',
        'shtml', 'cgi', 'pl', 'py', 'asp', 'aspx', 'jsp',
        'htaccess', 'htpasswd', 'ini',
    ];

    /**
     * True when any extension segment of the filename is executable,
     * so "shell.php.jpg", "shell.PHP" and ".htaccess" are all rejected.
     */
    public static function matches(string $filename): bool
    {
        $segments = explode('.', strtolower(basename($filename)));
        array_shift($segments);

        foreach ($segments as $segment) {
            if (in_array(trim($segment), self::EXTENSIONS, true)) {
                return true;
            }
        }

        return false;
    }
}
