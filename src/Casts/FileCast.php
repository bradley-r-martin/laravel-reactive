<?php

namespace Sihq\Casts;

use Illuminate\Contracts\Database\Eloquent\Castable;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Support\Facades\Crypt;

use Sihq\Models\File;

class FileCast implements Castable
{
    public static function castUsing(array $arguments)
    {
        return new class implements CastsAttributes {
            public function get($model, $key, $value, $attributes)
            {
                if ($value) {
                    return File::find($value) ?? new File();
                }
                return new File();
            }

            public function set($model, $key, $value, $attributes)
            {
                if ($value && $value->exists) {
                    return $value->id;
                }
                return null;
            }
        };
    }
}
