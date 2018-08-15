<?php
namespace Dbtlr\PHPEnvBuilder;

use Dotenv\Exception\InvalidPathException;
use Dotenv\Loader;

class EnvLoader extends Loader
{
    /**
     * Load the environment if we need to or can.
     *
     * @return array
     */
    public function parse()
    {
        try {
            $this->ensureFileIsReadable();
        } catch (InvalidPathException $e) {
            return [];
        }

        $filePath = $this->filePath;
        $lines = $this->readLinesFromFile($filePath);

        $data = [];
        foreach ($lines as $line) {
            if (!$this->isComment($line) && $this->looksLikeSetter($line)) {
                list($name, $value) = $this->processFilters($line, null);
                $data[$name] = $value;
            }
        }

        return $data;
    }
}
