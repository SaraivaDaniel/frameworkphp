<?php

namespace Saraiva\Framework;

use Carbon\Carbon;
use Exception;
use stdClass;

class EntityBase implements EntityManagerInterface {

    static $_TABLE;
    static $_FIELDS;
    static $_RELATIONSHIPS = array();
    protected $_KEY;

    /**
     *
     * @var EntityManager
     */
    protected $_EM;
    protected $_PRELOADED_RELATIONSHIPS = array();
    protected $_LOAD_RELATIONSHIPS = TRUE;
    private $calling_entity = NULL;
    private $original;

    public function __construct(EntityManager $em = NULL) {
        $this->_EM = $em;

        $this->original = new stdClass();
        foreach ($this->_getFields() as $field) {
            // inicializa o valor original da entidade como NULL
            // esse campo será usado para auditoria
            $this->original->{$field['name']} = NULL;
        }
    }

    public static function _getFields() {
        return static::$_FIELDS;
    }

    public static function _getTable() {
        return static::$_TABLE;
    }

    public function getKeyValues() {
        return $this->_KEY;
    }

    public function setKeyValues($key = array()) {
        $this->_KEY = $key;
    }

    public function disableLoadingOfRelationships() {
        $this->_LOAD_RELATIONSHIPS = FALSE;
    }

    public function setCallingEntity(EntityBase $entity = NULL) {
        $this->calling_entity = $entity;
    }

    public function insert() {
        if ($this->_EM instanceof EntityManager) {
            return $this->_EM->insert($this);
        } else {
            throw new Exception('EntityManager not set');
        }
    }

    public function update() {
        if ($this->_EM instanceof EntityManager) {
            $this->_EM->update($this);
        } else {
            throw new Exception('EntityManager not set');
        }
    }

    public function delete() {
        if ($this->_EM instanceof EntityManager) {
            $this->_EM->delete($this);
        } else {
            throw new Exception('EntityManager not set');
        }
    }

    public function _load($array, $prefix_table_name = FALSE) {
        $key = array();

        foreach ($this->_getFields() as $field) {
            $name = $field['name'];

            $array_name = '';
            if ($prefix_table_name) {
                $array_name .= strtolower($this->_getTable()) . '_';
            }
            $array_name .= $field['name'];

            // considerando que isset retorna FALSE quando:
            // - não existe a chave
            // - o valor da chave = NULL
            // em ambos os casos não é necessário processar o valor
            if (isset($array[$array_name])) {

                if (isset($field['datetime']) && $field['datetime'] == TRUE) {
                    // campos do tipo datetime devem ser NULL, zerados ou com uma data válida
                    // campos zerados são tratatos como NULL

                    if ($field['type'] == 'date') {
                        if ($array[$array_name] == '0000-00-00') {
                            $date = NULL;
                        } else {
                            $date = Carbon::createFromFormat('Y-m-d', $array[$array_name])->startOfDay();
                        }
                    } else if ($field['type'] == 'time') {
                        if ($array[$array_name] == '00:00:00') {
                            $date = NULL;
                        } else {
                            $date = Carbon::createFromFormat('H:i:s', $array[$array_name]);
                        }
                    } else {
                        // datetime
                        if ($array[$array_name] == '0000-00-00 00:00:00') {
                            $date = NULL;
                        } else {
                            $date = Carbon::createFromFormat('Y-m-d H:i:s', $array[$array_name]);
                        }
                    }

                    $this->$name = $date;
                    $this->original->{$field['name']} = $date;
                } else {
                    $this->$name = $array[$array_name];
                    $this->original->{$field['name']} = $array[$array_name];
                }

                if (isset($field['primary']) && $field['primary'] == TRUE) {
                    $key[$name] = $array[$array_name];
                }
            } else {
                $this->$name = NULL;
            }
        }

        $this->setKeyValues($key);
        if ($this->_LOAD_RELATIONSHIPS) {
            $this->_loadRelationships($array, $prefix_table_name);
        }
    }

    public function _getOriginalValues() {
        return $this->original;
    }

    public function validate($field) {
        if (method_exists($this, "_validate_$field")) {
            return call_user_func(array(get_called_class(), "_validate_$field"));
        } else {
            return TRUE;
        }
    }

