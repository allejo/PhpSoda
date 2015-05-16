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
    private $orderByColumn;
    private $groupByColumns;
    private $limitValue;
    private $offsetValue;
    private $searchText;

    /**
     * Write a SoQL query by chaining functions. This object will handle encoding the final query in order for it to be
     * used properly as a URL
     */
    public function __construct ()
    {
        $this->selectColumns  = array(self::DefaultSelect);
        $this->orderByColumn  = self::DefaultOrder;
        $this->orderDirection = self::DefaultOrderDirection;
    }

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

        $soql_query .= sprintf("&%s=%s", self::OrderKey, urlencode($this->orderByColumn . " " . $this->orderDirection));

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
     * @link    http://dev.socrata.com/docs/queries.html#the-order-parameter SoQL $order Parameter
     *
     * @param   string  $column     The column that determines how the results should be sorted
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
        $this->orderByColumn = $column;
        $this->orderDirection = SoqlOrderDirection::parseOrder($direction);

        return $this;
    }

    public function group ($columns = array())
    {
        $this->groupByColumns = $columns;

        return $this;
    }

    public function limit ($limit)
    {
        if (!is_integer($limit))
        {
            throw new \InvalidArgumentException("A limit must be an integer");
        }

        if ($limit <= 0)
        {
            throw new \OutOfBoundsException("A limit cannot be less than or equal to 0.", 1);
        }

        $this->limitValue = min(self::MaximumLimit, $limit);

        return $this;
    }

    public function offset ($offset)
    {
        if (!is_integer($offset))
        {
            throw new \InvalidArgumentException("An offset must be an integer");
        }

        if ($offset < 0)
        {
            throw new \OutOfBoundsException("An offset cannot be less than 0.", 1);
        }

        $this->offsetValue = $offset;

        return $this;
    }

    public function fullTextSearch ($needle)
    {
        $this->searchText = $needle;

        return $this;
    }
}
