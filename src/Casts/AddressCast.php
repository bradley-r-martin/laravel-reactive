<?php

namespace Sihq\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class AddressCast implements CastsAttributes
{
    /**
     * Cast the given value.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string  $key
     * @param  mixed  $value
     * @param  array  $attributes
     * @return mixed
     */
    public function get($model, string $key, $value, array $attributes)
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

    /**
     * Prepare the given value for storage.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string  $key
     * @param  mixed  $value
     * @param  array  $attributes
     * @return mixed
     */
    public function set($model, string $key, $value, array $attributes)
    {
        $address = (object) $value;

        return json_encode($address);
    }
}
