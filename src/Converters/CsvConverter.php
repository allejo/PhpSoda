<?php

namespace allejo\Socrata\Converters;

class CsvConverter extends Converter
{
    private $data;

    public function __construct ($csv)
    {
        $this->data = $csv;
    }

    public function toJson ()
    {
        $array = array_map("str_getcsv", explode("\n", $this->data));

        return json_encode($array);
    }
}