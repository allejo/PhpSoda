<?php

namespace allejo\Socrata\Converters;

abstract class Converter
{
    abstract public function toJson();
}