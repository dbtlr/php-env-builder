<?php
namespace Dbtlr\PHPEnvBuilder;

use Dotenv\Exception\InvalidPathException;
use League\CLImate\CLImate;
use Dotenv\Dotenv;

class EnvBuilder
{
    /** @var CLImate */
    protected $climate;

    /** @var string */
    protected $env;

    /** @var bool */
    protected $loadEnv = true;

    /** @var array */
    protected $output = [];

    /** @var bool */
    protected $verbose = true;

    /** @var bool */
    protected $uppercaseKeys = true;

    /**
     * EnvBuilder constructor.
     *
     * @throws ConfigurationException
     * @param array $config
     */
    public function __construct(array $config = array())
    {
        $this->climate = new CLImate();

        $this->loadConfig($config);
        $this->loadEnv();
    }

    /**
     * Load the configuration file.
     *
     * @throws ConfigurationException
     * @param array $config
     */
    protected function loadConfig(array $config)
    {
        if (!isset($config['env'])) {
            throw new ConfigurationException('env', 'Please add this with the location of the .env file we\'re reading and building.');
        }

        $this->env = $config['env'];
        $this->loadEnv = isset($config['loadEnv']) ? (bool) $config['loadEnv'] : true;
        $this->loadEnv = isset($config['verbose']) ? (bool) $config['verbose'] : true;
        $this->uppercaseKeys = isset($config['uppercaseKeys']) ? (bool) $config['uppercaseKeys'] : true;
    }

    /**
     * Load the environment if we need to or can.
     */
    protected function loadEnv()
    {
        if (!$this->loadEnv) {
            return;
        }

        // If the file doesn't exist yet, then we don't need to do anything.
        if (!file_exists($this->env)) {
            $this->log(
                'The env file `%s` doesn\'t exist yet.',
                $this->env
            );

            return;
        }

        $dotenv = new Dotenv(dirname($this->env), basename($this->env));

        try {
            $dotenv->overload();

        } catch (InvalidPathException $e) {
            // We shouldn't ever get this, but added just to be safe.
        }
    }

    /**
     * Log a response to the console.
     *
     * Logging will skip if the verbose config is set to `false`
     *
     * @param string $message
     * @param mixed $args...
     */
    protected function log($message, $args)
    {
        if (!$this->verbose) {
            return;
        }

        $args = func_get_args();
        $message = array_shift($args);

        $this->climate->out(vsprintf($message, $args));
    }

    /**
     * Ask for a variable name.
     *
     * @param string $name The name of the env variable to read/use
     * @param string $prompt The prompt you want to give the user
     * @param string $default A default answer, if any.
     * @param bool $required Is a response required?
     * @return string The user's response
     */
    public function ask($name, $prompt, $default = '', $required = false)
    {
        if ($this->uppercaseKeys) {
            $name = strtoupper($name);
        }

        $current = getenv($name);

        $answered = false;

        while (!$answered) {
            $input = $this->climate->input(sprintf('%s (%s):', $prompt, $current));
            $response = $input->prompt() ?: $current;

            if ($required  && empty($response)) {
                $this->climate->out('A response is required...');
                continue;
            }

            $answered = true;

            $this->output[$name] = $response;
        }

        return $this->output[$name];
    }

    /**
     * Get a variable from the asked store.
     *
     * @param string $name The var name you need
     * @return string|null
     */
    public function get($name)
    {
        if (!isset($this->output[$name])) {
            return null;
        }

        return $this->output[$name];
    }

    /**
     * Get all of the built files.
     *
     * @return array
     */
    public function getAll()
    {
        return $this->output;
    }

    /**
     * Write the results to the .env file.
     *
     * Note: This will overwrite any existing .env file.
     * @throws WritableException
     */
    public function write()
    {
        if (!file_exists($this->env)) {
            if (!is_writable(dirname($this->env))) {
                throw new WritableException(sprintf('The env file is not present and the directory `%s` is not writeable!', dirname($this->env)));
            }

            touch($this->env);
        }

        if (!is_writable($this->env)) {
            throw new WritableException(sprintf('The env file `%s` is not writeable!', $this->env));
        }

        $text = '';
        foreach ($this->output as $key => $value) {
            if ($this->uppercaseKeys) {
                $key = strtoupper($key);
            }

            $text .= sprintf("%s=%s\n", $key, $value);
        }

        file_put_contents($this->env, $text);
    }
}
