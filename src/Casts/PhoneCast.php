<?php

namespace Sihq\Reactive\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class PhoneCast implements CastsAttributes
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
            return (object) ["country" => "AU", "number" => ""];
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
        $phone = (object) $value;
        if (isset($value->number) && isset($value->country)) {
            $phoneUtil = \libphonenumber\PhoneNumberUtil::getInstance();
            try {
                $numberProto = $phoneUtil->parse($value->number, $value->country);
                $isValid = $phoneUtil->isValidNumber($numberProto);
                if ($isValid) {
                    $phone->number = $numberProto->getNationalNumber();
                    $phone->prased = $phoneUtil->format($numberProto, \libphonenumber\PhoneNumberFormat::NATIONAL);
                }
            } catch (\libphonenumber\NumberParseException $e) {
            }
        }

        return json_encode($phone);
    }
}
