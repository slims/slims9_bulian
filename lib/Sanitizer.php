<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2023-03-01 20:22:50
 * @modify date 2023-04-14 07:22:00
 * @license GPLv3
 * @desc [description]
 */

namespace SLiMS;

use Utility;

class Sanitizer 
{
    /**
     * Maximum array nesting level that will be traversed.
     * PHP itself rejects request data deeper than
     * max_input_nesting_level (64 by default), so this is only
     * a safety net against unbounded recursion.
     */
    const MAX_DEPTH = 64;

    private $variableToClean = [];
    
    public static function fromGlobal(array $variableToClean = []): self
    {
        return new self($variableToClean);
    }

    public function __construct(array $variableToClean = [])
    {
        $this->variableToClean = $variableToClean;
    }

    private function setNewValue(string $type, string $key, $value)
    {
        switch ($type) {
            case 'get':
                $_GET[$key] = $value;
                break;

            case 'post':
                $_POST[$key] = $value;
                break;
            
            case 'server':
                $_SERVER[$key] = $value;
                break;

            case 'cookie':
                $_COOKIE[$key] = $value;
                break;

            case 'session':
                $_SESSION[$key] = $value;
                break;
        }
    }

    /**
     * Read the current (live) value of a global variable member,
     * so nested data is sanitized from the real superglobal
     * instead of the snapshot taken on construction.
     *
     * @param string $type
     * @param string $key
     * @return mixed
     */
    private function getCurrentValue(string $type, string $key)
    {
        switch ($type) {
            case 'get':
                return $_GET[$key] ?? null;

            case 'post':
                return $_POST[$key] ?? null;

            case 'server':
                return $_SERVER[$key] ?? null;

            case 'cookie':
                return $_COOKIE[$key] ?? null;

            case 'session':
                return $_SESSION[$key] ?? null;
        }

        return null;
    }

    /**
     * Walk an array and apply $callback to every string found,
     * no matter how deep it is nested. Non string scalars, objects
     * and null are left untouched, excepted keys are skipped on
     * every level.
     *
     * @param array $input
     * @param callable $callback
     * @param array $exception
     * @param int $depth
     * @return array
     */
    private function sanitizeRecursive(array $input, callable $callback, array $exception = [], int $depth = 1): array
    {
        $sanitized = [];

        foreach ($input as $key => $value) {
            // keep excepted field as is, on any nesting level
            if (in_array($key, $exception)) {
                $sanitized[$key] = $value;
                continue;
            }

            if (is_array($value)) {
                // stop traversing on unreasonably deep data
                $sanitized[$key] = $depth >= self::MAX_DEPTH ? [] : $this->sanitizeRecursive($value, $callback, $exception, $depth + 1);
                continue;
            }

            $sanitized[$key] = is_string($value) ? $callback($value) : $value;
        }

        return $sanitized;
    }

    /**
     * Scalar filter equivalent of utility::filterData, usable on a
     * value that is already in hand. Needed because filterData only
     * accept a key of a global variable, so it can not reach
     * nested array member.
     *
     * @param string $value
     * @param array $filter escape_sql, trim, strip_tag
     * @return string
     */
    private function filterValue(string $value, array $filter): string
    {
        global $dbs;

        $escapeSql = $filter[0] ?? true;
        $trim = $filter[1] ?? true;
        $stripHtml = $filter[2] ?? false;

        // trim whitespace on string
        if ($trim) $value = trim($value);

        // strip html
        if ($stripHtml) $value = strip_tags($value);

        // escape SQL string
        if ($escapeSql && isset($dbs) && is_object($dbs) && method_exists($dbs, 'escape_string')) {
            $value = $dbs->escape_string($value);
        }

        return $value;
    }

    public function quoteFree(array $exception = [])
    {
        $removeQuote = fn($value) => str_replace(['\'','"','`'],'',$value);

        foreach ($this->variableToClean as $type => $globalVariable) {
            foreach ($globalVariable as $key => $value) {
                if (in_array($key, $exception)) continue;

                // sanitize array based input recursively to
                // avoid filtering bypass
                if (is_array($value)) {
                    $current = $this->getCurrentValue($type, (string)$key);
                    if (!is_array($current)) continue;
                    $this->setNewValue($type, (string)$key, $this->sanitizeRecursive($current, $removeQuote, $exception));
                    continue;
                }

                $this->setNewValue($type, (string)$key, $removeQuote($value));
            }
        }
    }

    public function cleanUp(array $exception = [], array $filter = [true, true, true] /* escape_sql, trim, strip_tag */): void
    {
        $filterValue = fn($value) => $this->filterValue($value, $filter);

        foreach ($this->variableToClean as $type => $globalVariable) {
            foreach ($globalVariable as $key => $value) {
                if (in_array($key, $exception)) continue;

                // sanitize array based input recursively to
                // avoid filtering bypass
                if (is_array($value)) {
                    $current = $this->getCurrentValue($type, (string)$key);
                    if (!is_array($current)) continue;
                    $this->setNewValue($type, (string)$key, $this->sanitizeRecursive($current, $filterValue, $exception));
                    continue;
                }

                $this->setNewValue($type, (string)$key, utility::filterData($key, $type, ...$filter));
            }
        }
    }
}
