<?php

namespace Saraiva\Framework\Database;

class ConnectionMysql extends Connection {

    public function __construct($host, $schema, $user, $password, $port = 3306) {
        $dsn = "mysql:dbname=$schema;host=$host;port=$port;charset=UTF8";        
        $options = array();

        // do not use \PDO -> \Saraiva\Framework\Database\PDO extends \PDO
        $instance = new PDO($dsn, $user, $password, $options);
        $instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $instance->setAttribute(PDO::ATTR_EMULATE_PREPARES, FALSE);

	$instance->exec("SET SESSION sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''));");
        
        $this->instance[] = $instance;
        
        $i = count($this->instance) - 1;
        
        parent::__construct($i);
    }

}
