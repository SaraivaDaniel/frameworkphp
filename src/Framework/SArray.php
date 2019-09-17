<?php

namespace Saraiva\Framework\Framework;

use ArrayAccess;
use Countable;

class SArray implements ArrayAccess, Countable {
    
    protected $container = array();
    
    public function offsetExists($offset) {
        return isset($this->container[$offset]);
    }

    public function offsetGet($offset) {
        return isset($this->container[$offset]) ? $this->container[$offset] : null;
    }

    public function offsetSet($offset, $value) {
        if (is_null($offset)) {
            $this->container[] = $value;
        } else {
            $this->container[$offset] = $value;
        }
    }

    public function offsetUnset($offset) {
        unset($this->container[$offset]);
    }
    
    public function merge($other_array) {
        $this->container = array_merge($this->container, $other_array);
    }

    public function count() {
        return count($this->container);
    }
    
    public function getContainer() {
        return $this->container;
    }

}