<?php

namespace Saraiva\Framework;

use Countable;
use Iterator;
use PDO;
use PDOStatement;

class EntityCollection implements Iterator, Countable {
    
    protected $_EM;
    /**
     *
     * @var PDOStatement
     */
    protected $stmt;
    protected $class;
    protected $key = 0;
    protected $current;
    protected $valid;
    protected $prefix_table_name = '';
    protected $_LOAD_RELATIONSHIPS = TRUE;
    protected $count = 0;
    protected $cache = array();
    private $calling_entity = NULL;
    
    public function __construct(EntityManager $em, PDOStatement $stmt, $class) {
        $this->_EM = $em;
        $this->class = $class;
        $this->stmt = $stmt;
        $this->count = $stmt->rowCount();
    }
    
    public function prefixTableName($prefix) {
        $this->prefix_table_name = $prefix . '_';
    }
    
    public function disableLoadingOfRelationships() {
        $this->_LOAD_RELATIONSHIPS = FALSE;
    }
    
    public function setCallingEntity(EntityBase $entity = NULL) {
        $this->calling_entity = $entity;
    }
    
    public function count() {
        return $this->count;
    }
    
    /**
     * Carrega valor corrente
     * @return EntityBase
     */
    public function current() {
        if (isset($this->cache[$this->key])) {
            return $this->cache[$this->key];
        }
        
        // cria nova classe, que deve ser uma entity
        /* @var $obj EntityBase */
        $obj = new $this->class($this->_EM);
        $obj->setCallingEntity($this->calling_entity);
        
        if (!$this->_LOAD_RELATIONSHIPS) {
            $obj->disableLoadingOfRelationships();
        }
        
        $data = $this->stmt->fetch(PDO::FETCH_ASSOC);
        $obj->_load($data, $this->prefix_table_name !== '');
        
        $this->cache[$this->key] = $obj;
        return $obj;
    }

    public function key() {
        return $this->key;
    }

    public function next() {
        $this->key++;
    }

    public function rewind() {
        $this->key = 0;
    }

    public function valid() {
        return ($this->key < $this->count);
    }

}