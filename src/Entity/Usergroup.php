<?php

namespace Saraiva\Framework\Entity;

use Saraiva\Framework\EntityBase;

class Usergroup extends EntityBase {

    static $_TABLE = 'usergroup';

    const FIELD_ID = 'id';
    const FIELD_NAME = 'name';
    const FIELD_DESCRIPTION = 'description';

    static $_FIELDS = array(
        self::FIELD_ID => array('name' => 'id', 'type' => 'int(11) unsigned', 'auto' => TRUE, 'primary' => TRUE),
        self::FIELD_NAME => array('name' => 'name', 'type' => 'varchar(45)'),
        self::FIELD_DESCRIPTION => array('name' => 'description', 'type' => 'varchar(45)'),
    );

    public $id;
    public $name;
    public $description;

    //==/==/==// do not change anything above this line

}
