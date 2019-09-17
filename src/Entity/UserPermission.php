<?php

namespace Saraiva\Framework\Entity;

use Saraiva\Framework\EntityBase;

class UserPermission extends EntityBase {

    static $_TABLE = 'user_permission';

    const FIELD_USER_ID = 'user_id';
    const FIELD_CLASS = 'class';
    const FIELD_PERMISSION = 'permission';

    static $_FIELDS = array(
        self::FIELD_USER_ID => array('name' => 'user_id', 'type' => 'int(10) unsigned', 'primary' => TRUE),
        self::FIELD_CLASS => array('name' => 'class', 'type' => 'varchar(45)', 'primary' => TRUE),
        self::FIELD_PERMISSION => array('name' => 'permission', 'type' => 'int(11)'),
    );

    public $user_id;
    public $class;
    public $permission;

    //==/==/==// do not change anything above this line
}
