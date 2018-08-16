<?php
namespace Dbtlr\PHPEnvBuilder;

use Dbtlr\PHPEnvBuilder\Exception\AskException;
use Dbtlr\PHPEnvBuilder\IOHandler\IOHandlerInterface;

class IO
{
    /** @var IOHandlerInterface */
    protected $handler;

    /**
     * IO constructor.
     *
     * @param IOHandlerInterface $handler
     */
    public function __construct(IOHandlerInterface $handler)
    {
        $this->setHandler($handler);
    }

    /**
     * Override the IO Handler.
     *
     * @param IOHandlerInterface $handler
     */
    public function setHandler(IOHandlerInterface $handler)
    {
        $this->handler = $handler;
    }

    /**
     * Ask a question.
     *
     * @throws AskException
     * @param string $name
     * @param string $question
     * @param string $default
     * @param bool $required
     * @return string
     */
    public function ask(string $name, string $question, string $default = '', bool $required = false)
    {
        $count = 0;
        $maxAsks = 3;

        while ($count++ < $maxAsks) {
            $response = $this->in($question, $default);

            if (!$required || !empty($response)) {
                return $response;
            }

            $this->handler->out('A response is required...');
        }

        throw new AskException($name, $maxAsks);
    }

    /**
     * Output a message to the console.
     *
     * @param string $message
     */
    public function out(string $message)
    {
        $this->handler->out($message);
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
        $formatted = sprintf('%s (%s):', $question, $default);

        return $this->handler->in($formatted, $default);
    }
}
