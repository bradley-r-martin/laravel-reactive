<?php
namespace Sihq\Reactive\Http\Controllers\Reactive;

use Sihq\Reactive\Facades\Payload;

class ReactiveX{

    public function decode($bundle){
        return json_decode(base64_decode($bundle));
    }
    public function encode($bundle){
        return base64_encode(json_encode($bundle));
    }

    public function parse(){
        $bundle = request()->input();
        $debug = request()->header('debug');
        if(!$debug){
            $bundle = $this->decode($bundle);
        }
        $bundle = collect($bundle)->map(function($payload){
            return new Payload($payload);
        });

        dd($bundle);
        //  Accepts reactive schema payload and 
    }

}