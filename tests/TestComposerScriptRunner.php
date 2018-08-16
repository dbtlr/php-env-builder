<?php

namespace Dbtlr\PHPEnvBuilder\Tests;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Package\RootPackageInterface;
use Composer\Script\Event;
use Dbtlr\PHPEnvBuilder\Builder;
use Dbtlr\PHPEnvBuilder\ComposerScriptRunner;
use Mockery\MockInterface;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;

class TestComposerScriptRunner extends TestBase
{
    /** @var Composer|MockInterface */
    protected $composer;

    /** @var Event|MockInterface */
    protected $event;

    /** @var RootPackageInterface|MockInterface */
    protected $package;

    /** @var IOInterface|MockInterface */
    protected $io;

    /** @var Builder|MockInterface */
    protected $builder;

    /** @var vfsStreamDirectory */
    protected $root;

    protected function internalSetup()
    {
        $this->root = vfsStream::setup('var');
        $this->event = \Mockery::mock(Event::class);
        $this->composer = \Mockery::mock(Composer::class);
        $this->package = \Mockery::mock(RootPackageInterface::class);
        $this->io = \Mockery::mock(IOInterface::class);
        $this->builder = \Mockery::mock(Builder::class);
    }

    protected function getRunner($extra, $withIO = false)
    {
        $this->event->expects()->getComposer()->andReturns($this->composer);
        $this->composer->expects()->getPackage()->andReturns($this->package);
        $this->package->expects()->getExtra()->andReturns($extra);

        if ($withIO) {
            $this->event->expects()->getIO()->andReturns($this->io);
        }

        $runner = new ComposerScriptRunner($this->event);
        $runner->setBasePath($this->root->url());
        $runner->setBuilder($this->builder);

        return $runner;
    }

    /** @test */
    public function will_blowup_when_missing_base_extra()
    {
        $this->expectException(\InvalidArgumentException::class);

        $extra = [];
        $this->getRunner($extra);
    }

    /** @test */
    public function will_blowup_when_base_extra_not_array()
    {
        $this->expectException(\InvalidArgumentException::class);

        $extra = [
            'php-env-builder' => 'not-array'
        ];
        $this->getRunner($extra);
    }

    /** @test */
    public function will_blow_up_when_missing_questions()
    {
        $this->expectException(\InvalidArgumentException::class);

        $extra = [
            'php-env-builder' => [

            ]
        ];
        $this->getRunner($extra);
    }

    /** @test */
    public function will_blow_up_when_question_missing_name()
    {
        $this->expectException(\InvalidArgumentException::class);

        $extra = [
            'php-env-builder' => [
                'questions' => [
                    [
                        'prompt' => 'asd',
                    ]
                ]
            ]
        ];
        $runner = $this->getRunner($extra, true);
        $runner->run();
    }

    /** @test */
    public function will_blow_up_when_question_missing_prompt()
    {
        $this->expectException(\InvalidArgumentException::class);

        $extra = [
            'php-env-builder' => [
                'questions' => [
                    [
                        'name' => 'name',
                    ]
                ]
            ]
        ];
        $runner = $this->getRunner($extra, true);
        $runner->run();
    }

    /** @test */
    public function will_load_config()
    {
        $extra = [
            'php-env-builder' => [
                'loadEnv' => true,
                'clobber' => true,
                'verbose' => true,
                'envFile' => '.env',
                'questions' => [
                    [
                        'name' => 'name',
                        'prompt' => 'what is your name?',
                    ]
                ]
            ]
        ];
        $runner = $this->getRunner($extra, true);

        foreach ($extra['php-env-builder'] as $name => $value) {
            $this->assertSame($value, $runner->get($name), 'Key -> ' . $name);
        }
    }

    /** @test */
    public function will_get_full_path()
    {
        $extra = [
            'php-env-builder' => [
                'loadEnv' => false,
                'clobber' => false,
                'verbose' => false,
                'envFile' => '.env',
                'questions' => [
                    [
                        'name' => 'name',
                        'prompt' => 'what is your name?',
                    ]
                ]
            ]
        ];

        $runner = $this->getRunner($extra, true);
        $this->assertSame($this->root->url() . DIRECTORY_SEPARATOR . '.env', $runner->getEnvFile());
    }
}
