<?php
namespace Dbtlr\PHPEnvBuilder\IOHandler;

use League\CLImate\CLImate;

class CLImateHandler implements IOHandlerInterface
{
    /** @var CLImate */
    protected $climate;

    /**
     * CLImateHandler constructor.
     *
     * @param CLImate $climate
     */
    public function __construct(CLImate $climate)
    {
        $this->climate = $climate;
    }

    /**
     * Output a message to the console.
     *
     * @param string $message
     */
    public function out(string $message)
    {
        $this->climate->out($message);
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
        $input = $this->climate->input($question);
        return $input->prompt() ?: $default;
    }
}
