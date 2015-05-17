<?php

/**
 * This file contains the SoqlQuery class and the respective constants and default values that belong to SoQL.
 *
 * @copyright 2015 Vladimir Jimenez
 * @license   https://www.gnu.org/licenses/lgpl-2.1.html LGPL-2.1
 */

namespace allejo\Socrata;

use allejo\Socrata\Utilities\StringUtilities;

/**
 * An object provided for the creation and handling of SoQL queries in an object-oriented fashion.
 *
 * @package allejo\Socrata
 * @since   0.1.0
 */
class SoqlQuery
{
    const Delimiter = ',';
    const SelectKey = '$select';
    const WhereKey  = '$where';
    const OrderKey  = '$order';
    const GroupKey  = '$group';
    const LimitKey  = '$limit';
    const OffsetKey = '$offset';
    const SearchKey = '$q';

    const DefaultSelect         = '*';
    const DefaultOrderDirection = SoqlOrderDirection::ASC;
    const DefaultOrder          = ':id';
    const MaximumLimit          = 1000;

    private $queryElements;

    /**
     * Write a SoQL query by chaining functions. This object will handle encoding the final query in order for it to be
     * used properly as a URL. By default a SoqlQuery will select all columns (excluding socrata columns; e.g. :id) and
     * sort by `:id` in ascending order.
     *
     * @since 0.1.0
     */
    public function __construct ()
    {
        $this->queryElements[self::SelectKey] = self::DefaultSelect;
        $this->queryElements[self::OrderKey]  = self::DefaultOrder . urlencode(" ") . self::DefaultOrderDirection;
    }

    /**
     * Convert the current information into a URL encoded query that can be appended to the domain
     *
     * @since 0.1.0
     *
     * @return string The SoQL query ready to be appended to a URL
     */
    public function __tostring ()
    {
        $query = array();

        foreach ($this->queryElements as $soqlKey => $value)
        {
            $value = (is_array($value)) ? implode(self::Delimiter, $value) : $value;

            $query[] = sprintf("%s=%s", $soqlKey, $value);
        }

        return implode("&", $query);
    }

    /**
     * Select only specific columns in your Soql Query. When this function is given no parameters or is not used in a
     * query, the Soql Query will return all of the columns by default.
     *
     * ```
     * // These are all valid usages
     * $soqlQuery->select();
     * $soqlQuery->select("foo", "bar", "baz");
     * $soqlQuery->select(array("foo" => "foo_alias, "bar" => "bar_alias", "baz"));
     * ```
     *
     * @link    http://dev.socrata.com/docs/queries.html#the-select-parameter SoQL $select Parameter
     *
     * @param   array|mixed $columns   The columns to select from the dataset. The columns can be specified as an array
     *                                 of values or it can be specified as multiple parameters separated by commas.
     *
     * @since   0.1.0
     *
     * @return  $this       A SoqlQuery object that can continue to be chained
     */
    public function select ($columns = self::DefaultSelect)
    {
        if (func_num_args() == 1)
        {
            $this->queryElements[self::SelectKey] = (is_array($columns)) ? $this->formatAssociativeArray("%s AS %s", $columns) : array($columns);
        }
        else if (func_num_args() > 1)
        {
            $this->queryElements[self::SelectKey] = func_get_args();
        }

        return $this;
    }

    /**
     * Create a filter to selectively choose data based on certain parameters.
     *
     * Multiple calls to this function in a chain will overwrite the previous statement. To combine multiple where
     * clauses, use the supported SoQL operators; e.g. `magnitude > 3.0 AND source = 'pr'`
     *
     * @link    http://dev.socrata.com/docs/queries.html#the-where-parameter SoQL $where Parameter
     *
     * @param   string $statement  The `where` clause that will be used to filter data
     *
     * @since   0.1.0
     *
     * @return  $this  A SoqlQuery object that can continue to be changed
     */
    public function where ($statement)
    {
        $this->queryElements[self::WhereKey] = urlencode($statement);

        return $this;
    }

    /**
     * Determines the order and the column the results should be sorted by. This function may be used more than once in
     * a chain so duplicate entries in the first column will be sorted by the second specified column specified. If this
     * function is called more than once in a chain, the order does matter in which order() you call first.
     *
     * @link    http://dev.socrata.com/changelog/2015/04/27/new-higher-performance-apis.html New Higher Performance API
     * @link    http://dev.socrata.com/docs/queries.html#the-order-parameter SoQL $order Parameter
     *
     * @param   string  $column     The column(s) that determines how the results should be sorted. This information
     *                              can be given as an array of values, a single column, or a comma separated string.
     *                              In order to support sorting by multiple columns, you need to use the latest version
     *                              of the dataset API.
     * @param   string  $direction  The direction the results should be sorted in, either ascending or descending. The
     *                              {@link SoqlOrderDirection} class provides constants to use should these values ever change
     *                              in the future. The only accepted values are: `ASC` and `DESC`
     *
     * @see     SoqlOrderDirection  View convenience constants
     *
     * @since   0.1.0
     *
     * @return  $this   A SoqlQuery object that can continue to be changed
     */
    public function order ($column, $direction = self::DefaultOrderDirection)
    {
        $this->queryElements[self::OrderKey][] = $column . " " . $direction;

        return $this;
    }

