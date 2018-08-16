<?php

namespace Dbtlr\PHPEnvBuilder\Tests\IOHandler;

use Composer\IO\IOInterface;
use Dbtlr\PHPEnvBuilder\IOHandler\ComposerIOHandler;
use Dbtlr\PHPEnvBuilder\Tests\TestBase;
use Mockery\MockInterface;

class TestComposerHandler extends TestBase
{
    /** @var IOInterface|MockInterface */
    protected $bus;

    /** @var ComposerIOHandler */
    protected $handler;

    protected function internalSetup()
    {
        $this->bus = \Mockery::mock(IOInterface::class);
        $this->handler = new ComposerIOHandler($this->bus);
    }

    /** @test */
    public function will_write_output_message()
    {
        $this->bus->expects()->write('message me')->once();
        $this->handler->out('message me');
    }

    /** @test */
    public function will_ask_question()
    {
        $this->bus->expects()->ask('question', 'default')->once()->andReturn('answer');
        $answer = $this->handler->in('question', 'default');

        $this->assertEquals('answer', $answer);
    }
}
