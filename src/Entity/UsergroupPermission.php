<?php

namespace Saraiva\Framework\Entity;

use Saraiva\Framework\EntityBase;

class UsergroupPermission extends EntityBase {

    static $_TABLE = 'usergroup_permission';

    const FIELD_USERGROUP_ID = 'usergroup_id';
    const FIELD_CLASS = 'class';
    const FIELD_PERMISSION = 'permission';

    static $_FIELDS = array(
        self::FIELD_USERGROUP_ID => array('name' => 'usergroup_id', 'type' => 'int(10) unsigned', 'primary' => TRUE),
        self::FIELD_CLASS => array('name' => 'class', 'type' => 'varchar(45)', 'primary' => TRUE),
        self::FIELD_PERMISSION => array('name' => 'permission', 'type' => 'int(11)'),
    );
    public $usergroup_id;
    public $class;
    public $permission;

    //==/==/==// do not change anything above this line
}
