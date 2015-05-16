<?php

namespace allejo\Socrata\Utilities;

use allejo\Socrata\Exceptions\InvalidResourceException;

class StringUtilities
{
    /**
     * Validate a resource ID to be sure if matches the criteria
     *
     * @param  string $resourceID The 4x4 resource ID of a data set
     *
     * @throws InvalidResourceException If the resource ID isn't in the format of xxxx-xxxx
     */
    public static function validateResourceID ($resourceID)
    {
        if (!preg_match('/^[a-z0-9]{4}-[a-z0-9]{4}$/', $resourceID))
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
        return is_string($string) && is_object(json_decode($string)) && (json_last_error() == JSON_ERROR_NONE);
    }
}