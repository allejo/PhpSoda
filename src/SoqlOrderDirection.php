<?php

/**
 * This file contains an abstract class with constants defining the order a Soda query could be returned in.
 *
 * @copyright 2015 Vladimir Jimenez
 * @license   https://github.com/allejo/PhpSoda/blob/master/LICENSE.md MIT
 */

namespace allejo\Socrata;

/**
 * An abstract class with constants defining the order a Soda query could be return in.
 *
 * @package allejo\Socrata
 * @since   0.1.0
 */
abstract class SoqlOrderDirection
{
    /**
     * The ascending clause Socrata expects
     */
    const ASC  = 'ASC';

    /**
     * The descending clause Socrata expects
     */
    const DESC = 'DESC';

    /**
     * Ensure that we have a proper sorting order, so return only valid ordering options
     *
     * @param  string $string The order to be checked if valid
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
