<?php
namespace Sihq\Reactive\Facades;

use Sihq\Reactive\Facades\Payload;

use Illuminate\Support\Facades\Validator;

class Controller{
    protected Payload $_payload;

    public function __construct(Payload $Paylod){
        $this->_payload = $payload;
        $this->hydrate();
    }

    // Return component props
    public function props(){
        return $this->_payload->props();
    }

    // Return component state
    public function state(){
        return $this->dehydrate();
    }

    // validate state
    public function validate(){
        $validator = Validator::make($this->state(), optional($this)->rules ?? [])->validate();
    }

    // Hydrate the payload state into the controller
    protected function hydrate(){
        $state = $this->_payload->state();
        foreach($state as $key=>$value){
            $type = null;
            try{
                $type = optional((new \ReflectionProperty($this, $key))->getType())->getName();
            }catch(\Exception $e){}
            if($type){
                $model_key = optional(new $type)->getKeyName();
                if(optional($value)[$model_key]){
                    $model = (new $type)->find(optional($value)[$model_key]);
                    if($model && !is_null($value)){
                        $model->fill($value);
                        $this->{$key} = $model;
                    }else{
                        $this->{$key} = (new $type)->fill($value ?? []);
                    }
                   
                }else{
                    $this->{$key} = (new $type)->fill($value ?? []);
                }
             
            }else{
                $this->{$key} = $value;
            }
        }
    }
    // dehydate the controller state
    protected function dehydrate(){
        $protected = ['_payload','rules'];
        $variables = get_object_vars($this);
        $variables = collect($variables)->map(function($variable,$property){
            if(is_string($variable) || is_bool($variable)){
                return $variable;
            }else  if(is_object($variable)){
                $type = null;
                try{
                    $type = optional((new \ReflectionProperty($this, $property))->getType())->getName();
                }catch(\Exception $e){}

                $object = collect($variable)->toArray();
                return (count($object) > 0 ?   $object : null);
            }else if(is_array($variable)){
                return $variable;
            }
        })->except($protected)->toArray();
        return $variables;
    }

}