<?php

namespace Saraiva\Framework\Security;

use Saraiva\Framework\Session\Session;

class PBAC {

    const CLASS_NAME = 'PBAC';
    const SESSION_HOLDER = '\Saraiva\Framework\Security\PBAC';
    
    public static function init() {
        Session::clear(self::SESSION_HOLDER);
        Session::set(self::SESSION_HOLDER, array());
    }
    
    public static function clear() {
        Session::clear(self::SESSION_HOLDER);
    }
    
    public static function addPermissions($array) {
        $current = Session::get(self::SESSION_HOLDER);
        
        foreach ($array as $class => $permission) {
            if (isset($current[$class])) {
                // notar o uso de BITWISE OR para combinar as permissões
                $current[$class] |= $permission; 
            } else {
                $current[$class] = $permission; 
            }
        }
        
        Session::set(self::SESSION_HOLDER, $current);
    }
    
    public static function hasPermission($class, $permission) {
        $current = Session::get(self::SESSION_HOLDER);
        if (isset($current[$class]) && ($current[$class] & $permission)) {
            return TRUE;
        } else {
            return FALSE;
        }
    }
    
    public static function hasOneOfPermissions($class, array $permissions) {
        foreach ($permissions as $permission) {
            if (static::hasPermission($class, $permission)) {
                return TRUE;
            }
        }
        return FALSE;
    }

}
