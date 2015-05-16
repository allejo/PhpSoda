<?php

namespace allejo\Socrata\Exceptions;

class InvalidResourceException extends \Exception
{
    public function __construct ($message)
    {
        $this->message = $message;
    }
}
