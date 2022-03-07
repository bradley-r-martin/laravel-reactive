<?php
namespace Sihq\Reactive\Facades;

class Payload{

    protected $_controller;
    protected $_event = null;
    protected $_action = null;
    protected $_state = [];
    protected $_props = []; 


    public function __construct($payload){
        $this->_controller = optional($payload)->controller;
        $this->_action = optional($payload)->action;
        $this->_event = optional($payload)->event;
        $this->_state = optional($payload)->state;
        $this->_props = optional($payload)->props;
    }

    // return state
    public function state(){
        return (array) $this->_state;
    }

    // return props
    public function props(){
        return (object) $this->_props;
    }

    public function action(){
        return $this->_action;
    }

    public function event(){
        return $this->_event;
    }

    // return controller;
    public function controller(){
        if(class_exists($this->_controller)){
            $controller_name = $this->_controller;
            return new $controller_name($this);
        }
        return null;
    }

}