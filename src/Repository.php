<?php

namespace Saraiva\Framework;

use Exception;
use PDOStatement;
use Saraiva\Framework\Database\Connection;

abstract class Repository {

    /**
     *
     * @var Database\Connection
     */
    public $connection;
    public $_EM;
    protected $_LOAD_RELATIONSHIPS = TRUE;
    protected $entity;
    protected $table;
    private $calling_entity = NULL;
    
    private $order_by = array();

    public function __construct(Connection $connection) {
        if (!($connection instanceof Database\Connection)) {
            throw new Exception('Deve passar um objeto Conexão para Repositório');
        }
        $this->connection = $connection;
        $this->_EM = new EntityManager($connection);
        
        $entity = str_replace('Repository', 'Entity', get_called_class());
        $this->entity = $entity;
        $this->table = $entity::$_TABLE;
    }
    
    public function disableLoadingOfRelationships() {
        $this->_LOAD_RELATIONSHIPS = FALSE;
    }
    
    /**
     * 
     * @param EntityBase $entity
     */
    public function setCallingEntity(EntityBase $entity = NULL) {
        $this->calling_entity = $entity;
    }
    
    /**
     * 
     * @return EntityBase
     */
    protected function getEntity() {
        $name = $this->entity;
        /* @var $entity EntityBase */
        $entity = new $name($this->_EM);
        
        if (!$this->_LOAD_RELATIONSHIPS) {
            $entity->disableLoadingOfRelationships();
        }
        
        return $entity;
    }
    
    /**
     * 
     * @param PDOStatement $stmt An executed statement
     * @param string $prefix
     * @return EntityCollection
     */
    protected function returnCollection($stmt, $prefix = '') {
        $result = new EntityCollection($this->_EM, $stmt, $this->entity);
        $result->setCallingEntity($this->calling_entity);
        
        if (!$this->_LOAD_RELATIONSHIPS) {
            $result->disableLoadingOfRelationships();
        }
        
        if ($prefix !== '') {
            $result->prefixTableName($prefix);
        }
        return $result;
    }
    
    protected function returnSingle(PDOStatement $stmt) {
        if ($stmt->rowCount() == 0) {
            return FALSE;
        }
        
        $e = $this->getEntity();
        $e->setCallingEntity($this->calling_entity);
        $e->_load($stmt->fetch());
        return $e;
    }
    
    public function getAll() {
        $where = $this->getAllWhere();
        $order = $this->getAllOrderBy();
        $sql = "SELECT * FROM {$this->table} $where $order";
        $instance = $this->connection->get();
        $stmt = $instance->prepare($sql);
        $stmt->execute();
        
        return $this->returnCollection($stmt);
    }
    
    /**
     * Filters getAll result, can be used in children classes to define expected result
     */
    protected function getAllWhere() {
        return '';
    }
    
    protected function getAllOrderBy() {
        return 'ORDER BY 1 ASC';
    }
    
    public function orderBy($field, $dir = 'ASC') {
        // verifica field
        if (NULL === constant($this->entity . "::FIELD_" . strtoupper($field))) {
            throw new Exception("Campo inválido");
        }
        
        $this->order_by[] = "$field $dir";
        return $this;
    }
    
    /**
     * 
     * @param string $field
     * @param string $value
     * @return EntityCollection
     */
    public function getByFieldValue($field, $value) {
        $sql = "SELECT * FROM {$this->table} WHERE $field = ?";
        
        if (count($this->order_by)) {
            $sql .= " ORDER BY " . implode(",", $this->order_by);
            $this->order_by = [];
        }
        
        $stmt = $this->connection->get()->prepare($sql);
        $stmt->execute(array($value));
        
        return $this->returnCollection($stmt);
    }
    
    public function getOneByFieldValue($field, $value) {
        $col = $this->getByFieldValue($field, $value);
        
        if ($col->count() === 0) {
            return FALSE;
        } else {
            return $col->current();
        }
    }
    
    public function getWhere($conds, array $values = array(), array $order_by = array()) {
        $sql = "SELECT * FROM {$this->table} WHERE ";
        
        if (count($conds)) {
            $sql .= "(" . implode(") AND (", $conds) . ")";
        }
        
        if (count($order_by)) {
            $sql .= " ORDER BY " . implode(",", $order_by);
        }
        
        $stmt = $this->connection->get()->prepare($sql);
        $stmt->execute($values);
        
        return $this->returnCollection($stmt);
    }

}
