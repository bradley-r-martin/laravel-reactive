<?php

namespace Sihq\Reactive\Http\Controllers\Reactive;

use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\BroadcastsEvents;

class Reactive{
    protected $protected = ['protected','rules','subscribe','route'];


    public function redirect($to){
        throw new \Illuminate\Http\Exceptions\HttpResponseException(redirect($to));
    }
    public function subscriptions(){
        return array_merge($this->subscribableModels()->toArray(), optional($this)->subscribe ?? []);
    }
    protected function subscribableModels(){
        // Identifies subscribable models for frontend
        return $this->controllerProperties()->map(function($property,$key){
            $subscription = null;
            try{
                // If property is a model and impliments the BroadcastEvents trait.
                if (is_a($this->{$key}, Model::class) && in_array(BroadcastsEvents::class, class_uses($this->{$key}))) {
                    $type = optional((new \ReflectionProperty($this, $key))->getType())->getName();
                    $key = optional($this->{$key})->{optional($this->{$key})->getKeyName()};
                    if($key){
                        $subscription = str_replace('\\','.',$type).".$key";
                    }
                }
            }catch (\Exception $e){}
            return $subscription;
        })->whereNotNull()->flatten();
    }

    protected function controllerProperties(){
        // Returns a list of controller properties
        return collect(get_object_vars($this))->except($this->protected);
    }

    public function dehydrate(){
       
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
        })->except($this->protected)->toArray();
        return $variables;
    }

    public function hydrate($state = null){
        $state = $state ?? request()->state ?? [];
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

    public function validate(){
        $validator = Validator::make($this->dehydrate(), $this->rules)->validate();
    }

    public function onRender(){

    }


    public static function route(){
        $controller_name = request()->controller;
        $controller = new $controller_name();
        $controller->hydrate();
        $action = request()->action;
        if(method_exists($controller, $action)){
            $controller->$action();
        }
        $controller->onRender();
        $state = $controller->dehydrate();
        return [
            'subscriptions'=> $controller->subscriptions(),
            'state'=> $state,
            'version'=> '1234'
        ];
    }

}