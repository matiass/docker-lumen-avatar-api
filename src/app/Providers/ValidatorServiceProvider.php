<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Extension\ValidatorExtension;

class ValidatorServiceProvider extends ServiceProvider
{
    public function register()
    {
    }

    public function boot()
    {
        $this->app->validator->resolver(function (
            $translator,
            $data,
            $rules,
            $messages = array(),
            $customAttributes = array()
        ) {
            return new ValidatorExtension($translator, $data, $rules, $messages, $customAttributes);
        });
    }

}