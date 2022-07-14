<?php

namespace Ifullgaz\GlobalUniqueId\Models\Traits;

use Illuminate\Support\Facades\Redis;

/*
    Assigns a global unique id to the record
    before creation in the DB.

    The unique ID is a 64-bits integer number.
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
*/

const LUA_SCRIPT = "local ctr = redis.call('incr', KEYS[1])
if ctr > 65535 then
  ctr = 1
  redis.call('set', KEYS[1], ctr)
end
redis.call('expire', KEYS[1], 3600)
return ctr";

const REDIS_PID_KEY = 'GlobalUniqueId:pid';

const APCU_TTL = 3600; // 1 hour

trait GlobalUniqueId
{
    public static function getNextGlobalId() {
        // Get environment variables
        $start_date = config('globaluniqueid.start_date');
        $timestamp_size = config('globaluniqueid.timestamp_size');
        $machine_id_size = config('globaluniqueid.machine_id_size');
        $counter_size = config('globaluniqueid.counter_size');
        // Convert start date to timestamp in ms
        $start_timestamp = strtotime($start_date) * 1000;
        // Get current unix timestamp in ms
        $time = (int)(microtime(true) * 1000);
        // Get ms diff
        $time = $time - $start_timestamp;
        // Mask the top bits and keep only the timestamp_size bottom bits
        $time = $time & ((1 << $timestamp_size) - 1);
        // Shift bits left by machine_id_size + counter_size bits
        $time = $time << ($machine_id_size + $counter_size);
        // Get globally unique pid
        $pid = getmypid();
        $machineIdKey = "$pid:machineId";
        $counterKey = "$pid:counter";
        $machineId = apcu_fetch($machineIdKey);
        $max_counter_value = ((1 << $counter_size) - 1);
        if (!$machineId) {
            $maxMachineId = ((1 << $machine_id_size) - 1);
            try {
                $machineId = (int)Redis::evalSha(LUA_SCRIPT, 1, REDIS_PID_KEY);
                $machineId = $machineId & $maxMachineId;
            } catch (\Exception $e) {
                // Fall back to some random number
                $machineId = rand(0, $maxMachineId);
            }
            // Shift bits left by counter_size bits
            $machineId = $machineId << $counter_size;
            // Seed the local counter with some random value
            $counter = rand(0, $max_counter_value);
        } else {
            // Get local incrementing counter
            $counter = (int)apcu_fetch($counterKey);
            $counter = $counter + 1;
            $counter = $counter & $max_counter_value;
        }
        // Refresh the local machine id TTL
        apcu_store($machineIdKey, $machineId, APCU_TTL);
        // Store the last used counter
        apcu_store($counterKey, $counter, APCU_TTL);
        // Final date will be around 15/05/2159
        return $time + $machineId + $counter;
    }

    /**
     * Boot function from Laravel.
     */
    protected static function bootGlobalUniqueId()
    {
        // Hook to the creating event from Eloquent
        static::creating(function ($model)
        {
            $modelKeyName = $model->getKeyName();
            $modelKeyType = $model->getKeyType();
            if ($modelKeyName &&
                in_array($modelKeyType, ['int', 'integer']) &&
                empty($model->$modelKeyName)) {
                $model->$modelKeyName = static::getNextGlobalId();
            }
        });
    }

    /**
     * Get the value indicating whether the IDs are incrementing.
     *
     * @return bool
     */
    public function getIncrementing()
    {
        return false;
    }
}
