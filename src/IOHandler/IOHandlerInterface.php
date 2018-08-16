<?php
namespace Dbtlr\PHPEnvBuilder\IOHandler;


interface IOHandlerInterface
{
    /**
     * Output a message to the console.
     *
     * @param string $message
     */
    public function out(string $message);

    /**
     * Get input from the console.
     *
     * @param string $question
     * @param string $default
     * @return mixed
     */
    public function in(string $question, string $default = '');
}
