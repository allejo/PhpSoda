<?php

namespace allejo\Sami;

use Sami\Parser\Filter\FilterInterface;
use Sami\Reflection\ClassReflection;
use Sami\Reflection\MethodReflection;
use Sami\Reflection\PropertyReflection;

class ApiFilter implements FilterInterface
{
    public function acceptClass (ClassReflection $class)
    {
        return true;
    }

    public function acceptMethod (MethodReflection $method)
    {
        return ($method->isPublic() && empty($method->getTags('internal')));
    }

    public function acceptProperty (PropertyReflection $property)
    {
        return ($property->isPublic() && empty($property->getTags('internal')));
    }
}
