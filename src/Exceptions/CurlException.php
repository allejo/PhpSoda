<?php

namespace allejo\Socrata\Exceptions;

class CurlException extends \Exception
{
    public function __construct ($code, $message)
    {
        $this->code = $code;
        $this->message = sprintf("cURL Error %d: %s", $code, $message);
    }
}