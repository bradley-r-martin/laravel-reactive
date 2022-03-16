<?php

namespace Sihq\Traits;

trait Authenticated
{

    public function __construct(){
        parent::__construct();
        if(!auth()->user()){
            $this->redirect('/');
        }
    }
    
}
