<?php

namespace Saraiva\Framework\Framework;

use Closure;
use Exception;
use Saraiva\Framework\Database\Connection;

class SimpleAjaxRequest {

    protected $con;
    protected $callback;

    public function __construct(Connection $con, Closure $callback) {
        $this->con = $con;
        $this->callback = $callback;
    }

    public function executeTransaction(Closure $exception = NULL) {
        try {
            $this->con->instance->beginTransaction();

            call_user_func($this->callback);

            $this->con->instance->commit();

            $result = array('IsSuccess' => TRUE, 'msg' => 'Sucesso');
        } catch (Exception $ex) {
            $this->con->instance->rollback();
            if (is_callable($exception)) {
                $exception();
            }
            $result = array('IsSuccess' => FALSE, 'msg' => $ex->getMessage());
        }

        header('Content-Type: application/json');
        echo json_encode($result);
        die();
    }

    public function execute(Closure $exception = NULL) {
        try {
            call_user_func($this->callback);
            $result = array('IsSuccess' => TRUE, 'msg' => 'Sucesso');
        } catch (Exception $ex) {
            if (is_callable($exception)) {
                $exception();
            }
            $result = array('IsSuccess' => FALSE, 'msg' => $ex->getMessage());
        }

        header('Content-Type: application/json');
        echo json_encode($result);
        die();
    }

}
