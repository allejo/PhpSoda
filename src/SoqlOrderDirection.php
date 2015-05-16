<?php

namespace allejo\Socrata;

abstract class SoqlOrderDirection
{
    const ASC  = 'ASC';
    const DESC = 'DESC';

    public static function parseOrder ($string)
    {
        if ($string === self::ASC || $string === self::DESC)
        {
            return $string;
        }

        throw new \InvalidArgumentException("An invalid sort order was given. You may only sort using ASC or DESC.");
    }
}
