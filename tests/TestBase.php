<?php
namespace Dbtlr\PHPEnvBuilder\Tests;

use PHPUnit\Framework\TestCase;
use Mockery;

abstract class TestBase extends TestCase
{
    public static $functions;

    public function setUp()
    {
        self::$functions = Mockery::mock();

        if (method_exists($this, 'internalSetup')) {
            $this->internalSetup();
        }
    }

    public function tearDown()
    {
        Mockery::close();
    }
}
