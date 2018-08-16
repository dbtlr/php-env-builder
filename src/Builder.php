<?php

namespace Dbtlr\PHPEnvBuilder;

use Dbtlr\PHPEnvBuilder\Exception\ConfigurationException;
use Dbtlr\PHPEnvBuilder\Exception\WritableException;
use Dbtlr\PHPEnvBuilder\IOHandler\CLImateHandler;
use Dbtlr\PHPEnvBuilder\IOHandler\IOHandlerInterface;
use League\CLImate\CLImate;

class Builder
{
    /** @var array */
    protected $questions = [];

    /** @var array */
    protected $config = [];

    /** @var string */
    protected $envFile;

    /** @var EnvLoader */
    protected $envLoader;

    /** @var EnvWriter */
    protected $envWriter;

    /** @var IO */
    protected $io;

    /** @var array */
    protected $answers = [];


    /**
     * Application constructor.
     *
     * This builds with the CLImateInterface as a default, but this
     * can be overridden by the setIOHandler() method.
     *
     * @throws ConfigurationException
     * @param string $envFile
     * @param array $config
     */
    public function __construct(string $envFile, array $config = [])
    {
        $this->envFile = $envFile;

        // Set the config to it's defaults.
        $this->config = [
            'verbose' => false,
            'loadEnv' => true,
        ];

        $this->setConfig($config);
        $this->setEnvLoader(new EnvLoader($envFile));
        $this->setEnvWriter(new EnvWriter(dirname($envFile), basename($envFile)));
        $this->setIO(new IO(new CLImateHandler(new CLImate())));
    }

    /**
     * Set some of the optional config elements.
     *
     * @throws ConfigurationException
     * @param array $config
     */
    public function setConfig(array $config)
    {
        foreach ($config as $key => $value) {
            switch ($key) {
                case 'verbose':
                case 'loadEnv':
                    if (!is_bool($value)) {
                        throw new ConfigurationException($key, 'This should be a boolean value.');
                    }

                    $this->config[$key] = $value;
                    break;

                default:
                    throw new ConfigurationException($key, 'This is an unknown configuration option.');
            }
        }
    }

    /**
     * Get the Builder's configuration options.
     *
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Set the EnvLoader.
     *
     * @param EnvLoader $envLoader
     */
    public function setEnvLoader(EnvLoader $envLoader)
    {
        $this->envLoader = $envLoader;
    }

    /**
     * Add in an IOHanderInterface object.
     *
     * @param IOHandlerInterface $ioHandler
     */
    public function setIOHandler(IOHandlerInterface $ioHandler)
    {
        $this->io->setHandler($ioHandler);
    }

    /**
     * Set the IO.
     *
     * @param IO $io
     */
    public function setIO(IO $io)
    {
        $this->io = $io;
    }

    /**
     * Override the EnvWriter.
     *
     * @param EnvWriter $envWriter
     */
    public function setEnvWriter(EnvWriter $envWriter)
    {
        $this->envWriter = $envWriter;
    }

    /**
     * Run the builder
     *
     * @return array
     */
    public function run()
    {
        $current = [];
        if ($this->config['loadEnv']) {
            $current = $this->envLoader->parse();
        }

        foreach ($this->questions as $name => $question) {
            // If We got an answer out of the loaded env, then let's use it.
            $default = isset($current[$name]) ? $current[$name] : $question['default'];

            // Ask the question.
            $this->answers[$name] = $this->io->ask($name, $question['prompt'], $default, $question['required']);
        }

        return $this->answers;
    }

    /**
     * Write the answers to a file.
     *
     * @return bool The status of the write operation.
     */
    public function write()
    {
        try {
            $this->envWriter->save($this->answers);
            $this->io->out(sprintf('Answers saved to %s', $this->envFile));
            return true;

        } catch (WritableException $e) {
            $this->io->out($e->getMessage());
            return false;
        }
    }

    /**
     * Set a question to ask the user.
     *
     * @param string $name The name of the env variable to read/use
     * @param string $prompt The prompt you want to give the user
     * @param string $default A default answer, if any.
     * @param bool $required Is a response required?
     */
    public function ask($name, $prompt, $default = '', $required = false)
    {
        $this->questions[$name] = [
            'prompt' => $prompt,
            'default' => $default,
            'required' => $required,
        ];
    }

    /**
     * Return all of the questions.
     *
     * @return array
     */
    public function getQuestions()
    {
        return $this->questions;
    }
}
