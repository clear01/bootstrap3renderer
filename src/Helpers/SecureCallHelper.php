<?php

namespace Clear01\BootstrapForm\Helpers;

use ReflectionClass;

class SecureCallHelper
{
    /**
     * Tries to call a method on an object. Returns NULL if the method does not exist or is not accessible.
     * @param object $object
     * @param string $method
     * @param mixed ...$args
     * @return mixed
     */
    public static function tryCall($object, $method, ...$args)
    {
        $rc = new ReflectionClass($object);
        if ($rc->hasMethod($method)) {
            $rm = $rc->getMethod($method);
            if ($rm->isPublic() && !$rm->isStatic()) {
                return $object->$method(...$args);
            }
        }
        return NULL;
    }
}