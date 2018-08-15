<?php

namespace Dbtlr\PHPEnvBuilder\Tests;

use Dbtlr\PHPEnvBuilder\EnvBuilder;

class TestEnvBuilder extends TestBase
{
    /** @test */
    public function should_build_with_env()
    {
        $builder = new EnvBuilder(['env' => '.env']);
        $this->assertInstanceOf('\Dbtlr\PHPEnvBuilder\EnvBuilder', $builder);
    }

    /** @test */
    public function should_blowup_while_building_with_no_env()
    {
        $this->expectException('\Dbtlr\PHPEnvBuilder\ConfigurationException');
        new EnvBuilder();
    }
}
