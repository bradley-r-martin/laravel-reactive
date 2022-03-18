<?php

namespace Sihq\Traits;
use Sihq\Facades\Payload;

trait Authenticated
{

    public function __construct(Payload $payload){
        parent::__construct($payload);
        if(!auth()->user()){
            $this->redirect('/');
        }
    }

    public function authorise($action, $model = null){
        if(auth()->user()->cannot($action,$model)){
            $this->redirect('/');
        }
    }
    
}
