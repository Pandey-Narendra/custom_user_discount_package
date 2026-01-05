<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Discount Stacking Order
    |--------------------------------------------------------------------------
    | Define the exact order discounts should be applied by discount code.
    | Example: ['WELCOME20', 'NEWSLETTER10']
    */
    'stacking_order' => [],

    /*
    |--------------------------------------------------------------------------
    | Maximum Total Discount Cap
    |--------------------------------------------------------------------------
    | Maximum percentage of subtotal that can be discounted (1.0 = 100%)
    */
    'max_percentage_cap' => 0.50, // 50%

    /*
    |--------------------------------------------------------------------------
    | Rounding Precision
    |--------------------------------------------------------------------------
    */
    'rounding_precision' => 2,
];