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
    
}
