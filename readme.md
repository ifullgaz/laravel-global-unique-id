## Laravel GlobalUniqueId
![Unit Tests](https://github.com/ifullgaz/laravel-global-unique-id/workflows/CI/badge.svg)
[![License](https://poser.pugx.org/ifullgaz/laravel-global-unique-id/license)](http://choosealicense.com/licenses/mit/)
[![Latest Stable Version](https://poser.pugx.org/ifullgaz/laravel-global-unique-id/v)](https://packagist.org/packages/ifullgaz/laravel-global-unique-id)
[![Latest Unstable Version](https://poser.pugx.org/ifullgaz/laravel-global-unique-id/v/unstable)](https://packagist.org/packages/ifullgaz/laravel-global-unique-id)
[![PHP Version Require](https://poser.pugx.org/ifullgaz/laravel-global-unique-id/require/php)](https://packagist.org/packages/ifullgaz/laravel-global-unique-id)
[![Total Downloads](https://poser.pugx.org/ifullgaz/laravel-global-unique-id/downloads)](https://packagist.org/packages/ifullgaz/laravel-global-unique-id)

Trait to generate 64 bit globally unique ids for all running PHP processes.
The unique ID is a 64-bits integer number.
Ids are guaranteed to be time ordered.
The content of the integer is as follows, for example:

    63 62 ------------------------------------------ 21 20 ------------ 9 8 ---------- 0
     0           42 bits time in milliseonds                 12 bits          9 bits
                140 years until rollover to 0           unique machine id  local counter

Note: bit 63 must be 0 since PHP doesn't support unsigned integers.

In this example, each PHP process can create up to 512 new ids per millisecond
before any collision occurs.
A total of 2,097,152 new ids could be created per milliseconds in this system.

Note: JS cannot process integers larger than 2^53-1 at this time.
A good compromise is to use 42-6-5 bits as values, allowing for 64 * 32 = 2048
new ids per milliseconds. In case this is not appropriate (not enough ids/sec),
the following snippet may be used to parse the raw response before deserialization:

    function (data) {
        data = data.replace(/:\s*(-?\d+),/g, (match, $1) => {
            if ($1 <= Number.MAX_SAFE_INTEGER) {
                return match;
            }
            return ':"'+$1+'",';
        });
        return data;
    }

## Requirements

This package requires Laravel 9 and above, apcu and redis php extensions and a redis server.

## Installation

Require this package with composer.

```shell
composer require ifullgaz/laravel-global-unique-id
```

Laravel uses Package Auto-Discovery, so doesn't require you to manually add the ServiceProvider.

### Laravel without auto-discovery:

If you don't use auto-discovery, add the ServiceProvider to the providers array in config/app.php

```php
Ifullgaz\GlobalUniqueId\ServiceProvider::class,
```

Copy the package config to your local config with the publish command:

```shell
php artisan vendor:publish --tag=globaluniqueid-config
```

## Options

The following options may be configured in the the globaluniqueid.php file:

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

## Usage

Add the trait to a class that has a numerical primary key as such:

```php
use Ifullgaz\GlobalUniqueId\Models\Traits\GlobalUniqueId;

class MyClass extends Model
{
    use GlobalUniqueId;
}
```
