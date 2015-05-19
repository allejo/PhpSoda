<?php

namespace allejo\Socrata\Exceptions;

class CurlException extends \Exception
{
    /**
     * @param resource $cURLObject
     */
    public function __construct ($cURLObject)
    {
        $this->code    = curl_errno($cURLObject);
        $this->message = sprintf("cURL Error %d: %s", $this->code, curl_error($cURLObject));
    }
}
