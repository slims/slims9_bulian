<?php
/**
 * Keeps the web installer from staying reachable after installation.
 *
 * The installer directory must not remain on a running site: it can be used
 * to re-install or upgrade the application by anyone who can reach it. This
 * class is the single place that knows how to detect it, remove it and lock
 * it when removal is not possible.
 *
 * It has no dependency on the application bootstrap so the installer itself
 * (install/api.php) can use it with a plain require.
 */

namespace SLiMS;

use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Throwable;

class InstallerGuard
{
    /**
     * Application root directory with trailing separator
     */
    private static function base(): string
    {
        return dirname(__DIR__) . DIRECTORY_SEPARATOR;
    }

    /**
     * Full path of the installer directory
     */
    public static function path(): string
    {
        return self::base() . 'install';
    }

    /**
     * Full path of the lock file that disables the installer
     */
    public static function lockPath(): string
    {
        return self::base() . 'config' . DIRECTORY_SEPARATOR . 'installer.lock';
    }

    /**
     * Is the installer directory still on the server?
     */
    public static function exists(): bool
    {
        $path = self::path();
        if (is_link($path)) return true;
        if (!is_dir($path)) return false;
        // an empty folder holds no installer. It can be left behind when the
        // operating system refuses to drop the folder itself (e.g. it is open
        // in another program) although everything inside has been deleted
        $entries = @scandir($path);
        return $entries === false || count(array_diff($entries, ['.', '..'])) > 0;
    }

    /**
     * Should the super administrator be stopped until the installer is gone?
     * Development environment and command line are left alone: developers
     * work from a source checkout where the directory belongs.
     */
    public static function mustBlock(): bool
    {
        if (!self::exists()) return false;
        if (PHP_SAPI === 'cli') return false;
        if (defined('ENVIRONMENT') && ENVIRONMENT === 'development') return false;
        return true;
    }

    /**
     * Disable the installer without removing it. Used as a fallback when the
     * directory can not be deleted by the web server user.
     */
    public static function lock(): bool
    {
        if (self::isLocked()) return true;
        return @file_put_contents(self::lockPath(), 'Installer disabled at ' . date('c') . PHP_EOL, LOCK_EX) !== false;
    }

    public static function isLocked(): bool
    {
        return is_file(self::lockPath());
    }

    /**
     * Remove the installer directory and everything in it.
     *
     * @return array{ok: bool, reason: string}
     */
    public static function remove(): array
    {
        $path = self::path();

        if (!self::exists()) {
            @unlink(self::lockPath());
            return ['ok' => true, 'reason' => ''];
        }

        // never follow a link: the target may be anywhere on the server
        if (is_link($path)) {
            return ['ok' => false, 'reason' => 'The install folder is a symbolic link.'];
        }

        // the resolved path must be exactly <root>/install
        $real = realpath($path);
        $root = realpath(self::base());
        if ($real === false || $root === false || $real !== $root . DIRECTORY_SEPARATOR . 'install') {
            return ['ok' => false, 'reason' => 'The install folder path could not be verified.'];
        }

        $failed = '';
        try {
            $items = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($real, FilesystemIterator::SKIP_DOTS),
                RecursiveIteratorIterator::CHILD_FIRST
            );
            foreach ($items as $item) {
                $itemPath = $item->getPathname();
                // links inside are unlinked, not traversed (default iterator behaviour)
                $done = ($item->isDir() && !$item->isLink()) ? @rmdir($itemPath) : (@unlink($itemPath) || @rmdir($itemPath));
                if (!$done && $failed === '') $failed = $itemPath;
            }
            // release the directory handles before removing the folder itself
            unset($items, $item);
            if ($failed === '' && !@rmdir($real)) $failed = $real;
        } catch (Throwable $e) {
            $failed = $failed ?: $real;
        }

        clearstatcache();
        if (self::exists()) {
            return ['ok' => false, 'reason' => 'The web server is not allowed to delete: ' . ($failed ?: $real)];
        }

        // nothing left to lock
        @unlink(self::lockPath());
        return ['ok' => true, 'reason' => ''];
    }
}