    public function group ($column)
    {
        $this->queryElements[self::GroupKey][] = $column;

        return $this;
    }

    /**
     * Set the amount of results that can be retrieved from a dataset per query.
     *
     * The maximum value is 1000 based on API restrictions; larger values will be ignored.
     *
     * @link    http://dev.socrata.com/docs/queries.html#the-limit-parameter  SoQL $limit Parameter
     *
     * @param   int    $limit  The number of results the dataset should be limited to when returned
     *
     * @throws  \InvalidArgumentException  If the given argument is not an integer
     * @throws  \OutOfBoundsException      If the given argument is less than or equal to 0
     *
     * @since   0.1.0
     *
     * @return  $this          A SoqlQuery object that can continue to be changed
     */
    public function limit ($limit)
    {
        $this->handleInteger("limit", $limit, true);

        $this->queryElements[self::LimitKey] = min(self::MaximumLimit, $limit);

        return $this;
    }

    /**
     * The offset is the number of records into a dataset that you want to start, indexed at 0. For example, to retrieve
     * the “4th page” of records (records 151 - 200) where you are using limit() to page 50 records at a time, you’d ask
     * for an $offset of 150.
     *
     * @link    http://dev.socrata.com/docs/queries.html#the-offset-parameter  SoQL $offset Parameter
     *
     * @param   int    $offset  The number of results the dataset should be offset to when returned
     *
     * @throws  \InvalidArgumentException  If the given argument is not an integer
     * @throws  \OutOfBoundsException      If the given argument is less than 0
     *
     * @since   0.1.0
     *
     * @return  $this           A SoqlQuery object that can continue to be changed
     */
    public function offset ($offset)
    {
        $this->handleInteger("offset", $offset, false);

        $this->queryElements[self::OffsetKey] = $offset;

        return $this;
    }

    /**
     * Search the entire dataset for a specified string. Think of this as a search engine instead of performing a SQL
     * query.
     *
     * @param   string  $needle  The phrase to search for
     *
     * @since   0.1.0
     *
     * @return  $this            A SoqlQuery object that can continue to be changed
     */
    public function fullTextSearch ($needle)
    {
        $this->queryElements[self::SearchKey] = $needle;

        return $this;
    }

    /**
     * Create an array of values that have already been formatted and are ready to be converted into a comma separated
     * list that will be used as a parameter for selectors such was `$select`, `$order`, or `$group` in SoQL
     *
     * @param   string  $format  The format used in sprintf() for keys and values of an array to be formatted to
     * @param   array   $array   The array that will be formatted appropriately for usage within this class
     *
     * @since   0.1.0
     *
     * @return  array
     */
    private function formatAssociativeArray ($format, $array)
    {
        $formattedValues = [];

        foreach ($array as $key => $value)
        {
            $formattedValues[] = (is_string($key)) ? sprintf($format, trim($key), trim($value)) : $value;
        }

        return $formattedValues;
    }

    /**
     * Analyze a given value and ensure the value fits the criteria set by the Socrata API
     *
     * @param   string  $variable                 The literal name of this field
     * @param   int     $number                   The value to analyze
     * @param   bool    $disallowNegativeAndZero  When set to true, it will disallow numbers that are less than or equal
     *                                            to 0. When set to false, it will disallow numbers that are less than 0
     *
     * @since   0.1.0
     *
     * @throws  \InvalidArgumentException         If the given argument is not an integer
     * @throws  \OutOfBoundsException             If the given argument is less than 0
     */
    private function handleInteger ($variable, $number, $disallowNegativeAndZero)
    {
        if (!is_integer($number))
        {
            throw new \InvalidArgumentException(sprintf("The %s must be an integer", $variable));
        }

        if (($disallowNegativeAndZero && $number <= 0) || (!$disallowNegativeAndZero && $number < 0))
        {
            $comparison = ($disallowNegativeAndZero) ? "less than or equal to" : "less than";
            $message = sprintf("The %s cannot be %s 0.", $variable, $comparison);

            throw new \OutOfBoundsException($message, 1);
        }
    }
}
