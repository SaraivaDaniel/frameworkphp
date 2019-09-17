<?php

namespace Saraiva\Framework\Entity;

use Saraiva\Framework\EntityBase;

class AuditLogin extends EntityBase {

    static $_TABLE = 'audit_login';

    const FIELD_ID = 'id';
    const FIELD_USER_ID = 'user_id';
    const FIELD_DATETIME = 'datetime';
    const FIELD_IP = 'ip';
    const FIELD_USER_AGENT = 'user_agent';

    static $_FIELDS = array(
        self::FIELD_ID => array('name' => 'id', 'type' => 'int(10) unsigned', 'auto' => TRUE, 'primary' => TRUE),
        self::FIELD_USER_ID => array('name' => 'user_id', 'type' => 'int(10) unsigned'),
        self::FIELD_DATETIME => array('name' => 'datetime', 'type' => 'datetime', 'datetime' => TRUE),
        self::FIELD_IP => array('name' => 'ip', 'type' => 'varchar(15)'),
        self::FIELD_USER_AGENT => array('name' => 'user_agent', 'type' => 'varchar(255)'),
    );
    
    public $id;
    public $user_id;
    public $datetime;
    public $ip;
    public $user_agent;

    //==/==/==// do not change anything above this line
}
