<?php

namespace Dbtlr\PHPEnvBuilder\Tests;

use Dbtlr\PHPEnvBuilder\Builder;
use Dbtlr\PHPEnvBuilder\EnvLoader;
use Dbtlr\PHPEnvBuilder\Exception\ConfigurationException;
use Dbtlr\PHPEnvBuilder\IO;
use Dbtlr\PHPEnvBuilder\IOHandler\IOHandlerInterface;
use Mockery\MockInterface;

class TestBuilder extends TestBase
{
    /** @var IO|MockInterface */
    protected $io;

    /** @var EnvLoader|MockInterface */
    protected $loader;

    /** @var Builder */
    protected $builder;

    protected function internalSetup()
    {
        $this->loader = \Mockery::mock(EnvLoader::class);
        $this->io = \Mockery::mock(IO::class);
        $this->handler = \Mockery::mock(IOHandlerInterface::class);

        $this->builder = new Builder('/filename');
        $this->builder->setEnvLoader($this->loader);
        $this->builder->setIO($this->io);
    }


    /** @test */
    public function builder_will_construct_with_file_and_default_config()
    {
        $builder = new Builder('/file/name');
        $this->assertSame(['verbose' => false, 'loadEnv' => true], $builder->getConfig());
    }

    /** @test */
    public function builder_will_explode_when_given_non_booleans()
    {
        $this->expectException(ConfigurationException::class);

        new Builder('/file/name', ['verbose' => 'true']);
    }

    /** @test */
    public function builder_will_explode_when_given_unknown_config()
    {
        $this->expectException(ConfigurationException::class);

        new Builder('/file/name', ['unknown' => 'string']);
    }

    /** @test */
    public function ask_questions_will_set_as_array()
    {
        $builder = new Builder('/file/name');
        $questions = [
            'q1' => [
                'prompt' => 'What is your question?',
                'default' => 'something',
                'required' => false,
            ],
            'q2' => [
                'prompt' => 'Really?',
                'default' => 'yes!',
                'required' => true,
            ],
        ];

        foreach ($questions as $name => $q) {
            $builder->ask($name, $q['prompt'], $q['default'], $q['required']);
        }

        $this->assertSame($questions, $builder->getQuestions());
    }

    /** @test */
    public function run_will_load_env_by_default()
    {
        $this->loader->expects()->parse()->once();
        $this->builder->run();
    }

    /** @test */
    public function run_will_not_load_env_when_configured()
    {
        $this->loader->expects()->parse()->never();
        $this->builder->setConfig(['loadEnv' => false]);
        $this->builder->run();
    }

    /** @test */
    public function run_will_ask_questions()
    {
        $this->loader->expects()->parse()->andReturns([]);

        $this->io->expects()->ask('name', 'What is your name?', '', true)->once()->andReturns('bill');
        $this->io->expects()->ask('age', 'How old are you?', '18', false)->once()->andReturns('17');

        $this->builder->ask('name', 'What is your name?', '', true);
        $this->builder->ask('age', 'How old are you?', '18', false);

        $answers = $this->builder->run();
        $this->assertSame(['name' => 'bill', 'age' => '17'], $answers);
    }

    /** @test */
    public function run_will_ask_questions_and_use_current()
    {
        $this->loader->expects()->parse()->andReturns(['name' => 'jack', 'age' => '44']);

        $this->io->expects()->ask('name', 'What is your name?', 'jack', true)->once()->andReturns('bill');
        $this->io->expects()->ask('age', 'How old are you?', '44', false)->once()->andReturns('17');

        $this->builder->ask('name', 'What is your name?', '', true);
        $this->builder->ask('age', 'How old are you?', '18', false);

        $answers = $this->builder->run();
        $this->assertSame(['name' => 'bill', 'age' => '17'], $answers);
    }
}
