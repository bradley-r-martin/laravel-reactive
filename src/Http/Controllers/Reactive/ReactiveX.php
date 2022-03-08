<?php
namespace Sihq\Reactive\Http\Controllers\Reactive;

use Sihq\Reactive\Facades\Payload;
use Illuminate\Support\Facades\Response;

class ReactiveX{

    protected $_debug = false;
    protected $_payloads = [];
    protected $_redirect_to = null;

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


       // This works but not great

        $this->_payloads->filter(function($payload){return $payload->action() === 'onRequest';})->map(function($payload){
            $controller = $payload->controller();
            try{
                $event = $payload->event();
                if(method_exists($controller,$event)){
                    $controller->$event();
                }
            }catch(\Illuminate\Http\Exceptions\HttpResponseException $e){
               $this->_redirect_to = $e->getResponse()->getTargetUrl();
            }
        });
    
        $states = $this->_payloads->map(function($payload){
            $controller = $payload->controller();
            if($payload->action() === 'onMount'){
                if(method_exists($controller,'onMount')){
                    $controller->onMount();
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

        return array_merge([
            "payload"=> $this->_debug ? $states : $this->encode($states)
        ],($this->_redirect_to ? ['redirect'=>  $this->_redirect_to] : []));
    }

}