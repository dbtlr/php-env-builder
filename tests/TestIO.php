<?php

namespace Dbtlr\PHPEnvBuilder\Tests;

use Dbtlr\PHPEnvBuilder\Exception\AskException;
use Dbtlr\PHPEnvBuilder\IO;
use Dbtlr\PHPEnvBuilder\IOHandler\IOHandlerInterface;
use Mockery\MockInterface;

class TestIO extends TestBase
{
    /** @var IOHandlerInterface|MockInterface */
    protected $ioHandler;

    /** @var IO */
    protected $io;

    protected function internalSetup()
    {
        $this->ioHandler = \Mockery::spy(IOHandlerInterface::class);
        $this->io = new IO($this->ioHandler);
    }

    /** @test */
    public function will_output_message_to_handler()
    {
        $this->io->out('hello');
        $this->ioHandler->shouldHaveReceived('out')->with('hello')->once();
    }

    /** @test */
    public function will_input_message_to_handler()
    {
        $this->ioHandler->allows()->in('hello ():', '')
            ->andReturns('hi');

        $response = $this->io->in('hello');

        $this->assertEquals('hi', $response);
    }

    /** @test */
    public function can_ask_question()
    {
        $this->ioHandler->allows()->in('hello ():', '')
            ->andReturns('world');

        $response = $this->io->ask('greeting', 'hello');

        $this->assertEquals('world', $response);
    }

    /** @test */
    public function will_reask_question_without_answer()
    {
        $this->ioHandler->expects()->in('hello ():', '')->twice()->andReturns('', 'okay');
        $this->ioHandler->expects()->out('A response is required...')->once();
        $response = $this->io->ask('greeting', 'hello', '', true);

        $this->assertEquals('okay', $response);
    }

    /** @test */
    public function will_reask_question_without_answer_3_times()
    {
        $this->ioHandler->expects()->in('hello ():', '')->times(3)->andReturns('', '', 'okay');
        $this->ioHandler->expects()->out('A response is required...')->twice();
        $response = $this->io->ask('greeting', 'hello', '', true);

        $this->assertEquals('okay', $response);
    }

    /** @test */
    public function will_blowup_when_asking_more_than_3_times()
    {
        $this->expectException(AskException::class);
        $this->ioHandler->expects()->in('hello ():', '')->times(3)->andReturns('', '', '');
        $this->ioHandler->expects()->out('A response is required...')->times(3);
        $this->io->ask('greeting', 'hello', '', true);

    }

    /** @test */
    public function ask_will_pass_in_default()
    {
        $this->ioHandler->expects()->in('hello (default):', 'default')->once()->andReturn('default');
        $response = $this->io->ask('greeting', 'hello', 'default', false);

        $this->assertEquals('default', $response);
    }

    /** @test */
    public function ask_will_return_empty_when_not_required()
    {
        $this->ioHandler->expects()->in('hello ():', '')->once()->andReturn('');
        $response = $this->io->ask('greeting', 'hello', '', false);

        $this->assertEmpty($response);
    }
}
