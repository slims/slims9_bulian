<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2023-06-29 10:10:08
 * @modify date 2023-07-09 16:02:36
 * @license GPLv3
 * @desc [description]
 */

namespace SLiMS\Csv;

use Closure;
use Exception;

class Reader
{
    private int $limit = 0;
    private array $standert;
    private $resource;
    private array $fields = [];

    public function __construct(array $standert = [])
    {
        $this->standart = $standert?:config('csv');
    }

    public function readFromStream($resource)
    {
        $this->resource = $resource;
        return $this;
    }

    public function setLimit(int $limit)
    {
        $this->limit = $limit;
    }

    public function getTotalLine()
    {
        // get total line
        $lineNumber = 0;
        while (!feof($this->resource)) {
            $line = fgets($this->resource);
            if (empty($line)) continue;
            $lineNumber++;
            ob_flush();
            flush();
        }
        // close file handle
        fclose($this->resource);
        
        return $lineNumber - (isset($_SESSION['csv']['header']) ? 1 : 0);
    }
    
    public function each(Closure $formatter, Closure|string $processor = '', int $length = 102400)
    {
        $row = 1;
        $fields = [];
        
        while (!feof($this->resource)) {
            // break it if limit exceed
            if ($this->limit > 0 && ($this->limit + 1) === $row) break;

            // Get csv data
            $csv = fgetcsv($this->resource, $length, $this->standart['separator'], $this->standart['enclosed_with']);

            // data must be an array
            if (!is_array($csv)) continue;

            // bypass some header
            if (in_array(trim($csv[0]), ['item_code','title','member_id'])) continue;

            if (isset($csv[12])) $csv[12] = strlen($csv[12]) > 50 ? '<div style="height: 250px; overflow-y: auto;">' . $csv[12] . '</div>' : strlen($csv[12]);

            
            $row++;
            
            if (is_callable($formatter)) {
                foreach ($csv as $index => $data) {
                    $formatter($csv, $row, $index, $data);
                }
            }

            $this->fields[] = $csv;
            if (is_callable($processor)) $processor($this, $row);
        }
        return $this;
    }

    public function getFields()
    {
        return $this->fields;
    }
}