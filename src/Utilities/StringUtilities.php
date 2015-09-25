<?php

/**
 * This file contains the StringUtilities class
 *
 * @copyright 2015 Vladimir Jimenez
 * @license   https://github.com/allejo/PhpSoda/blob/master/LICENSE.md MIT
 */

namespace allejo\Socrata\Utilities;

use allejo\Socrata\Exceptions\InvalidResourceException;

/**
 * This class contains static utilities used for validating strings
 *
 * @package allejo\Socrata\Utilities
 * @since   0.1.0
 */
class StringUtilities
{
    /**
     * Validate a resource ID to be sure if matches the criteria
     *
     * @param  string $resourceId The 4x4 resource ID of a data set
     *
     * @throws InvalidResourceException If the resource ID isn't in the format of xxxx-xxxx
     */
    public static function validateResourceId ($resourceId)
    {
        if (!preg_match('/^[a-z0-9]{4}-[a-z0-9]{4}$/', $resourceId))
        {
            throw new InvalidResourceException("The resource ID given didn't fit the expected criteria");
        }
    }

    /**
     * Test whether a string is proper JSON or not
     *
     * @param  string $string The string to be tested as JSON
     *
     * @return bool  True if the given string is JSON
     */
    public static function isJson ($string)
    {
        return is_string($string) && !is_null(json_decode($string)) && (json_last_error() == JSON_ERROR_NONE);
    }

    /**
     * Determine whether a string is null or empty
     *
     * @param  string $string The string to test
     *
     * @return bool True if string is null or empty
     */
    public static function isNullOrEmpty ($string)
    {
        return (!isset($string) || empty($string) || ctype_space($string));
    }
}
