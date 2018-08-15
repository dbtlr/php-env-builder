<?php
namespace Dbtlr\PHPEnvBuilder\Tests;

use PHPUnit\Framework\TestCase;
use Mockery;

abstract class TestBase extends TestCase
{
    public function setUp()
    {
        $this->internalSetup();
    }

    public function tearDown()
    {
        Mockery::close();
    }

    /**
     * Override this to do your own setup.
     */
    protected function internalSetup()
    {
        // noop
    }
}
