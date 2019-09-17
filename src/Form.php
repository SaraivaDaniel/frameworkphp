<?php

namespace Saraiva\Framework;

use Carbon\Carbon;
use Exception;
use ReflectionClass;
use ReflectionException;
use ReflectionProperty;
use Respect\Validation\Validator as v;

abstract class Form {

    protected $_INPUT = array();
    protected $_ERROR = array();
    protected $last_error = '';

    /**
     * @param $input
     * @return array
     * @throws ReflectionException
     */
    public function process($input) {
        v::with('Saraiva\\Framwork\\Validation\\Rules');
        // salva input, pode ser necessário em outros casos
        $this->_INPUT = $input;

        $class = new ReflectionClass(get_called_class());
        $properties = $class->getProperties(ReflectionProperty::IS_PUBLIC);

        $rules = array();
        $erros = array();

        foreach ($properties as $property) {
            /* @var $property ReflectionProperty */
            $rules = $this->_parsePropertyRules($property);
            $this->_processProperty($property->name, $rules, $erros);
        }

        return $erros;
    }

    abstract public function execute(Database\Connection $connection);

    protected function _parsePropertyRules(ReflectionProperty $property) {
        $rules = array();
        $docs = str_replace("\r", "", $property->getDocComment());
        $docs = explode("\n", $docs);
        foreach ($docs as $doc) {
            $matches = array();
            if (preg_match_all('/@validate-([a-z\-]+)\s?(.*)/', $doc, $matches, PREG_PATTERN_ORDER)) {
                $val = trim($matches[2][0]);
                if (strtolower($val) === 'true') {
                    $val = TRUE;
                } elseif (strtolower($val) === 'false') {
                    $val = FALSE;
                }

                $rules[$matches[1][0]] = $val;
            }
            if (preg_match_all('/@transform-([a-z\-]+)/', $doc, $matches, PREG_PATTERN_ORDER)) {
                $rules[$matches[1][0]] = TRUE;
            }
        }

        if (!isset($rules['required'])) {
            $rules['required'] = FALSE;
        }

        return $rules;
    }

    protected function _processProperty($property, $rule, &$erros) {
        $original = isset($this->_INPUT[$property]) ? $this->_INPUT[$property] : NULL;

        if (isset($rule['array']) || is_array($original)) {
            $this->$property = array();
            if (isset($this->_INPUT[$property])) {
                if (!is_array($this->_INPUT[$property])) {
                    throw new Exception("Esperado $property = array");
                }

                foreach (array_keys($this->_INPUT[$property]) as $index) {
                    $this->$property[$index] = NULL;
                }
            }
        }

        // se tiver um método específico para processar a propriedade, executa ele
        // e depois prossegue com a validação
        // esse processo deve ser executado antes da verificação se o parâmetro foi
        // informado abaixo
        $process_method = '_process_' . $property;
        if (method_exists($this, $process_method)) {
            $this->$process_method();
        }

        // se não foi informado e é obrigatório, já retorna erro
        if (!isset($this->_INPUT[$property])) {
            if ($rule['required'] === TRUE) {
                $erros[$property] = isset($rule['msg']) ? $rule['msg'] : "Parâmetro obrigatório '$property' não informado";
                return;
            }
        }

        if (($original == NULL || (is_array($original) && count($original) === 0))) {
            if ($rule['required'] === FALSE) {
                // se tem valor default, preenche o valor

                if (isset($rule['default'])) {
                    if (is_array($original)) {
                        $original[] = $rule['default'];
                    } else {
                        $original = $rule['default'];
                    }
                } else {
                    // está vazio, não é obrigatório e não tem valor default definido]
                    return;
                }
            } else {
                $erros[$property] = isset($rule['msg']) ? $rule['msg'] : "Parâmetro obrigatório '$property' vazio";
                return;
            }
        }

        // processamento de array e não-array deve ser feito separado porque no caso de não-arrays, o ultimo parametro de _validateProperty DEVE ser NULL
        if (is_array($original)) {
            foreach ($original as $index => $value) {
                $this->_validateProperty($property, "{$property}[{$index}]", $value, $rule, $erros, $index);
            }
        } else {
            $this->_validateProperty($property, $property, $original, $rule, $erros, NULL);
        }
    }

    protected function _validateProperty($property, $key, $value, $rule, &$erros, $index) {
        $validation_method = $this->_getValidateMethod($rule, $property);

        if (!isset($rule['no-trim']) && is_string($value)) {
            $value = trim($value);
        }

        // validation method retorna FALSE se nao valida, ou o valor formatado interno mais proximo
        $this->last_error = '';
        $processed = $this->$validation_method($value, $rule, $index);

        if (FALSE === $processed && $validation_method !== '_validate_boolean') {
            if ($this->last_error != '') {
                $msg = $this->last_error;
            } else {
                $msg = isset($rule['msg']) ? $rule['msg'] : '';
            }
            $erros[$key] = $msg;
        } else {
            if ($index === NULL) {
                $this->$property = $processed;
            } else {
                $this->{$property}[$index] = $processed;
            }
        }
    }

