<?php
namespace Dbtlr\PHPEnvBuilder\IOHandler;

use Composer\IO\IOInterface;

class ComposerIOHandler implements IOHandlerInterface
{
    /** @var IOInterface */
    protected $bus;

    /**
     * ComposerIOHandler constructor.
     *
     * @param IOInterface $bus
     */
    public function __construct(IOInterface $bus)
    {
        $this->bus = $bus;
    }

    /**
     * Output a message to the console.
     *
     * @param string $message
     */
    public function out(string $message)
    {
        $this->bus->write($message);
    }

    /**
     * Get input from the console.
     *
     * @param string $question
     * @param string $default
     * @return mixed
     */
    public function in(string $question, string $default = '')
    {
        return $this->bus->ask($question, $default);
    }
}
