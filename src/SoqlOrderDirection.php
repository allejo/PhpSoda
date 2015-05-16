<?php

namespace allejo\Socrata;

abstract class SoqlOrderDirection
{
    const ASC  = 'ASC';
    const DESC = 'DESC';

    /**
     * Ensure that we have a proper sorting order, so return only valid ordering options
     *
     * @param  string  $string            The order to be checked if valid
     *
     * @throws \InvalidArgumentException  If an unsupported sort order was given
     *
     * @return string                     Supported sorting order option
     */
    public static function parseOrder ($string)
    {
        if ($string === self::ASC || $string === self::DESC)
        {
            return $string;
        }

        throw new \InvalidArgumentException(sprintf("An invalid sort order (%s) was given; you may only sort using ASC or DESC.", $string));
    }
}
