<?php

namespace DevsNG\Interswitch\Test;

use Mockery as m;
use PHPUnit\Framework\TestCase;
use DevsNG\Interswitch\Interswitch;

class InterswitchTest extends TestCase
{
    protected $app;

    public function setUp()
    {
        $this->app = m::mock(DevsNG\Interswitch\Interswitch::class);
    }

    public function tearDown()
    {
        m::close();
    }

    /** @test */
    public function canOutputRaw()
    {
        $array = $this->app->shouldReceive('raw')
                           ->andReturn();

        $this->assertEquals('array', gettype(array($array)));
    }

}
