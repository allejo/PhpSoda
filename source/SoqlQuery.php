<?php

namespace allejo\Socrata;

abstract class SoqlorderDirection
{
    const ASC = 'ASC';
    const DESC = 'DESC';
}

class SoqlQuery
{
    public static const Delimiter = ',';
    public static const SelectKey = '$select';
    public static const WhereKey  = '$where';
    public static const OrderKey  = '$order';
    public static const GroupKey  = '$group';
    public static const LimitKey  = '$limit';
    public static const OffsetKey = '$offset';
    public static const SearchKey = '$q';

    public static const DefaultSelect[] = array('*');
    public static const DefaultOrderDirection = SoqlorderDirection::ASC;
    public static const DefaultOrder[] = array(':id');
    public static const MaximumLimit = 1000;

    private $selectColumns;
    private $whereClause;
    private $orderDirection;
    private $orderByColumns;
    private $groupByColumns;
    private $limitValue;
    private $offsetValue;
    private $searchText;

    public function __construct ()
    {
        $this->selectColumns = self::DefaultSelect;
        $this->orderByColumns = self::DefaultOrder;
        $this->orderDirection = self::DefaultOrderDirection;
    }

    public function __tostring ()
    {
        $soql_query = sprintf("%s=", self::SelectKey);

        if (count($this->selectColumns) === 1 && $this->selectColumns[0] === "*")
        {
            $soql_query .= $this->selectColumns[0]);
        }
        else
        {
            $selectedColumns = array();

            foreach ($this->selectColumns as $key => $value)
            {
                if (is_string($key))
                {
                    $selectedColumns[] = sprintf("%s AS %s", $key, $value);
                }
                else
                {
                    $selectedColumns[] = $value;
                }
            }

            $soql_query .= implode(self::Delimiter, $selectedColumns);
        }

        $soql_query = sprintf("&%s=%s %s", self::OrderKey, implode(self::Delimiter, $this->orderByColumns), $this->orderDirection);

        if ($this->isNullOrEmpty($this->whereClause))
        {
            $soql_query .= sprintf("&%s=%s", self::WhereKey, $this->whereClause);
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

        if ($this->isNullOrEmpty($this->searchText))
        {
            $soql_query .= sprintf("&%s=%s", self::SearchKey, $this->searchTexth);
        }

        return $soql_query;
    }

    public function select ($columns = self::DefaultSelect)
    {
        $this->selectedColumns = $columns;

        return $this;
    }

    public function where ($statement)
    {
        $this->whereClause = $statement;

        return $this;
    }

    public function order ($columns, $direction = self::DefaultOrderDirection)
    {
        $this->orderByColumns = $columns;
        $this->orderDirection = $direction;

        return $this;
    }

    public function group ($columns = array())
    {
        $this->groupByColumns = $columns;

        return $this;
    }

    public function limit ($limit)
    {
        if ($limit <= 0)
        {
            throw new OutOfBoundsException("A limit cannot be less than or equal to 0.", 1);
        }

        $this->limitValue = min(self::MaximumLimit, $limit);

        return $this;
    }

    public function offset ($offset)
    {
        if ($offset <= 0)
        {
            throw new OutOfBoundsException("An offset cannot be less than or equal to 0.", 1);
        }

        $this->offset = $offset;

        return $this;
    }

    public function fullTextSearch ($needle)
    {
        $this->searchText = $needle;

        return $this;
    }

    private function isNullOrEmpty ($string)
    {
        return (!isset($string) || ctype_space($string));
    }
}