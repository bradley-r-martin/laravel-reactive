<?php

namespace Sihq\Casts;
use Illuminate\Contracts\Database\Eloquent\Castable;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class AddressCast implements Castable
{
    public static function castUsing(array $arguments)
    {
        return new class implements CastsAttributes {
            public function get($model, $key, $value, $attributes)
            {
                if (is_null($value)) {
                    return (object) [
                        "__toString" => "",
                        "building_name" => "",
                        "country" => "",
                        "level" => "",
                        "postcode" => "",
                        "state" => "",
                        "street_name" => "",
                        "street_number" => "",
                        "street_type" => "",
                        "suburb" => "",
                        "unit" => "",
                    ];
                }
                return (object) json_decode($value);
            }

            public function set($model, $key, $value, $attributes)
            {
                $address = (object) $value;

                return json_encode($address);
            }
        };
    }
}