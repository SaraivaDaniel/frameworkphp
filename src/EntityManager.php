<?php

namespace Saraiva\Framework;

use Carbon\Carbon;
use Saraiva\Framework\Entity\AuditDatabase;
use Saraiva\Framework\Entity\User;
use Exception;
use PDO;
use PDOStatement;
use Saraiva\Framework\Database\Connection;
use Saraiva\Framework\ServiceLocator;

class EntityManager {

    /**
     *
     * @var Connection
     */
    public $connection;
    /**
     *
     * @var PDO
     */
    public $pdo;
    
    private $audit_enabled = TRUE;
    private $last_insert_id = NULL;

    /**
     * 
     * @param Connection $connection
     * @param bool $audit_enabled
     */
    public function __construct(Connection $connection, $audit_enabled = TRUE) {
        $this->connection = $connection;
        $this->pdo = $connection->get();
        $this->audit_enabled = $audit_enabled;
    }

    /**
     * Binds values to params in a prepared query
     * @param EntityBase $entity
     * @param PDOStatement $stmt
     * @param string[] $fields
     * @return int
     * @throws Exception
     */
    protected function _bindValues(EntityBase $entity, PDOStatement &$stmt, $fields) {
        $i = 0;

        foreach ($entity->_getFields() as $field) {

            switch (TRUE) {
                case !in_array($field['name'], $fields):
                    // se não estiver no array, então não pode realizar o bind
                    break;

                case $entity->{$field['name']} === NULL:
                    $stmt->bindValue($field['name'], 'NULL', PDO::PARAM_NULL);
                    $i++;
                    break;

                case $entity->{$field['name']} === '':
                    $stmt->bindValue($field['name'], '', PDO::PARAM_STR);
                    $i++;
                    break;

                case (isset($field['datetime']) && $field['datetime']):
                    /* @var $date Carbon */
                    $date = $entity->{$field['name']};
                    if (!($date instanceof Carbon)) {
                        throw new Exception('Deve fornecer um objeto Carbon para campos date, time ou datetime');
                    }

                    switch ($field['type']) {
                        case 'date':
                            $str = $date->format('Y-m-d');
                            break;

                        case 'time':
                            $str = $date->format('H:i:s');
                            break;

                        case 'datetime':
                            $str = $date->format('Y-m-d H:i:s');
                            break;

                        default:
                            throw new Exception("Formato inválido");
                    }
                    $stmt->bindValue($field['name'], $str, PDO::PARAM_STR);
                    $i++;
                    break;

                case (strstr($field['type'], 'int') != FALSE):
                case (strstr($field['type'], 'decimal') != FALSE):
                    $stmt->bindValue($field['name'], $entity->{$field['name']}, PDO::PARAM_INT);
                    $i++;
                    break;

                default:
                    $stmt->bindValue($field['name'], $entity->{$field['name']}, PDO::PARAM_STR);
                    $i++;
            }
        }

        return $i;
    }

    /**
     * Update an Entity<br>
     * The entity pk info must be set using Entity::setKeyValues
     * @param EntityBase $entity
     * @return boolean
     * @throws Exception
     */
    public function update(EntityBase $entity) {
        $fields = array();
        $where = array();
        $set = array();
        foreach ($entity->_getFields() as $field) {
            if (isset($field['primary']) && $field['primary'] && $entity->{$field['name']} != NULL) {
                $where[] = "`{$field['name']}` = :PRI__KEY__{$field['name']}";
            }

            $set[] = "`{$field['name']}` = :{$field['name']}";
            $fields[] = $field['name'];
        }

        if (count($where) == 0) {
            throw new Exception('Os valores da chave primária da entidade não podem ser nulos/vazios');
        }

        $sql = sprintf("UPDATE `%s` SET %s WHERE (%s)", $entity->_getTable(), implode(',', $set), implode(' AND ', $where));
        $stmt = $this->pdo->prepare($sql);

        $this->_bindValues($entity, $stmt, $fields);
        foreach ($entity->getKeyValues() as $field => $value) {
            $stmt->bindValue('PRI__KEY__' . $field, $value);
        }

        $stmt->execute();
        
        if ($this->audit_enabled) {
            $this->auditUpdate($entity);
        }

        return TRUE;
    }

    /**
     * Persists an Entity to the database
     * 
     * Auto increment fields must be empty for it to be automatically generated - otherwise
     * it's value will be used during insert
     * @param EntityBase $entity
     * @return boolean
     * @throws Exception
     */
    public function insert(EntityBase $entity) {
        $fields = array();
        
        foreach ($entity->_getFields() as $field) {
            if (!isset($field['auto']) || $field['auto'] !== TRUE || $entity->{$field['name']} != NULL) {
                $fields[] = $field['name'];
            }
        }
        
        $sql = sprintf("INSERT INTO `%s` (`%s`) VALUES (:%s)", $entity->_getTable(), implode('`, `', $fields), implode(', :', $fields));
        $stmt = $this->pdo->prepare($sql);
        
        if (count($fields) !== $this->_bindValues($entity, $stmt, $fields)) {
            throw new Exception('Insuficient bound values');
        }
        
        $stmt->execute();
        $this->last_insert_id = $this->pdo->lastInsertId();
        
        if ($this->audit_enabled) {
            $this->auditInsert($entity);
        }
        
        return TRUE;
    }

