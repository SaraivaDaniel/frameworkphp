<?php

namespace Saraiva\Framework\Entity;

use Saraiva\Framework\EntityBase;

class AuditDatabase extends EntityBase {

    static $_TABLE = 'audit_database';

    const FIELD_ID = 'id';
    const FIELD_USER_ID = 'user_id';
    const FIELD_DT = 'dt';
    const FIELD_ACTION = 'action';
    const FIELD_TABLE = 'table';
    const FIELD_PK = 'pk';
    const FIELD_DATA = 'data';

    static $_FIELDS = array(
        self::FIELD_ID => array('name' => 'id', 'type' => 'int(11) unsigned', 'auto' => TRUE, 'primary' => TRUE),
        self::FIELD_USER_ID => array('name' => 'user_id', 'type' => 'int(11) unsigned'),
        self::FIELD_DT => array('name' => 'dt', 'type' => 'datetime', 'datetime' => TRUE),
        self::FIELD_ACTION => array('name' => 'action', 'type' => 'varchar(45)'),
        self::FIELD_TABLE => array('name' => 'table', 'type' => 'varchar(100)'),
        self::FIELD_PK => array('name' => 'pk', 'type' => 'text'),
        self::FIELD_DATA => array('name' => 'data', 'type' => 'text'),
    );

    public $id;
    public $user_id;
    public $dt;
    public $action;
    public $table;
    public $pk;
    public $data;

    //==/==/==// do not change anything above this line

}
