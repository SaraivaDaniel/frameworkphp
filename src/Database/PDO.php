<?php

namespace Saraiva\Framework\Database;

use Exception;

class PDO extends \PDO {

    protected $transactionCounter = 0;
    protected $transactionRollback = 0;

    function beginTransaction() {
        $this->transactionCounter++;
        
        if ($this->transactionCounter == 1) {
            return parent::beginTransaction();
        }
        
        $this->exec('SAVEPOINT trans'.$this->transactionCounter);
        
        return TRUE;
    }

    function commit($commit_always = FALSE) {
        if ($this->transactionCounter == 0) {
            throw new Exception('No transaction active');
        }
        
        $this->transactionCounter--;
        
        if ($this->transactionRollback && !$commit_always) {
            throw new Exception('An inner transaction was rolled back - cannot commit the transaction');
        }
        
        if ($this->transactionCounter == 0) {
            return parent::commit();
        }
        
        return TRUE; // $this->transactionCounter > 0
    }

    function rollback() {
        $this->transactionRollback++;
        $this->transactionCounter--;
        
        if ($this->transactionCounter > 0) {
            $this->exec('ROLLBACK TO trans' . ($this->transactionCounter + 1));
            return true;
        }
        
        return parent::rollback(); // $this->transactionCounter == 0
    }

}
