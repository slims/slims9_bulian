<?php
namespace SLiMS;
use Ramsey\Collection\AbstractCollection;

class Collection extends AbstractCollection {

    private string $type = '';

    public function __construct(string $type)
    {
        $this->type = $type;
    }

    public function get(string $name)
    {
        $filter = $this->filter(function($data) use($name) {
            if ($data->getName() === $name) return true;
        });

        return $filter->count() ? $filter->first() : null;
    }

    public function getType(): string
    {
        return $this->type;
    }
}