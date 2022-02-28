<?php
namespace Sihq\Reactive\Http\Controllers\Reactive;

use Sihq\Reactive\Facades\Payload;

class ReactiveX{

    protected $_payloads = [];

    public function __construct(){
        $bundle = request()->payload;
        $debug = request()->header('debug');
        $this->_payloads = collect($debug ? $bundle : $this->decode($bundle))->map(function($payload){
            return new Payload($payload);
        });
    }

    public function decode($bundle){
        return json_decode(base64_decode($bundle));
    }
    public function encode($bundle){
        return base64_encode(json_encode($bundle));
    }

    public function parse(){
 
        dd($this->_payloads);
        //  Accepts reactive schema payload and 
    }

}