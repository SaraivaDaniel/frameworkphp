<?php

namespace Saraiva\Framework\Security;

use Exception;
use Saraiva\Framework\Exception\UnauthorizedException;

class AccessControl {

    static $METHOD;

    public static function init($method) {
        if (!file_exists(__DIR__ . '/' . $method . '.php')) {
            throw new Exception("Method don't exist");
        }
        static::$METHOD = "\\Saraiva\\Framework\\Security\\" . $method;
    }
    
    public static function addPermissions($array) {
        return call_user_func_array(array(static::$METHOD, 'addPermissions'), array($array));
    }

    public static function hasPermission($class, $permission) {
        return call_user_func_array(array(static::$METHOD, 'hasPermission'), array($class, $permission));
    }

    public static function hasOneOfPermissions($class, array $permissions) {
        return call_user_func_array(array(static::$METHOD, 'hasOneOfPermissions'), array($class, $permissions));
    }
    
    public static function checkHasPermission($class, $permission) {
        if (FALSE === static::hasPermission($class, $permission)) {
            throw new UnauthorizedException("Não possui permissão para $class:$permission");
        }
    }

    public static function checkHasOneOfPermissions($class, array $permissions) {
        if (FALSE === static::hasOneOfPermissions($class, $permissions)) {
            throw new UnauthorizedException("Não possui permissão para essa função");
        }
    }
    
}
