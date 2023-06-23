<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2023-06-23 11:21:56
 * @modify date 2023-06-23 13:22:22
 * @license GPLv3
 * @desc [description]
 */

namespace SLiMS;

use Exception;

class Csv
{
    private string $contents = '';

    public function create(array $collection, array $options = [])
    {
        if (!$collection) throw new Exception("Collection is not allowed!");

        $csv = config('csv');

        if (isset($options['with_header']) || ($onlyHeader = isset($options['only_header'])))
        {
            $header = array_keys($collection[0]);
            $imploding = [$csv['enclosed_with'] . $csv['separator'] . $csv['enclosed_with'], $header];
            $this->contents .= $csv['enclosed_with'] . implode(...$imploding) . $csv['enclosed_with'] . $csv['record_separator']['newline'];
            if ($onlyHeader) return;
        }

        foreach ($collection as $data) {
            $imploding = [$csv['enclosed_with'] . $csv['separator'] . $csv['enclosed_with'], array_values($data)];
            $this->contents .= $csv['enclosed_with'] . implode(...$imploding) . $csv['enclosed_with'] . $csv['record_separator']['newline'];
        }    
    }

    public function asText()
    {
        echo $this->contents;
    }

    public function asStream(string $filename)
    {
        header('Content-type: text/plain');
        header('Content-Disposition: attachment; filename="' . basename($filename) . '.csv"');
        exit(urldecode($this->contents));
    }
}