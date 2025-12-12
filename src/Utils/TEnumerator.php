<?php

namespace Clear01\BootstrapForm\Utils;

use ReflectionClass;
use RuntimeException;

trait TEnumerator
{
    public static function getPossibleValues()
    {
        $refl = new ReflectionClass(get_called_class());
        return array_values($refl->getConstants());
    }

    public static function isValidValue($value)
    {
        return in_array($value, static::getPossibleValues(), TRUE);
    }

    public static function assertValidValue($value)
    {
        if (!static::isValidValue($value)) {
            throw new RuntimeException(sprintf('""%s" is not a valid value for enum %s', $value, __CLASS__));
        }
    }
}