<?php

return [
    'points' => [
        'default_conversion_rate' => 1.0,
        'minimum_conversion_rate' => 0.1,
        'maximum_conversion_rate' => 10.0,
        'minimum_redemption_points' => 10000,
        'cash_pickup_location' => env('POINT_CASH_PICKUP_LOCATION', 'Loket Admin DLH Kota Madiun'),
    ],
];
