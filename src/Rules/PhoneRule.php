<?php

namespace Sihq\Reactive\Rules;

use Illuminate\Contracts\Validation\Rule;

class PhoneRule implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function validate($attribute, $value)
    {
        return $this->passes($attribute, $value);
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $phone = (object) $value;

        if (isset($phone->number) && isset($phone->country)) {
            $phoneUtil = \libphonenumber\PhoneNumberUtil::getInstance();
            try {
                $numberProto = $phoneUtil->parse($phone->number, $phone->country);
                $isValid = $phoneUtil->isValidNumber($numberProto);
                return $isValid;
            } catch (\libphonenumber\NumberParseException $e) {
                return false;
            }
        }
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return "The validation error message.";
    }
}
