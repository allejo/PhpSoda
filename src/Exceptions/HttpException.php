<?php

namespace allejo\Socrata\Exceptions;

class HttpException extends \Exception
{
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
