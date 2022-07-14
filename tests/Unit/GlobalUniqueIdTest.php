<?php

namespace Ifullgaz\GlobalUniqueId\Tests\Unit;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Ifullgaz\GlobalUniqueId\Tests\TestCase;
use Ifullgaz\GlobalUniqueId\Models\Traits\GlobalUniqueId;
use Illuminate\Support\Facades\DB;
use Mockery;

class Dummy extends Model
{
    use GlobalUniqueId;
}

class GlobalUniqueIdTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function model_has_id()
    {
        $id = Dummy::getNextGlobalId();
        $this->assertGreaterThan(0, $id);
    }
}