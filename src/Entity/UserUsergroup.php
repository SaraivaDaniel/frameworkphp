<?php

namespace Saraiva\Framework\Entity;

use Saraiva\Framework\EntityBase;

class UserUsergroup extends EntityBase {

    static $_TABLE = 'user_usergroup';

    const FIELD_USER_ID = 'user_id';
    const FIELD_USERGROUP_ID = 'usergroup_id';

    static $_FIELDS = array(
        self::FIELD_USER_ID => array('name' => 'user_id', 'type' => 'int(11) unsigned', 'primary' => TRUE),
        self::FIELD_USERGROUP_ID => array('name' => 'usergroup_id', 'type' => 'int(11) unsigned', 'primary' => TRUE),
    );
    public $user_id;
    public $usergroup_id;

    //==/==/==// do not change anything above this line
}
