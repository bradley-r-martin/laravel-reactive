<?php

namespace Sihq\Casts;
use Illuminate\Contracts\Database\Eloquent\Castable;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;


class PhoneCast implements Castable
{
    public static function castUsing(array $arguments)
    {
        return new class implements CastsAttributes {
            public function get($model, $key, $value, $attributes)
            {
                if (is_null($value)) {
                    return (object) ["country" => "AU", "number" => ""];
                }
                return (object) json_decode($value);
            }

            public function set($model, $key, $value, $attributes)
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
        };
    }
}



