<?php

namespace Dbtlr\PHPEnvBuilder;

use Composer\Script\Event;
use Composer\Factory;
use Dbtlr\PHPEnvBuilder\Exception\AskException;
use Dbtlr\PHPEnvBuilder\Exception\ConfigurationException;
use Dbtlr\PHPEnvBuilder\IOHandler\ComposerIOHandler;

class ComposerScriptRunner
{
    /** @var array */
    protected $config = [];

    /** @var Builder */
    protected $builder;

    /** @var Event */
    protected $event;

    /** @var string */
    protected $basePath;

    /**
     * ComposerScriptRunner constructor.
     *
     * @throws ConfigurationException
     * @param Event $event
     * @param Builder|null $builder
     */
    public function __construct(Event $event, Builder $builder = null)
    {
        $this->event = $event;
        $this->builder = $builder;

        $this->populateConfig();

        if (!$this->builder) {
            $this->createBuilder();
        }
    }

    /**
     * Set a particular config value.
     *
     * @param string $key
     * @param mixed $value
     */
    public function set(string $key, $value)
    {
        $this->config[$key] = $value;
    }

    /**
     * Get the requested config var.
     *
     * @param string $key
     * @return mixed|null
     */
    public function get(string $key)
    {
        return isset($this->config[$key]) ? $this->config[$key] : null;
    }

    /**
     * @return string
     */
    public function getBasePath()
    {
        if (!isset($this->basePath)) {
            $this->basePath = realpath(dirname(Factory::getComposerFile()));
        }

        return $this->basePath;
    }

    /**
     * @param string $basePath
     */
    public function setBasePath(string $basePath)
    {
        $this->basePath = $basePath;
    }

    /**
     * Get the full path to the env file.
     *
     * @return mixed|null|string
     */
    public function getEnvFile()
    {
        $basePath = $this->getBasePath();
        $envFile = $this->get('envFile');
        $startsWith = substr($envFile, 0, 1);
        if (!$startsWith !== '/' && $startsWith !== '~' && $startsWith !== '\\') {
            $envFile = $basePath . DIRECTORY_SEPARATOR . $envFile;
        }

        return $envFile;
    }

    /**
     * @throws \InvalidArgumentException
     */
    protected function populateConfig()
    {
        $extras = $this->event->getComposer()->getPackage()->getExtra();

        $this->verifyExtras($extras);

        $config = $extras['php-env-builder'];

        $this->set('envFile', isset($config['envFile']) ? $config['envFile'] : '.env');
        $this->set('clobber', isset($config['clobber']) ? $config['clobber'] : false);
        $this->set('verbose', isset($config['verbose']) ? $config['verbose'] : false);
        $this->set('loadEnv', isset($config['loadEnv']) ? $config['loadEnv'] : false);
        $this->set('questions', $config['questions']);
    }

    /**
     * Verify that the extras a formatted properly and throw an exception if not.
     *
     * @throws \InvalidArgumentException
     * @param array $extras
     */
    protected function verifyExtras(array $extras)
    {
        if (!isset($extras['php-env-builder'])) {
            throw new \InvalidArgumentException(
                'The parameter handler needs to be configured through the ' .
                'extra.php-env-builder setting.'
            );
        }

        if (!is_array($extras['php-env-builder'])) {
            throw new \InvalidArgumentException(
                'The extra.php-env-builder setting must be an array or a configuration object.'
            );
        }

        if (!isset($extras['php-env-builder']['questions']) || !is_array($extras['php-env-builder']['questions'])) {
            throw new \InvalidArgumentException(
                'The extra.php-env-builder.questions setting must be an array of questions.'
            );
        }
    }

    /**
     * @throws ConfigurationException
     */
    protected function createBuilder()
    {
        $this->builder = new Builder(
            $this->getEnvFile(),
            [
                'verbose' => $this->get('verbose'),
                'loadEnv' => $this->get('loadEnv')
            ]
        );

        $this->builder->setIOHandler(new ComposerIOHandler($this->event->getIO()));
    }

    /**
     * Override the Builder.
     *
     * @param Builder $builder
     */
    public function setBuilder(Builder $builder)
    {
        $this->builder = $builder;
    }

    /**
     * Build the config based on a composer's package.json file.
     *
     * @throws ConfigurationException
     * @throws AskException
     * @param Event $event
     * @param Builder $builder
     */
    public static function build(Event $event, Builder $builder = null)
    {
        $runner = new ComposerScriptRunner($event, $builder);
        $runner->run();
    }

    /**
     * Run the builder
     *
     * @throws AskException
     */
    public function run()
    {
        if ($this->shouldCancelOnClobber()) {
            return;
        }

        $this->askQuestions($this->get('questions'));

        $this->builder->run();
        $this->builder->write();
    }

    /**
     * Run through each of the questions and ask each one.
     *
     * @param array $questons
     */
    protected function askQuestions(array $questons)
    {
        foreach ($questons as $question) {
            $this->verifyQuestion($question);

            $name = $question['name'];
            $prompt = $question['prompt'];
            $default = isset($question['default']) ? $question['default'] : '';
            $required = isset($question['required']) ?  (bool) $question['required'] : false;

            $this->builder->ask($name, $prompt, $default, $required);
        }
    }

    /**
     * Verify that the question has the minimum fields.
     *
     * @throws \InvalidArgumentException
     * @param array $question
     */
    protected function verifyQuestion(array $question)
    {
        if (!isset($question['name'])) {
            throw new \InvalidArgumentException(
                'The extra.php-env-builder.questions require all questions have a `name` property.'
            );
        }

        if (!isset($question['prompt'])) {
            throw new \InvalidArgumentException(
                'The extra.php-env-builder.questions require all questions have a `prompt` property.'
            );
        }
    }

    /**
     * If we're clobbering and we're not supposed to, should we cancel?
     *
     * @return bool
     */
    protected function shouldCancelOnClobber()
    {
        $fullPath = $this->getEnvFile();
        if (!$this->get('clobber') && file_exists($fullPath)) {
            $this->event->getIO()->write(sprintf('Env file `%s` already exists, skipping...', $fullPath));
            return true;
        }

        return false;
    }
}
