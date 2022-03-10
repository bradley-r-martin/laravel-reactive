<?php
namespace Sihq\Reactive\Http\Controllers\Reactive;

use Sihq\Reactive\Facades\Payload;
use Sihq\Reactive\Facades\Thread;

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

       // Convert payloads into threads
       $threads = $this->_payloads->map(function($payload){
            return new Thread($payload);
       });

        // Run primary controllers event
        $threads->filter(function($thread){
            return $thread->payload()->action() === 'onRequest';
        })->map(function($thread){
            $event = $thread->payload()->event();
            if(method_exists($thread->controller(),$event)){
                $thread->execute(function() use($thread, $event){ $thread->controller()->$event(); });
            }
        });

        // run all controller onMount
        $threads->filter(function($thread){
            return $thread->payload()->action() === 'onMount';
        })->map(function($thread){
            $thread->execute(function() use($thread){ $thread->controller()->onMount(); });
        });

        // Run all controllers onDispatch
        $threads->map(function($thread){
            if(method_exists($thread->controller(),'onDispatch')){
                $thread->execute(function() use($thread){ $thread->controller()->onDispatch(); });
            }
        });

        $states = $threads->map(function($thread){
            return $thread->response();
        });

        $redirect = $states->filter(function($state){return !!$state['redirect']; })->map(function($state){return $state['redirect']; })->first();

        return array_merge([
            "payload"=> $this->_debug ? $states : $this->encode($states)
        ],($redirect ? ['redirect'=>  $redirect] : []));
    }

}