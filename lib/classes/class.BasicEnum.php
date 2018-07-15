<?php
/*
Enumerator class
This is an extension of contributions by by Brian Cline and others.
See https://stackoverflow.com/questions/254514/php-and-enumerations
Stackoverflow contributions are CC BY-SA 3.0 licensed
*/

namespace CMSMS;

use ReflectionClass;

abstract class BasicEnum
{
    private static $constCacheArray = null;

    private static function getConstants()
    {
        if (self::$constCacheArray === null) {
            self::$constCacheArray = [];
        }
        $calledClass = get_called_class();
        if (!array_key_exists($calledClass, self::$constCacheArray)) {
            $reflect = new ReflectionClass($calledClass);
            self::$constCacheArray[$calledClass] = $reflect->getConstants();
        }
        return self::$constCacheArray[$calledClass];
    }

    public static function isValidName($name, $strict = false)
    {
        $constants = self::getConstants();
        if ($strict) {
            return array_key_exists($name, $constants);
        }
        $keys = array_map('strtolower', array_keys($constants));
        return in_array(strtolower($name), $keys);
    }

    public static function isValidValue($value, $strict = false)
    {
        $values = array_values(self::getConstants());
        return in_array($value, $values, $strict);
    }

    public static function getValue($name, $strict = false)
    {
        $constants = self::getConstants();
        if ($strict) {
            return $constants[$name] ?? null;
        }
        $key = array_search($name, array_keys($constants));
        return ($key !== false) ? $constants[$key] : null;
    }

    public static function getNames()
    {
        return array_keys(self::getConstants());
    }
} // class