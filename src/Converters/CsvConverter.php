<?php

/**
 * This file contains the CSV converter used by PhpSoda to officially support CSV as a data format
 *
 * @copyright 2015 Vladimir Jimenez
 * @license   https://github.com/allejo/PhpSoda/blob/master/LICENSE.md MIT
 */

namespace allejo\Socrata\Converters;

/**
 * The class used to officially support CSV as a data format that can be submitted to Socrata
 *
 * This class extends the Converter base class which implements the abstract `toJson()` method that PhpSoda calls when
 * sending data to Socrata.
 *
 * @package allejo\Socrata\Converters
 * @see     allejo\Socrata\Converters\Converter
 * @since   0.1.0
 */
class CsvConverter extends Converter
{
    /**
     * {@inheritdoc}
     *
     * @link   http://steindom.com/articles/shortest-php-code-convert-csv-associative-array
     */
    public function toJson ()
    {
        $rows    = array_map("str_getcsv", explode("\n", trim($this->data)));
        $columns = array_shift($rows);
        $csv     = array();

        foreach ($rows as $row)
        {
            $csv[] = array_combine($columns, $row);
        }

        return json_encode($csv);
    }
}
