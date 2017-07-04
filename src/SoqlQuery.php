<?php

/**
 * This file contains the SoqlQuery class and the respective constants and default values that belong to SoQL.
 *
 * @copyright 2015 Vladimir Jimenez
 * @license   https://github.com/allejo/PhpSoda/blob/master/LICENSE.md MIT
 */

namespace allejo\Socrata;

/**
 * An object provided for the creation and handling of SoQL queries in an object-oriented fashion.
 *
 * @package allejo\Socrata
 * @since   0.1.0
 */
class SoqlQuery
{
    /**
     * The default delimiter used to separate multiple values.
     */
    const DELIMITER = ',';

    /**
     * The SELECT clause in SoQL
     */
    const SELECT_KEY = '$select';

    /**
     * The WHERE clause in SoQL
     */
    const WHERE_KEY = '$where';

    /**
     * The ORDER clause in SoQL
     */
    const ORDER_KEY = '$order';

    /**
     * The GROUP clause in SoQL
     */
    const GROUP_KEY = '$group';

    /**
     * The LIMIT clause in SoQL
     */
    const LIMIT_KEY = '$limit';

    /**
     * The HAVING clause in SoQL
     */
    const HAVING_KEY = '$having';

    /**
     * The OFFSET clause in SoQL
     */
    const OFFSET_KEY = '$offset';

    /**
     * The SEARCH clause in SoQL
     */
    const SEARCH_KEY = '$q';

    /**
     * The default value for the `$select` clause in a SoQL query. By default, select all the columns
     */
    const DEFAULT_SELECT = '*';

    /**
     * The default order for the `$order` clause in a SoQL query. By default, order in ascending order
     */
    const DEFAULT_ORDER_DIRECTION = SoqlOrderDirection::ASC;

    /**
     * This array contains all of the parts to a SoqlQuery being converted into a URL where the key of an element is the
     * SoQL clause (e.g. $select) and the value of an element is the value to the SoQL clause (e.g. *).
     *
     * @var string[]
     */
    private $queryElements;

    /**
     * Write a SoQL query by chaining functions. This object will handle encoding the final query in order for it to be
     * used properly as a URL. By default a SoqlQuery will select all columns (excluding socrata columns; e.g. :id) and
     * sort by `:id` in ascending order.
     *
     * @since 0.1.0
     */
    public function __construct () {}

    /**
     * Convert the current information into a URL encoded query that can be appended to the domain
     *
     * @since 0.1.0
     *
     * @return string The SoQL query ready to be appended to a URL
     */
    public function __tostring ()
    {
        if (is_null($this->queryElements))
        {
            return "";
        }

        $query = [];

        foreach ($this->queryElements as $soqlKey => $value)
        {
            $value = (is_array($value)) ? implode(self::DELIMITER, $value) : $value;

            $query[] = sprintf("%s=%s", $soqlKey, $value);
        }

        return implode("&", $query);
    }

    /**
     * Select only specific columns in your Soql Query. When this function is given no parameters or is not used in a
     * query, the Soql Query will return all of the columns by default.
     *
     * ```php
     * // These are all valid usages
     * $soqlQuery->select();
     * $soqlQuery->select("foo", "bar", "baz");
     * $soqlQuery->select(array("foo" => "foo_alias", "bar" => "bar_alias", "baz"));
     * ```
     *
     * @link    https://dev.socrata.com/docs/queries/select.html SoQL $select Parameter
     *
     * @param   array|mixed $columns   The columns to select from the dataset. The columns can be specified as an array
     *                                 of values or it can be specified as multiple parameters separated by commas.
     *
     * @since   0.1.0
     *
     * @return  $this       A SoqlQuery object that can continue to be chained
     */
    public function select ($columns = self::DEFAULT_SELECT)
    {
        if (func_num_args() == 1)
        {
            $this->queryElements[self::SELECT_KEY] = (is_array($columns)) ? $this->formatAssociativeArray("%s AS %s", $columns) : array($columns);
        }
        else if (func_num_args() > 1)
        {
            $this->queryElements[self::SELECT_KEY] = func_get_args();
        }

        return $this;
    }

    /**
     * Create an array of values that have already been formatted and are ready to be converted into a comma separated
     * list that will be used as a parameter for selectors such was `$select`, `$order`, or `$group` in SoQL
     *
     * @param   string $format The format used in sprintf() for keys and values of an array to be formatted to
     * @param   array  $array  The array that will be formatted appropriately for usage within this class
     *
     * @since   0.1.0
     *
     * @return  array
     */
    private function formatAssociativeArray ($format, $array)
    {
        $formattedValues = array();

        foreach ($array as $key => $value)
        {
            if(is_string($key) && !is_null($value))
            {
                $formattedValues[] = rawurlencode(sprintf($format, trim($key), trim($value)));
            }
            else
            {
                $formattedValues[] = is_string($key) ? $key : $value;
            }
        }

        return $formattedValues;
    }

    /**
     * Create a filter to selectively choose data based on certain parameters.
     *
     * Multiple calls to this function in a chain will overwrite the previous statement. To combine multiple where
     * clauses, use the supported SoQL operators; e.g. `magnitude > 3.0 AND source = 'pr'`
     *
     * @link    https://dev.socrata.com/docs/queries/where.html SoQL $where Parameter
     *
     * @param   string $statement The `where` clause that will be used to filter data
     *
     * @since   0.1.0
     *
     * @return  $this  A SoqlQuery object that can continue to be chained
     */
    public function where ($statement)
    {
        $this->queryElements[self::WHERE_KEY] = rawurlencode($statement);

        return $this;
    }

