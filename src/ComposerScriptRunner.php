<?php

namespace Dbtlr\PHPEnvBuilder;

use Composer\Script\Event;
use Composer\Factory;
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
     * @throws \InvalidArgumentException
     * @param Event $event
     */
    public function __construct(Event $event)
    {
        $this->event = $event;

        $this->populateConfig();
        $this->createBuilder();
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

        if (!isset($extras['php-env-builder'])) {
            throw new \InvalidArgumentException('The parameter handler needs to be configured through the extra.php-env-builder setting.');
        }

        $config = $extras['php-env-builder'];
        if (!is_array($config)) {
            throw new \InvalidArgumentException('The extra.php-env-builder setting must be an array or a configuration object.');
        }

        $this->set('envFile', isset($config['envFile']) ? $config['envFile'] : '.env');
        $this->set('clobber', isset($config['clobber']) ? $config['clobber'] : false);
        $this->set('verbose', isset($config['verbose']) ? $config['verbose'] : false);
        $this->set('loadEnv', isset($config['loadEnv']) ? $config['loadEnv'] : false);

        if (!isset($config['questions']) || !is_array($config['questions'])) {
            throw new \InvalidArgumentException('The extra.php-env-builder.questions setting must be an array of questions.');
        }

        $this->set('questions', $config['questions']);
    }

    /**
     * @throws \InvalidArgumentException
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
     * @throws \InvalidArgumentException
     * @param Event $event
     */
    public static function build(Event $event)
    {
        $runner = new ComposerScriptRunner($event);
        $runner->run();
    }

    /**
     * Run the builder
     */
    public function run()
    {
        $fullPath = $this->getEnvFile();
        if (!$this->get('clobber') && file_exists($fullPath)) {
            $this->event->getIO()->write(sprintf('Env file `%s` already exists, skipping...', $fullPath));
            return;
        }

        foreach ($this->get('questions') as $question) {
            if (!isset($question['name'])) {
                throw new \InvalidArgumentException('The extra.php-env-builder.questions require all questions have a `name` property.');
            }

            if (!isset($question['prompt'])) {
                throw new \InvalidArgumentException('The extra.php-env-builder.questions require all questions have a `prompt` property.');
            }

            $name = $question['name'];
            $prompt = $question['prompt'];
            $default = isset($question['default']) ? $question['default'] : '';
            $required = isset($question['required']) ?  (bool) $question['required'] : false;

            $this->builder->ask($name, $prompt, $default, $required);
        }

        $this->builder->run();
        $this->builder->write();
    }
}
