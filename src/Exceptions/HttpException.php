<?php

/**
 * This file contains the HttpException
 *
 * @copyright 2015 Vladimir Jimenez
 * @license   https://github.com/allejo/PhpSoda/blob/master/LICENSE.md MIT
 */

namespace allejo\Socrata\Exceptions;

/**
 * An exception thrown if a cURL job returned an HTTP status of anything but 200
 *
 * @package allejo\Socrata\Exceptions
 * @since   0.1.0
 */
class HttpException extends \Exception
{
    /**
     * Create an exception
     *
     * @param string $code      The HTTP code returned
     * @param string $response  A JSON formatted string containing information regarding the HTTP error or a string
     *                          simply containing stating the error.
     *
     * @since 0.1.0
     */
    public function __construct ($code, $response)
    {
        $json = json_decode($response);

        if (is_null($json))
        {
            $status        = ucfirst($json['code']);
            $message       = $json['message'];
            $this->message = sprintf("HTTP %s %d: %s", $status, $code, $message);
        }
        else
        {
            $this->message = $response;
        }

        $this->code = $code;
    }
}
