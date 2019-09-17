<?php

namespace Saraiva\Framework;

use Saraiva\Framework\Database\Connection;

class Model {

    /**
     *
     * @var Connection
     */
    protected $connection = null;

    public function __construct(Connection $connection) {
        $this->connection = $connection;
    }

}
