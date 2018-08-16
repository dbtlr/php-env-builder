<?php

namespace Dbtlr\PHPEnvBuilder\Exception;

class AskException extends \Exception
{
    /**
     * AskException constructor.
     *
     * @param string $name
     * @param int $times
     */
    public function __construct(string $name, int $times)
    {
        parent::__construct(sprintf(
            'Failed after asking for `%S` %d time%S',
            $name,
            $times,
            $times !== 1 ? 's' : ''
        ));
    }
}
