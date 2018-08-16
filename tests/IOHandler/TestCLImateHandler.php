<?php

namespace Dbtlr\PHPEnvBuilder\Tests\IOHandler;

use Dbtlr\PHPEnvBuilder\IOHandler\CLImateHandler;
use Dbtlr\PHPEnvBuilder\Tests\TestBase;
use League\CLImate\CLImate;
use League\CLImate\Util\Reader\ReaderInterface;
use Mockery\MockInterface;

class TestCLImateHandler extends TestBase
{
    /** @var CLImate|MockInterface */
    protected $bus;

    /** @var ReaderInterface|MockInterface */
    protected $prompt;

    /** @var CLImateHandler */
    protected $handler;

    protected function internalSetup()
    {
        $this->bus = \Mockery::mock(CLImate::class);
        $this->prompt = \Mockery::mock(ReaderInterface::class);
        $this->handler = new CLImateHandler($this->bus);
    }

    /** @test */
    public function will_output_message()
    {
        $this->bus->expects()->out('message me')->once();
        $this->handler->out('message me');
    }

    /** @test */
    public function will_input_question()
    {
        $this->bus->expects()->input('question')->once()->andReturn($this->prompt);
        $this->prompt->expects()->prompt()->once()->andReturns('answer');
        $answer = $this->handler->in('question');

        $this->assertEquals('answer', $answer);
    }

    /** @test */
    public function will_input_question_and_return_default()
    {
        $this->bus->expects()->input('question')->once()->andReturn($this->prompt);
        $this->prompt->expects()->prompt()->once()->andReturns('');
        $answer = $this->handler->in('question', 'default');

        $this->assertEquals('default', $answer);
    }
}
