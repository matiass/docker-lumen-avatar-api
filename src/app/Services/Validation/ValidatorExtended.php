<?php

namespace App\Services\Validation;

use Illuminate\Validation\Validator as IlluminateValidator;
use Illuminate\Support\Str;

class ValidatorExtended extends IlluminateValidator
{

    /**
     * The failed validation rules verbose.
     *
     * @var array
     */
    protected $failedRulesVerbose = [];

    public function __construct($translator, $data, $rules, $messages = array(), $customAttributes = array())
    {
        parent::__construct($translator, $data, $rules, $messages, $customAttributes);
        $this->setUpExtended();
    }

    /**
     * Setup any customizations
     *
     * @return void
     */
    protected function setUpExtended()
    {
        $rules = config('validator.rules');
        $this->setCustomMessages($rules);
    }

    /**
     * Determine if the field value is 404
     *
     * @param string $attribute
     * @param mixed $value
     * @return bool
     */
    protected function validateV404($attribute, $value)
    {
        return '404' == $value || 404 == $value;
    }

    /**
     * Determine if the field value is blank
     *
     * @param string $attribute
     * @param mixed $value
     * @return bool
     */
    protected function validateBlank($attribute, $value)
    {
        return strcasecmp($value, 'blank') == 0;
    }

    /**
     * Determine if the field value is a hex color
     *
     * @param string $attribute
     * @param mixed $value
     * @return bool
     */
    protected function validateHexColor($attribute, $value)
    {
        return (bool)preg_match("/#([a-f0-9]{3}){1,2}\b/i", $value);
    }

    /**
     * Determine if the field is a default
     *
     * @param string $attribute
     * @param mixed $value
     * @return bool
     */
    protected function validateDefaults($attribute, $value)
    {
        return $this->validateUrlEncoded($attribute, $value)
        || $this->validateV404($attribute, $value)
        || $this->validateBlank($attribute, $value)
        || $this->validateHexColor($attribute, $value);
    }

    /**
     * Validate that an attribute is an active URL.
     *
     * @param  string $attribute
     * @param  mixed $value
     * @return bool
     */
    protected function validateUrlEncoded($attribute, $value)
    {
        return $this->validateActiveUrl($attribute, $value);
    }

    /**
     * Add a failed rule and error message to the collection.
     *
     * @param  string $attribute
     * @param  string $rule
     * @param  array $parameters
     * @return void
     */
    protected function addFailure($attribute, $rule, $parameters)
    {
        $this->addError($attribute, $rule, $parameters);

        $message = $this->getMessage($attribute, $rule);

        $message = $this->doReplacements($message, $attribute, $rule, $parameters);

        $this->failedRules += $this->formatParameters($parameters, $message);

        $this->failedRulesVerbose[$attribute][$rule] = $this->formatParameters($parameters, $message);
    }

    /**
     * Format parameteres.
     *
     * @param  array $parameteres
     * @param  mixed $message
     * @return array
     */
    protected function formatParameters($parameteres, $message)
    {
        $custom = (bool)($parameteres['link'] ?? 0);
        if (!$custom) {
            $unknown = $this->getCustomMessages()['unknown'];
            $parameteres['link'] = $unknown['link'];
            $parameteres['code'] = $unknown['code'];
        }
        return [
            'code' => $parameteres['code'],
            'message' => $parameteres['message'] ?? $message['message'] ?? $message,
            'link' => $parameteres['link'],
        ];
    }

    /**
     * Add an error message to the validator's collection of messages.
     *
     * @param  string $attribute
     * @param  string $rule
     * @param  array $parameters
     * @return void
     */
    protected function addError($attribute, $rule, $parameters)
    {
        $message = $this->getMessage($attribute, $rule);

        $message = $this->doReplacements($message, $attribute, $rule, $parameters);

        $this->messages->add($rule, $message);
    }

    /**
     * Get the failed validation rules.
     *
     * @return array
     */
    public function failedVerbose()
    {
        return $this->failedRulesVerbose;
    }

    /**
     * Get errors in array format
     *
     * @return array
     */
    public function errorsArray()
    {
        return collect($this->errors()->toArray())->keyBy(function ($value, $key) {
            return Str::snake($key);
        })->toArray();
    }
//
//    /**
//     * Get the inline message for a rule if it exists.
//     *
//     * @param  string  $attribute
//     * @param  string  $lowerRule
//     * @param  array   $source
//     * @return string|null
//     */
//    protected function getInlineMessage($attribute, $lowerRule, $source = null)
//    {
//        $source = $source ?: $this->customMessages;
//
//        $keys = ["{$attribute}.{$lowerRule}", $lowerRule];
//        // First we will check for a custom message for an attribute specific rule
//        // message for the fields, then we will check for a general custom line
//        // that is not attribute specific. If we find either we'll return it.
//        foreach ($keys as $key) {
//            foreach (array_keys($source) as $sourceKey) {
//                if (!empty($sourceKey) && Str::is($sourceKey, $key)) {
//                    return $source[$sourceKey];
//                }
//            }
//        }
//    }
}