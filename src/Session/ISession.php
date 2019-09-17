<?php

namespace Saraiva\Framework\Session;

interface ISession {

    public static function init();

    public static function close();

    public static function destroy();

    public static function set($name, $val);

    public static function get($name);

    public static function clear($name);

    public static function getId();

    public static function exists($name);
}
