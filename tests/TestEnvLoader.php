<?php

namespace Dbtlr\PHPEnvBuilder\Tests;

use Dbtlr\PHPEnvBuilder\EnvLoader;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use org\bovigo\vfs\vfsStreamFile;

class TestEnvLoader extends TestBase
{
    /** @var vfsStreamDirectory */
    protected $root;

    /** @var vfsStreamFile */
    protected $envFile;

    protected function internalSetup()
    {
        $env = <<< ENV
VAR1=value1
VAR2=value2
VAR3=value3
ENV;

        $this->root = vfsStream::setup('var');
        $this->envFile = vfsStream::newFile('.env')
            ->withContent($env)
            ->at($this->root);
    }

    /** @test */
    public function can_load_contents()
    {
        $loader = new EnvLoader($this->envFile->url());
        $data = $loader->parse();

        $this->assertArrayHasKey('VAR1', $data);
        $this->assertArrayHasKey('VAR2', $data);
        $this->assertArrayHasKey('VAR3', $data);
        $this->assertEquals('value1', $data['VAR1']);
        $this->assertEquals('value2', $data['VAR2']);
        $this->assertEquals('value3', $data['VAR3']);
    }

    /** @test */
    public function returns_empty_with_no_file()
    {
        $loader = new EnvLoader('.not-a-file');
        $data = $loader->parse();

        $this->assertEmpty($data);
    }
}
