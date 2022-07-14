<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Start date
    |--------------------------------------------------------------------------
    |
    | Starting reference date. Should be before today.
    |
    */
    'start_date' => env('GLOBAL_UNIQUE_ID_START_DATE', '01/01/2022'),

    /*
    |--------------------------------------------------------------------------
    | Timestamp size
    |--------------------------------------------------------------------------
    |
    | Number of bits to use for the timestamp.
    | The more bits used, the larger the date range
    | eg: 42 bits -> ~140 years, 41 bits -> ~70 years...
    |
    */
    'timestamp_size' => env('GLOBAL_UNIQUE_ID_TIMESTAMP_SIZE', 42),

    /*
    |--------------------------------------------------------------------------
    | Machine id size
    |--------------------------------------------------------------------------
    |
    | Number of bits to use for the machine id.
    | The more bits used, the more PHP processes can run concurrently.
    | eg: 11 bits -> 2^11 (2048) concurrent processes
    |
    */
    'machine_id_size' => env('GLOBAL_UNIQUE_ID_MACHINE_ID_SIZE', 11),

    /*
    |--------------------------------------------------------------------------
    | Counter size
    |--------------------------------------------------------------------------
    |
    | Number of bits to use for the local counter.
    | The more bits used, the higher the local counter can go.
    | eg: 10 bits -> 2^10 (1024) values
    |
    */
    'counter_size' => env('GLOBAL_UNIQUE_ID_COUNTER_SIZE', 10),
];
