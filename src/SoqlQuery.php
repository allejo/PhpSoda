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

    private $selectColumns;
    private $whereClause;
    private $orderDirection;
    private $orderByColumns;
    private $groupByColumns;
    private $limitValue;
    private $offsetValue;
    private $searchText;

    /**
     * Write a SoQL query by chaining functions. This object will handle encoding the final query in order for it to be
     * used properly as a URL
     *
     * @since 0.1.0
     */
    public function __construct ()
    {
        $this->selectColumns  = array(self::DefaultSelect);
        $this->orderByColumns = self::DefaultOrder;
        $this->orderDirection = self::DefaultOrderDirection;
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
        $soql_query = sprintf("%s=", self::SelectKey);

        if (count($this->selectColumns) === 1 && $this->selectColumns[0] === "*")
        {
            $soql_query .= $this->selectColumns[0];
        }
        else
        {
            $selectedColumns = array();

            foreach ($this->selectColumns as $key => $value)
            {
                if (is_string($key))
                {
                    $selectedColumns[] = urlencode(sprintf("%s AS %s", $key, $value));
                }
                else
                {
                    $selectedColumns[] = $value;
                }
            }

            $soql_query .= implode(self::Delimiter, $selectedColumns);
        }

        $soql_query .= sprintf("&%s=%s", self::OrderKey, urlencode($this->orderByColumns . " " . $this->orderDirection));

        if (!StringUtilities::isNullOrEmpty($this->whereClause))
        {
            $soql_query .= sprintf("&%s=%s", self::WhereKey, urlencode($this->whereClause));
        }

        if (count($this->groupByColumns) > 0)
        {
            $soql_query .= sprintf("&%s=%s", self::GroupKey, implode(self::Delimiter, $this->groupByColumns));
        }

        if ($this->offsetValue > 0)
        {
            $soql_query .= sprintf("&%s=%s", self::OffsetKey, $this->offsetValue);
        }

        if ($this->limitValue > 0)
        {
            $soql_query .= sprintf("&%s=%s", self::LimitKey, $this->limitValue);
        }

        if (!StringUtilities::isNullOrEmpty($this->searchText))
        {
            $soql_query .= sprintf("&%s=%s", self::SearchKey, urlencode($this->searchText));
        }

        return $soql_query;
    }

    /**
     * Select only specific columns in your Soql Query. When this function is given no parameters or is not used in a
     * query, the Soql Query will return all of the columns by default.
     *
     * ```
     * // These are all valid usages
     * $soqlQuery->select();
     * $soqlQuery->select("foo", "bar", "baz");
     * $soqlQuery->select(array("foo", "bar", "baz"));
     * ```
     *
     * @link    http://dev.socrata.com/docs/queries.html#the-select-parameter SoQL $select Parameter
     *
     * @param   array|mixed $columns   The columns to select from the dataset. The columns can be specified as an array
     *                                 of values or it can be specified as multiple parameters separated by commas.
     *
     * @since   0.1.0
     *
     * @return  $this  A SoqlQuery object that can continue to be chained
     */
    public function select ($columns = self::DefaultSelect)
    {
        if (func_num_args() == 1)
        {
            $this->selectColumns = (is_array($columns)) ? $columns : array($columns);
        }
        else if (func_num_args() > 1)
        {
            $this->selectColumns = func_get_args();
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
        $this->whereClause = $statement;

        return $this;
    }

    /**
     * Determines the order the results should be sorted in.
     *
     * @link    http://dev.socrata.com/changelog/2015/04/27/new-higher-performance-apis.html New Higher Performance API
     * @link    http://dev.socrata.com/docs/queries.html#the-order-parameter SoQL $order Parameter
     *
     * @param   string|array  $column     The column(s) that determines how the results should be sorted. This information
     *                                    can be given as an array of values, a single column, or a comma separated string.
     *                                    In order to support sorting by multiple columns, you need to use the latest version
     *                                    of the dataset API.
     * @param   string        $direction  The direction the results should be sorted in, either ascending or descending. The
     *                                    {@link SoqlOrderDirection} class provides constants to use should these values ever change
     *                                    in the future. The only accepted values are: `ASC` and `DESC`
     *
     * @see     SoqlOrderDirection        View convenience constants
     *
     * @since   0.1.0
     *
     * @return  $this         A SoqlQuery object that can continue to be changed
     */
    public function order ($column, $direction = self::DefaultOrderDirection)
    {
        $this->orderByColumns = $this->handlePossibleArray($column);
        $this->orderDirection = SoqlOrderDirection::parseOrder($direction);

        return $this;
    }

    public function group ($columns)
    {
        $this->groupByColumns = $columns;

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

        $this->limitValue = min(self::MaximumLimit, $limit);

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

        $this->offsetValue = $offset;

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
        $this->searchText = $needle;

        return $this;
    }

    /**
     * Convert an array argument into a comma separated value or just return the string as it was
     *
     * @param   string|array  $mixed  Multiple values given as an array or a comma separated list in a string
     *
     * @since   0.1.0
     *
     * @return  string        A comma separated list or a single value
     */
    private function handlePossibleArray ($mixed)
    {
        return (is_array($mixed)) ? implode(self::Delimiter, $mixed) : $mixed;
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
