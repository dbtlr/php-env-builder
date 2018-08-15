<?php

namespace Dbtlr\PHPEnvBuilder;

class ConfigurationException extends \Exception
{
    /**
     * ConfigurationException constructor.
     *
     * @param string $name
     * @param string $postPend
     */
    public function __construct($name, $postPend = '')
    {
        parent::__construct(sprintf(
            'Config option `%S` was missing. %s',
            $name,
            $postPend
        ));
    }
}
