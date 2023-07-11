<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2023-06-23 11:21:56
 * @modify date 2023-06-28 16:38:47
 * @license GPLv3
 * @desc [description]
 */

namespace SLiMS\Csv;

use Exception;
use Ramsey\Collection\AbstractCollection;

class Writer extends AbstractCollection
{
    /**
     * @var string
     */
    private string $contents = '';

    /**
     * Concating contents based on row generated
     *
     * @return Writer
     */
    public function create(): Writer
    {
        foreach ($this as $row) {
            $this->contents .= $row->generate();       
        }
        return $this;
    }

    /**
     * Contents as string
     *
     * @return void
     */
    public function asText():void
    {
        $this->create();
        echo $this->contents;
    }

    /**
     * Stream contents as file
     *
     * @param string $filename
     * @return void
     */
    public function download(string $filename): void
    {
        $this->create();
        ob_start();
        header('Content-type: text/plain; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . basename($filename) . '.csv"');
        echo $this->contents;
        exit(ob_get_clean());
    }

    public function getType(): string
    {
        return Row::class;
    }
}