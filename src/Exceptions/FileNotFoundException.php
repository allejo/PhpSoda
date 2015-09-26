<?php

/**
 * This file contains the FileNotFoundException
 *
 * @copyright 2015 Vladimir Jimenez
 * @license   https://github.com/allejo/PhpSoda/blob/master/LICENSE.md MIT
 */

namespace allejo\Socrata\Exceptions;

/**
 * An exception thrown if a file trying to be access could not be found
 *
 * @package allejo\Socrata\Exceptions
 * @since   0.1.0
 */
class FileNotFoundException extends \Exception
{
    /**
     * Create an exception
     *
     * @param string $filename The path to the file
     *
     * @since 0.1.0
     */
    public function __construct ($filename)
    {
        $this->message = "The following file could not be found or opened: " . $filename;
    }
}
