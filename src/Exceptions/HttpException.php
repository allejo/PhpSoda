<?php

namespace allejo\Socrata\Exceptions;

class HttpException extends \Exception
{
    public function __construct ($code, $json)
    {
        $json = json_decode($json);
        $status  = ucfirst($json->code);
        $message = $json->message;

        $this->code = $code;
        $this->message = sprintf("HTTP %s %d: %s", $status, $code, $message);
    }
}