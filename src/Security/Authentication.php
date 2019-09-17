<?php

namespace Saraiva\Framework\Security;

use Exception;
use Saraiva\Framework\Session\Session;

class Authentication {

    const SESSION_HOLDER = 'ACC';

    public static function isLoggedIn() {
        if (!Session::exists(self::SESSION_HOLDER)) {
            return FALSE;
        }

        return TRUE;
    }

    public static function login($id, $email, $name, $avatar = '') {
        static::logout();
        Session::set(self::SESSION_HOLDER, array(
            'id' => $id,
            'email' => $email,
            'name' => $name,
            'avatar' => $avatar,
        ));
        return TRUE;
    }

    public static function logout() {
        Session::clear(self::SESSION_HOLDER);
    }

    public static function getData($name) {
        if (static::isLoggedIn()) {
            $session = Session::get(self::SESSION_HOLDER);
            if (isset($session[$name])) {
                return $session[$name];
            } else {
                return NULL;
            }
        }

        throw new Exception('Not logged in');
    }

    public static function getLoggedInEmail() {
        return static::getData('email');
    }

    public static function getLoggedInName() {
        return static::getData('name');
    }

    public static function getLoggedInId() {
        return static::getData('id');
    }
    
    public static function getLoggedInAvatar() {
        return static::getData('avatar');
    }

}
