<?php

namespace Saraiva\Framework\Database;

class Connection {

    /**
     * Instância do PDO
     * @var PDO[]
     */
    public $instance;
    
    public function __construct($i = 0) {
        $this->instance[$i]->setAttribute(\PDO::ATTR_EMULATE_PREPARES, FALSE);
        $this->instance[$i]->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    }
    
    /**
     * @param int $i
     * @return PDO
     */
    public function get($i = 0) {
        return $this->instance[$i];
    }
    
    /**
     * @param int $i
     * @return PDO
     */
    public function __invoke($i = 0) {
        return $this->get($i);
    }

}
