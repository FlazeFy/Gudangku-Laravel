<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class ReportCategory implements Rule
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
        $type = ['Shopping Cart','Checkout','Wash List','Wishlist','Others'];

        foreach ($type as $format) {
            if ($format === $value) {
                return true;
            }
        }

        return false;
    }

    public function message()
    {
        return 'Report Category is not available';
    }
}