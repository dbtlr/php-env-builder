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

        $this->event->allows()->getComposer()->andReturns($this->composer);
        $this->composer->allows()->getPackage()->andReturns($this->package);
        $this->event->allows()->getIO()->andReturns($this->io);
    }

    protected function getRunner($extra)
    {
        $this->package->allows()->getExtra()->andReturns($extra);

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
        $runner = $this->getRunner($extra);
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
        $runner = $this->getRunner($extra);
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
        $runner = $this->getRunner($extra);

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

        $runner = $this->getRunner($extra);
        $this->assertSame($this->root->url() . DIRECTORY_SEPARATOR . '.env', $runner->getEnvFile());
    }

    /** @test */
    public function will_ask_questions_and_write()
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
                        'prompt' => 'What is your name?',
                    ],
                    [
                        'name' => 'age',
                        'prompt' => 'How old are you?',
                    ]
                ]
            ]
        ];

        $this->builder->expects()->ask('name', 'What is your name?', '', false)->once();
        $this->builder->expects()->ask('age', 'How old are you?', '', false)->once();
        $this->builder->expects()->run()->once();
        $this->builder->expects()->write()->once();

        $runner = $this->getRunner($extra);
        $runner->run();

    }

    /** @test */
    public function will_run_when_file_exists_and_clobbering_time()
    {
        $extra = [
            'php-env-builder' => [
                'loadEnv' => false,
                'clobber' => true,
                'verbose' => false,
                'envFile' => '.env',
                'questions' => [
                    [
                        'name' => 'name',
                        'prompt' => 'What is your name?',
                    ],
                    [
                        'name' => 'age',
                        'prompt' => 'How old are you?',
                    ]
                ]
            ]
        ];

        // Make sure the .env file exists.
        vfsStream::newFile('.env')->at($this->root)->withContent('hi');

        // We don't write out anything this time.
        $this->io->expects()->write()->never();

        // All of this happens.
        $this->builder->expects()->ask('name', 'What is your name?', '', false)->once();
        $this->builder->expects()->ask('age', 'How old are you?', '', false)->once();
        $this->builder->expects()->run()->once();
        $this->builder->expects()->write()->once();

        $runner = $this->getRunner($extra);
        $runner->run();
    }

    /** @test */
    public function will_not_run_when_file_exists_and_not_clobbering_time()
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
                        'prompt' => 'What is your name?',
                    ],
                    [
                        'name' => 'age',
                        'prompt' => 'How old are you?',
                    ]
                ]
            ]
        ];

        // Make sure the .env file exists.
        vfsStream::newFile('.env')->at($this->root)->withContent('hi');

        // We should have written out a message saying the file exists.
        $this->io->expects('write')->once();

        // None of this happens this time.
        $this->builder->expects()->ask('name', 'What is your name?', '', false)->never();
        $this->builder->expects()->ask('age', 'How old are you?', '', false)->never();
        $this->builder->expects()->run()->never();
        $this->builder->expects()->write()->never();

        $runner = $this->getRunner($extra);
        $runner->run();
    }

    /** @test */
    public function static_build_should_run()
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
                        'prompt' => 'What is your name?',
                    ],
                    [
                        'name' => 'age',
                        'prompt' => 'How old are you?',
                    ]
                ]
            ]
        ];

        $this->package->allows()->getExtra()->andReturns($extra);

        // All of this happens.
        $this->builder->expects()->ask('name', 'What is your name?', '', false)->once();
        $this->builder->expects()->ask('age', 'How old are you?', '', false)->once();
        $this->builder->expects()->run()->once();
        $this->builder->expects()->write()->once();

        ComposerScriptRunner::build($this->event, $this->builder);
    }
}
