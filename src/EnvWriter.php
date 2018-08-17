<?php
namespace Dbtlr\PHPEnvBuilder;

use Dbtlr\PHPEnvBuilder\Exception\WritableException;

class EnvWriter
{
    /** @var string */
    protected $directory;

    /** @var string */
    protected $file;

    /**
     * EnvWriter constructor.
     *
     * @param string $directory
     * @param string $file
     */
    public function __construct($directory, $file = '.env')
    {
        $this->directory = $directory;
        $this->file = $file;
    }

    /**
     * Save the given answers to the env file.
     *
     * @throws WritableException
     * @param array $answers
     */
    public function save(array $answers)
    {
        $path = $this->directory . DIRECTORY_SEPARATOR . $this->file;

        if (!file_exists($path)) {
            if (!is_writable($this->directory)) {
                throw new WritableException(
                    sprintf(
                        'The env file is not present and the directory `%s` is not writeable!',
                        $this->directory
                    )
                );
            }

            touch($path);
        }

        if (!is_writable($path)) {
            throw new WritableException(sprintf('The env file `%s` is not writeable!', $path));
        }

        $text = '';
        foreach ($answers as $key => $value) {
            $text .= sprintf("%s=%s\n", $key, $value);
        }

        file_put_contents($path, $text);
    }
}