    /**
     * Deletes an Entity<br>
     * The entity pk info must be set using Entity::setKeyValues
     * @param EntityBase $entity
     * @return boolean
     * @throws Exception
     */
    public function delete(EntityBase $entity) {
        $where = array();
        foreach ($entity->_getFields() as $field) {
            if (isset($field['primary']) && $field['primary'] && $entity->{$field['name']} != NULL) {
                $where[] = "`{$field['name']}` = :PRI__KEY__{$field['name']}";
            }
        }

        if (count($where) == 0) {
            throw new Exception('Os valores da chave primária da entidade não podem ser nulos/vazios');
        }

        $sql = sprintf("DELETE FROM `%s` WHERE (%s)", $entity->_getTable(), implode(' AND ', $where));
        $stmt = $this->pdo->prepare($sql);
        
        foreach ($entity->getKeyValues() as $field => $value) {
            $stmt->bindValue('PRI__KEY__' . $field, $value);
        }

        $stmt->execute();
        
        if ($this->audit_enabled) {
            $this->auditDelete($entity);
        }

        return TRUE;
    }

    /**
     * Get last insert id 
     * @return int
     */
    public function lastInsertId() {
        return $this->last_insert_id;
    }
    
    private function auditCheckType($value, $type) {
        switch (TRUE) {
            case strstr($type, 'int') !== FALSE:
            case strstr($type, 'decimal') !== FALSE:
            case strstr($type, 'float') !== FALSE:
            case strstr($type, 'double') !== FALSE:
                return $value * 1;
                
            default:
                return (string)$value;
        }
    }

    private function auditInsert(EntityBase $entity) {
        $pk = array();
        $auto = FALSE;
        
        foreach ($entity->_getFields() as $field) {
            if (isset($field['auto']) && $field['auto']) {
                $auto = $field['name'];
            }
            
            // mesmo que seja auto, caso o valor da chave primária esteja definida
            // consideramos o valor que está no objeto pois ele vai ser o valor
            // inserido no banco
            if (isset($field['primary']) && $field['primary'] === TRUE) {
                $pk[$field['name']] = $this->auditCheckType($entity->{$field['name']}, $field['type']);
            }
        }
        
        if ($auto !== FALSE && $entity->{$auto} == NULL) {
            // se é auto, 
            $id = $this->pdo->lastInsertId();
            $pk[$auto] = $id;
        }
        
        $table = $entity->_getTable();
        $json_pk = json_encode($pk);
        $json_data = json_encode($this->auditGetArray($entity));;
        
        $this->audit('INSERT', $json_pk, $json_data, $table);
    }
    
    private function auditUpdate(EntityBase $entity) {
        $pk = array();
        $current = $this->auditGetArray($entity);
        $original = $this->auditGetArray($entity, TRUE);
        $diff = [];
        
        foreach ($entity->_getFields() as $field) {
            if (isset($field['primary']) && $field['primary'] === TRUE) {
                $pk[$field['name']] = $this->auditCheckType($original[$field['name']], $field['type']);
            }
            
            if ($current[$field['name']] !== $original[$field['name']]) {
                $diff[$field['name']] = $current[$field['name']];
            }
        }
        
        $table = $entity->_getTable();
        $json_pk = json_encode($pk);
        $json_data = json_encode($diff);
        
        $this->audit('UPDATE', $json_pk, $json_data, $table);
    }
    
    private function auditDelete(EntityBase $entity) {
        $pk = array();
        $original = $this->auditGetArray($entity, TRUE);
        
        foreach ($entity->_getFields() as $field) {
            if (isset($field['primary']) && $field['primary'] === TRUE) {
                $pk[$field['name']] = $this->auditCheckType($original[$field['name']], $field['type']);
            }
        }
        
        $table = $entity->_getTable();
        $json_pk = json_encode($pk);
        $json_data = json_encode([]);
        
        $this->audit('DELETE', $json_pk, $json_data, $table);
    }
    
    private function audit($action, $pk, $data, $table) {
        /* @var $user User */
        $user = ServiceLocator::get('user');
        $user_id = $user->id;
     
        $now = Carbon::now();
        
        $audit = new AuditDatabase(new EntityManager($this->connection, FALSE));
        $audit->action = $action;
        $audit->dt = $now;
        $audit->pk = $pk;
        $audit->table = $table;
        $audit->user_id = $user_id;
        $audit->data = $data;
        $audit->insert();
        
        return $this->pdo->lastInsertId();
    }
    
    private function auditGetArray(EntityBase $entity, $original = FALSE) {
        $result = [];
        if ($original) {
            $data = $entity->_getOriginalValues();
        } else {
            $data = $entity;
        }

        foreach ($entity->_getFields() as $field) {
            switch (TRUE) {
                case $data->{$field['name']} === NULL:
                    $val = NULL;
                    break;

                case $data->{$field['name']} === '':
                    $val = '';
                    break;

                case (isset($field['datetime']) && $field['datetime']):
                    /* @var $date Carbon */
                    $date = $data->{$field['name']};
                    if (!($date instanceof Carbon)) {
                        throw new Exception('Deve fornecer um objeto Carbon para campos date, time ou datetime');
                    }

                    switch ($field['type']) {
                        case 'date':
                            $val = $date->format('Y-m-d');
                            break;

                        case 'time':
                            $val = $date->format('H:i:s');
                            break;

                        case 'datetime':
                            $val = $date->format('Y-m-d H:i:s');
                            break;
                    }
                    break;

                default:
                    $val = $data->{$field['name']};
            }
            
            $result[$field['name']] = $this->auditCheckType($val, $field['type']);
        }

        return $result;
    }
    
}