    public function validateAll() {
        $erros = array();
        foreach (static::$_FIELDS as $field) {
            if (method_exists($this, "_validate_{$field['name']}")) {
                $erros[$field['name']] = call_user_func(array(get_called_class(), "_validate_{$field['name']}"));
            } else {
                $erros[$field['name']] = TRUE;
            }
        }
    }

    public function __get($name) {
        if (isset(static::$_RELATIONSHIPS[$name])) {
            return $this->$name();
        }

        throw new Exception("Undefined property $name");
    }

    public function __call($name, $args) {
        // verifica se a função se refere a uma relação e carrega relação
        if (isset(static::$_RELATIONSHIPS[$name])) {
            if (!isset($this->_PRELOADED_RELATIONSHIPS[$name])) {
                $this->_loadRelationshipFromProperties($name, static::$_RELATIONSHIPS[$name]);
            }
            return $this->_PRELOADED_RELATIONSHIPS[$name];
        }

        throw new Exception("Undefined method $name");
    }

    protected function _loadRelationships($array, $prefix_table_name) {
        foreach (static::$_RELATIONSHIPS as $name => $properties) {
            // verifica se é circular
            if ($this->verificaSeRelationshipCircular($properties)) {
                $this->_PRELOADED_RELATIONSHIPS[$name] = $this->calling_entity;
                continue;
            }

            // para carregar pelo array, tem que ter prefix
            $loaded = FALSE;
            if ($prefix_table_name) {
                $loaded = $this->_loadRelationshipFromArray($properties['entity'], $array);
            }

            // se não tem prefix ou não conseguir carregar pelo array
            if (!$prefix_table_name || !$loaded) {
                $this->_loadRelationshipFromProperties($name, $properties);
            }
        }
    }

    private function verificaSeRelationshipCircular($properties) {
        // se não tem nenhuma entidade relacionada, já retorna false
        if ($this->calling_entity === NULL) {
            return FALSE;
        }

        $foreign_classname = trim($properties['entity'], "\\");

        // se a classe é diferente, não pode ser a mesma
        if (get_class($this->calling_entity) !== $foreign_classname) {
            return FALSE;
        }

        if ($this->calling_entity->{$properties['foreign_key']} !== $this->{$properties['local_key']}) {
            return FALSE;
        }

        return TRUE;
    }

    protected function _loadRelationshipFromProperties($name, $properties) {
        $repo_class = str_replace("Entity", "Repository", $properties['entity']);
        /* @var $repo Repository */
        $repo = new $repo_class($this->_EM->connection);
        $repo->setCallingEntity($this);

        $result = $repo->getByFieldValue($properties['foreign_key'], $this->{$properties['local_key']});

        $obj = NULL;
        if ($properties['type'] == 'hasOne' or $properties['type'] == 'belongsTo' && $result->count() > 0) {
            $obj = $result->current();
        } elseif ($properties['type'] == 'hasMany') {
            $obj = $result;
        }

        $this->_PRELOADED_RELATIONSHIPS[$name] = $obj;
        return TRUE;
    }

    /**
     * Carrega relacionamentos de um array 
     * Os campos tem que estar prefixados com o nome da tabela para evitar colisão com a tabela master
     * @param string $relationship
     * @param string[] $array
     * @return boolean
     */
    protected function _loadRelationshipFromArray($relationship, $array) {
        // dependencia é uma entity e precisa passar EntityManager para o construtor
        /* @var $obj EntityBase */
        $obj = new $relationship($this->_EM);
        $obj->setCallingEntity($this);

        // verifica se todos campos estão setados
        foreach ($obj->_getFields() as $field) {
            $name = strtolower($obj->_getTable()) . '_' . $field['name'];

            if (!isset($array[$name])) {
                return FALSE;
            }
        }

        $obj->_load($array, TRUE);
        $parts = explode('\\', $relationship);
        $name = strtolower(array_pop($parts));
        $this->_PRELOADED_RELATIONSHIPS[$name] = $obj;
        return TRUE;
    }

    public function getEntityManager() {
        return $this->_EM;
    }

}
