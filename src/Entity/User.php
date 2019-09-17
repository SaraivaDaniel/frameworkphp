<?php

namespace Saraiva\Framework\Entity;

use Saraiva\Framework\EntityBase;

class User extends EntityBase {

    static $_TABLE = 'user';

    const FIELD_ID = 'id';
    const FIELD_NAME = 'name';
    const FIELD_EMAIL = 'email';
    const FIELD_PASSWORD = 'password';
    const FIELD_ACTIVE = 'active';
    const FIELD_AVATAR = 'avatar';
    const FIELD_USERNAME = 'username';
    const FIELD_PERSON_ID = 'person_id';
    const FIELD_IS_CLOCKABLE = 'is_clockable';

    static $_FIELDS = array(
        self::FIELD_ID => array('name' => 'id', 'type' => 'int(11) unsigned', 'auto' => TRUE, 'primary' => TRUE),
        self::FIELD_NAME => array('name' => 'name', 'type' => 'varchar(100)'),
        self::FIELD_EMAIL => array('name' => 'email', 'type' => 'varchar(255)'),
        self::FIELD_PASSWORD => array('name' => 'password', 'type' => 'char(60)'),
        self::FIELD_ACTIVE => array('name' => 'active', 'type' => 'tinyint(4)'),
        self::FIELD_AVATAR => array('name' => 'avatar', 'type' => 'blob'),
        self::FIELD_USERNAME => array('name' => 'username', 'type' => 'varchar(100)'),
        self::FIELD_PERSON_ID => array('name' => 'person_id', 'type' => 'int(10) unsigned'),
        self::FIELD_IS_CLOCKABLE => array('name' => 'is_clockable', 'type' => 'tinyint(3) unsigned'),
    );

    public $id;
    public $name;
    public $email;
    public $password;
    public $active;
    public $avatar;
    public $username;
    public $person_id;
    public $is_clockable;

    //==/==/==// do not change anything above this line
    
}
