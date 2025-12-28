<?php

namespace App\Rules;
use Illuminate\Contracts\Validation\Rule;

class DictionaryType implements Rule
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

    public function passes($attribute, $value)
    {
        $type = ['inventory_category','inventory_unit','inventory_room','reminder_type','reminder_context','report_category'];

        foreach ($type as $format) {
            if ($format === $value) {
                return true;
            }
        }

        return false;
    }

    public function message()
    {
        return 'Dictionary Type is not available';
    }
}