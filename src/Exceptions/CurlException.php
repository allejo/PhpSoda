<?php

/**
 * This file contains the CurlException
 *
 * @copyright 2015 Vladimir Jimenez
 * @license   https://github.com/allejo/PhpSoda/blob/master/LICENSE.md MIT
 */

namespace allejo\Socrata\Exceptions;

/**
 * An exception thrown if cURL were to face an issue while processing a request
 *
 * @package allejo\Socrata\Exceptions
 * @since   0.1.0
 */
class CurlException extends \Exception
{
    /**
     * Create a new exception
     *
     * @param resource $cURLObject The cURL object used when cURL faced an issue
     *
     * @since 0.1.0
     */
    public function __construct ($cURLObject)
    {
        $this->code    = curl_errno($cURLObject);
        $this->message = sprintf("cURL Error %d: %s", $this->code, curl_error($cURLObject));
    }
}
