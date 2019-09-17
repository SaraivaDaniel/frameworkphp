<?php

namespace Saraiva\Framework\Session;

class Session {

    /**
     *
     * @var ISession
     */
    static $adapter;

    public static function init($adapter = 'Native') {
        $adapter = "\\Saraiva\\Framework\\Session\\" . $adapter;
        static::$adapter = new $adapter();
        static::$adapter->init();
    }

    public static function close() {
        return static::$adapter->close();
    }

    public static function destroy() {
        return static::$adapter->destroy();
    }

    public static function get($name) {
        return static::$adapter->get($name);
    }

    public static function set($name, $val) {
        return static::$adapter->set($name, $val);
    }

    public static function clear($name) {
        return static::$adapter->clear($name);
    }

    public static function getId() {
        return static::$adapter->getId();
    }
    
    public static function exists($name) {
        return static::$adapter->exists($name);
    }
    

}
