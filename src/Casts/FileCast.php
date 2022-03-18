<?php

namespace Sihq\Casts;

use Illuminate\Contracts\Database\Eloquent\Castable;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Support\Facades\Crypt;

use Sihq\Facades\File;

class FileCast implements Castable
{
    public static function castUsing(array $arguments)
    {
        return new class implements CastsAttributes {
            public function get($model, $key, $value, $attributes)
            {
                if ($value) {
                    return new File($value);
                }
                return new File();
            }

            public function set($model, $key, $value, $attributes)
            {
                if ($value) {
                    return (array) $value;
                }
                return null;
            }
        };
    }
}
