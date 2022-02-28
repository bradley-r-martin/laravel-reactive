<?php
namespace Sihq\Reactive\Facades;

class Payload{

    protected $_controller;
    protected $_state = [];
    protected $_props = []; 


    public function __construct($payload){
        $this->_controller = optional($payload)['controller'];
        $this->_state = optional($payload)['state'];
        $this->_props = optional($payload)['props'];
    }

    // return state
    public function state(){
        return (object) $this->_state;
    }

    // return props
    public function props(){
        return (object) $this->_props;
    }

    // return controller;
    public function controller(){
        return new $this->_controller($this);
    }

}