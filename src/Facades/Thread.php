<?php
namespace Sihq\Reactive\Facades;

class Thread{

    protected $_payload;
    protected $_controller;
    protected $_response = [];
    protected $_exceptions = [];
    protected $_redirect = null;

    public function __construct($payload){
        $this->_payload = $payload;
        $this->_controller = $payload->controller();
    }

    public function execute($func){
        try{
            if($func){
                $func();
            }
        }catch(\Illuminate\Validation\ValidationException $e){
           $this->_exceptions = array_merge($this->_exceptions, $e->validator->messages()->getMessages());
        }
        catch(\Illuminate\Http\Exceptions\HttpResponseException $e){
            $this->_redirect = $e->getResponse()->getTargetUrl();
        }
    }

    public function payload(){
        return $this->_payload;
    }
    public function controller(){
        return $this->_controller;
    }

    public function exceptions(){
        return $this->_exceptions;
    }

    public function response(){
        return [
            "controller"=> $this->controller() ? get_class($this->controller()) : null,
            "state" => optional($this->controller())->state() ?? [],
            "exceptions"=> $this->exceptions(),
            "redirect" => $this->_redirect
        ];
    }

}