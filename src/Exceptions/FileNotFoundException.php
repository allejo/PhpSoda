<?php

namespace allejo\Socrata\Exceptions;

class FileNotFoundException extends \Exception
{
    public function __construct ($filename)
    {
        $this->message = "The following file could not be found or opened: " . $filename;
    }
}
