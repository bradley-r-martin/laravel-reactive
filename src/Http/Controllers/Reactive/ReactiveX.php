<?php
namespace Sihq\Reactive\Http\Controllers\Reactive;

use Sihq\Reactive\Facades\Payload;

class ReactiveX{

    protected $_debug = false;
    protected $_payloads = [];

    public function __construct(){
        $bundle = request()->payload;
        $this->_debug = (request()->header('x-debug') === "true");
     
        $payload = ($this->_debug ? $bundle : $this->decode($bundle));

        $this->_payloads = collect($payload)->map(function($payload){
            return new Payload((object) $payload);
        });
    }

    public function decode($bundle){
        return is_string($bundle) ? json_decode(base64_decode($bundle)) : null;
    }
    public function encode($bundle){
        return base64_encode(json_encode($bundle));
    }

    public function parse(){
      
        $states = $this->_payloads->map(function($payload){
            $controller = $payload->controller();
            if($payload->action() === 'onMount'){
                if(method_exists($controller,'onMount')){
                    $controller->onMount();
                }
            }else if($payload->action() === 'onRequest'){
                $event = $payload->event();
                if(method_exists($controller,$event)){
                    $controller->$event();
                }
            }
            if(method_exists($controller,'onDispatch')){
                $controller->onDispatch();
            }
          
           
            return [
                "controller"=> $controller ? get_class($controller) : null,
                "state" => optional($controller)->state() ?? []
            ];
        });

        return [
            "payload"=> $this->_debug ? $states : $this->encode($states)
        ];
    }

}