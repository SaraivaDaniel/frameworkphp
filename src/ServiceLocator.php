<?php


namespace Saraiva\Framework;


class ServiceLocator
{

    private static $registry = [];

    public static function register($name, $obj)
    {
        static::$registry[$name] = $obj;
    }

    public static function get($name)
    {
        return static::$registry[$name];
    }

}