    /**
     * Create a filter to aggregate your results using boolean operators, similar to the HAVING clause in SQL.
     *
     * @link    https://dev.socrata.com/docs/queries/having.html SoQL $having Parameter
     *
     * @param   string $statement The `having` clause that will be used to filter data
     *
     * @since   0.2.0
     *
     * @return  $this  A SoqlQuery object that can continue to be chained
     */
    public function having ($statement)
    {
        $this->queryElements[self::HAVING_KEY] = rawurlencode($statement);

        return $this;
    }

    /**
     * Determines the order and the column the results should be sorted by. This function may be used more than once in
     * a chain so duplicate entries in the first column will be sorted by the second specified column specified. If
     * this
     * function is called more than once in a chain, the order does matter in which order() you call first.
     *
     * @link    https://dev.socrata.com/changelog/2015/04/27/new-higher-performance-apis.html New Higher Performance API
     * @link    https://dev.socrata.com/docs/queries/order.html SoQL $order Parameter
     *
     * @param   string $column      The column(s) that determines how the results should be sorted. This information
     *                              can be given as an array of values, a single column, or a comma separated string.
     *                              In order to support sorting by multiple columns, you need to use the latest version
     *                              of the dataset API.
     * @param   string $direction   The direction the results should be sorted in, either ascending or descending. The
     *                              {@link SoqlOrderDirection} class provides constants to use should these values ever
     *                              change in the future. The only accepted values are: `ASC` and `DESC`
     *
     * @see     SoqlOrderDirection  View convenience constants
     *
     * @since   0.1.0
     *
     * @return  $this   A SoqlQuery object that can continue to be chained
     */
    public function order ($column, $direction = self::DEFAULT_ORDER_DIRECTION)
    {
        $this->queryElements[self::ORDER_KEY][] = rawurlencode($column . " " . $direction);

        return $this;
    }

    /**
     * Group the resulting dataset based on a specific column. This function must be used in conjunction with
     * `select()`.
     *
     * For example, to find the strongest earthquake by region, we want to group() by region and provide a select of
     * region, MAX(magnitude).
     *
     * ```php
     * $soql->select("region", "MAX(magnitude)")->group("region");
     * ```
     *
     * @link    https://dev.socrata.com/docs/queries/group.html  The $group Parameter
     *
     * @param   string $column The column that will be used to group the dataset
     *
     * @since   0.1.0
     *
     * @return  $this   A SoqlQuery object that can continue to be chained
     */
    public function group ($column)
    {
        $this->queryElements[self::GROUP_KEY][] = $column;

        return $this;
    }

    /**
     * Set the amount of results that can be retrieved from a dataset per query.
     *
     * @link    https://dev.socrata.com/docs/queries/limit.html  SoQL $limit Parameter
     *
     * @param   int $limit The number of results the dataset should be limited to when returned
     *
     * @throws  \InvalidArgumentException  If the given argument is not an integer
     * @throws  \OutOfBoundsException      If the given argument is less than 0
     *
     * @since   0.1.0
     *
     * @return  $this          A SoqlQuery object that can continue to be chained
     */
    public function limit ($limit)
    {
        $this->handleInteger("limit", $limit);

        $this->queryElements[self::LIMIT_KEY] = $limit;

        return $this;
    }

    /**
     * Analyze a given value and ensure the value fits the criteria set by the Socrata API
     *
     * @param   string $variable The literal name of this field
     * @param   int    $number   The value to analyze
     *
     * @since   0.1.0
     *
     * @throws  \InvalidArgumentException         If the given argument is not an integer
     * @throws  \OutOfBoundsException             If the given argument is less than 0
     */
    private function handleInteger ($variable, $number)
    {
        if (!is_integer($number))
        {
            throw new \InvalidArgumentException(sprintf("The %s must be an integer", $variable));
        }

        if ($number < 0)
        {
            $message = sprintf("The %s cannot be less than 0.", $variable);

            throw new \OutOfBoundsException($message, 1);
        }
    }

    /**
     * The offset is the number of records into a dataset that you want to start, indexed at 0. For example, to retrieve
     * the “4th page” of records (records 151 - 200) where you are using limit() to page 50 records at a time, you’d ask
     * for an $offset of 150.
     *
     * @link    https://dev.socrata.com/docs/queries/offset.html  SoQL $offset Parameter
     *
     * @param   int $offset The number of results the dataset should be offset to when returned
     *
     * @throws  \InvalidArgumentException  If the given argument is not an integer
     * @throws  \OutOfBoundsException      If the given argument is less than 0
     *
     * @since   0.1.0
     *
     * @return  $this           A SoqlQuery object that can continue to be chained
     */
    public function offset ($offset)
    {
        $this->handleInteger("offset", $offset);

        $this->queryElements[self::OFFSET_KEY] = $offset;

        return $this;
    }

    /**
     * Search the entire dataset for a specified string. Think of this as a search engine instead of performing a SQL
     * query.
     *
     * @param   string $needle The phrase to search for
     *
     * @since   0.1.0
     *
     * @return  $this            A SoqlQuery object that can continue to be chained
     */
    public function fullTextSearch ($needle)
    {
        $this->queryElements[self::SEARCH_KEY] = rawurlencode($needle);

        return $this;
    }
}
