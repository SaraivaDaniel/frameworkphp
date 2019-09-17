<?php

namespace Saraiva\Framework\Session;

class Native implements ISession {

    public static function init() {
        return session_start();
    }

    public static function close() {
        return session_write_close();
    }

    public static function destroy() {
        return session_destroy();
    }

    public static function get($name) {
        if (!static::exists($name)) {
            return NULL;
        }
        return $_SESSION[$name];
    }

    public static function set($name, $val) {
        $_SESSION[$name] = $val;
    }

    public static function clear($name) {
        unset($_SESSION[$name]);
    }

    public static function getId() {
        return session_id();
    }
    
    public static function exists($name) {
        return isset($_SESSION[$name]);
    }

}