    protected function _getValidateMethod($rule, $property) {
        if ($rule['rule'] == 'custom') {
            $validation_method = "_validate_custom_" . $property;
        } else {
            $validation_method = "_validate_" . $rule['rule'];
        }

        if (!method_exists($this, $validation_method)) {
            throw new Exception("Validacao $validation_method invalida" . print_r($property));
        }

        return $validation_method;
    }

    protected function _validate_string($value, $rule) {
        $min_size = isset($rule['min-size']) ? $rule['min-size'] : null;
        $max_size = isset($rule['max-size']) ? $rule['max-size'] : null;

        if ($rule['required'] !== 'true' && $value == '') {
            return NULL;
        }

        if (isset($rule['uppercase'])) {
            $value = mb_strtoupper($value, 'UTF-8');
        }

        $v = v::stringType()->length($min_size, $max_size);

        if (isset($rule['regex'])) {
            $v->regex($rule['regex']);
        }

        if (FALSE === $v->validate($value)) {
            return FALSE;
        } else {
            return $value;
        }
    }

    protected function _validate_date($value, $rule) {
        if (isset($rule['format'])) {
            $format = $rule['format'];
        } else {
            $format = Config::$config['Locale']['date'];
        }

        if (FALSE === v::date($format)->validate($value)) {
            if ($rule['required'] !== 'true') {
                return NULL;
            }

            return FALSE;
        } else {
            $return = Carbon::createFromFormat($format, $value);
            $return->startOfDay();
            return $return;
        }
    }

    protected function _validate_time($value, $rule) {
        if (isset($rule['format'])) {
            $format = $rule['format'];
        } else {
            $format = Config::$config['Locale']['time'];
        }

        if (FALSE === v::date($format)->validate($value)) {
            if ($rule['required'] !== 'true') {
                return NULL;
            }

            return FALSE;
        } else {
            $return = Carbon::createFromFormat($format, $value);
            $return->setDate(0, 1, 1);
            return $return;
        }
    }

    protected function _validate_custom($value, $rule) {
        $validation_method = "_validate_custom_$value";

        if (!method_exists($this, $validation_method)) {
            throw new Exception("Validacao custom $validation_method nao definida");
        }

        return $this->$validation_method($value, $rule);
    }

    protected function _validate_cpf($value, $rule) {
        if (FALSE === v::cpf()->validate($value)) {
            if ($rule['required'] !== 'true') {
                return NULL;
            }

            return FALSE;
        } else {
            $value = preg_replace('/[^0-9]/', '', $value);
            return $value;
        }
    }

    protected function _validate_int($value, $rule) {
        $v = v::intVal();

        if (isset($rule['min-range'])) {
            $v->min($rule['min-range'], TRUE);
        }

        if (isset($rule['max-range'])) {
            $v->max($rule['max-range'], TRUE);
        }

        if (FALSE === $v->validate($value)) {
            if ($rule['required'] !== 'true') {
                return NULL;
            }

            return FALSE;
        } else {
            return intval($value);
        }
    }

    /**
     * validos: 1 1. 1.0 .1
     * @param string $value
     * @param string[] $rules
     * @return boolean
     */
    protected function _validate_decimal($value, $rules) {
        $pattern = '/([0-9]?)?(\.)?([0-9]+)/';

        if (FALSE === v::regex($pattern)->validate($value)) {
            if ($rules['required'] !== 'true') {
                return NULL;
            }

            return FALSE;
        } else {
            return $value * 1.0;
        }
    }

    protected function _validate_boolean($value, $rules) {
        if (FALSE === v::boolVal()->validate($value)) {
            return FALSE;
        }

        return (bool) $value;
    }

    protected function _validate_numberformat($value, $rules) {
        $valid = Helper\InputHelper::validate_numberformat($value);

        if ($valid === FALSE) {
            if ($rules['required'] === 'true') {
                return FALSE;
            } else {
                return 0.0;
            }
        }

        if (isset($rules['min-range'])) {
            if ($valid < (float) $rules['min-range']) {
                if ($rules['required'] !== 'true') {
                    return NULL;
                }

                return FALSE;
            }
        }

        if (isset($rules['max-range'])) {
            if ($valid > (float) $rules['max-range']) {
                if ($rules['required'] !== 'true') {
                    return NULL;
                }

                return FALSE;
            }
        }

        return $valid;
    }

}
