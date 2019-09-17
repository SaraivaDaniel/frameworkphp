<?php

namespace Saraiva\Framework;

use Exception;

class Config {
    
    public static $config = array();
    
    public static function loadConfig(array $config) {
        static::$config = array_merge_recursive(static::$config, $config);
    }
    
    public static function getItemByPath($path, $path_separator = '/') {
        $parts = explode($path_separator, $path);
        if (count($parts) == 0) {
            throw new Exception("Path do item inválido");
        }
        
        $rparts = array_reverse($parts);
        $root = array_pop($rparts);
        
        if (!isset(static::$config[$root])) {
            throw new Exception("Path inválido");
        }
        $config = static::$config[$root];
        
        while ($part = array_pop($rparts)) {
            if (!isset($config[$part])) {
                throw new Exception("Path inválido");
            }
            $config = $config[$part];
        }
        
        return $config;
    }
    
}
