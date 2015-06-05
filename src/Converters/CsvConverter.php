<?php

namespace allejo\Socrata\Converters;

class CsvConverter extends Converter
{
    /**
     * Convert the data that was given to this object into JSON.
     *
     * @link   http://steindom.com/articles/shortest-php-code-convert-csv-associative-array
     *
     * @return string A JSON encoded string
     */
    public function toJson ()
    {
        $rows = array_map("str_getcsv", explode("\n", trim($this->data)));
        $columns = array_shift($rows);
        $csv = array();

        foreach ($rows as $row)
        {
            $csv[] = array_combine($columns, $row);
        }

        return json_encode($csv);
    }
}
