<?php

namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;

class Controller extends BaseController
{
    /**
     * Get validator.
     *
     * @param  array  $attributes
     * @param  array  $rules
     * @param  array  $messages
     * @param  array  $customAttributes
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function makeValidator(array $attributes,  array $rules, array $messages = [], array $customAttributes = [])
    {
        return $this->getValidationFactory()->make($attributes, $rules, $messages, $customAttributes);
    }
}
