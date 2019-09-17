<?php

namespace Saraiva\Framework\Exception;

class FormProcessModelException extends SaraivaException {
    
    protected $erros = array();
    
    public function __construct($message = "", $erros = array()) {
        parent::__construct($message);
        
        $this->erros = $erros;
    }
    
    public function getErros() {
        return $this->erros;
    }
    
}
