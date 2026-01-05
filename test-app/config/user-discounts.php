<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Discount Stacking Order
    |--------------------------------------------------------------------------
    |
    | Define the exact order in which discounts should be applied.
    | You can use discount 'code' strings or discount IDs.
    | Example: ['WELCOME20', 'NEWSLETTER10', 5]
    | Discounts not listed here will be applied last, ordered by highest percentage.
    |
    */
    'stacking_order' => [],

    /*
    |--------------------------------------------------------------------------
    | Maximum Total Discount Cap
    |--------------------------------------------------------------------------
    |
    | The maximum percentage (as decimal) of the subtotal that can be discounted
    | across all stacked discounts. 0.5 = 50%, 1.0 = 100%.
    |
    */
    'max_percentage_cap' => 0.50,

    /*
    |--------------------------------------------------------------------------
    | Rounding Precision
    |--------------------------------------------------------------------------
    |
    | Number of decimal places to round discount amounts to.
    |
    */
    'rounding_precision' => 2,

    /*
    |--------------------------------------------------------------------------
    | Usage Lock Timeout (seconds)
    |--------------------------------------------------------------------------
    |
    | How long to hold the row lock during apply() in case of slow processing.
    | Laravel's lockForUpdate() uses database defaults, but this can help with logging.
    |
    */
    'usage_lock_timeout' => 10,

];