<?php
namespace Dbtlr\PHPEnvBuilder\Tests;

use Dbtlr\PHPEnvBuilder\EnvWriter;
use Dbtlr\PHPEnvBuilder\Exception\WritableException;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;

class TestEnvWriter extends TestBase
{
    /** @var vfsStreamDirectory */
    protected $root;

    protected function internalSetup()
    {
        $this->root = vfsStream::setup('var');
    }

    /** @test */
    public function will_write_answers_to_file()
    {
        $writer = new EnvWriter($this->root->url(), '.env');
        $writer->save(['name' => 'jack', 'age' => 44]);

        $expected = "name=jack\nage=44\n";
        $result = file_get_contents($this->root->url() . DIRECTORY_SEPARATOR . '.env');
        $this->assertSame($expected, $result);
    }

    /** @test */
    public function will_blow_up_if_directory_not_writable()
    {
        $this->expectException(WritableException::class);

        // Make a directory that is not writable.
        vfsStream::newDirectory('blue', 0111)->at($this->root);

        $writer = new EnvWriter($this->root->url() . DIRECTORY_SEPARATOR . 'blue', '.env');
        $writer->save(['name' => 'jack', 'age' => 44]);
    }

    /** @test */
    public function will_blow_up_if_directory_not_exist()
    {
        $this->expectException(WritableException::class);

        $writer = new EnvWriter($this->root->url() . DIRECTORY_SEPARATOR . 'nonexistent', '.env');
        $writer->save(['name' => 'jack', 'age' => 44]);
    }

    /** @test */
    public function will_blow_up_if_file_not_writable()
    {
        $this->expectException(WritableException::class);

        vfsStream::newFile('.env', 0000)->at($this->root);

        $writer = new EnvWriter($this->root->url(), '.env');
        $writer->save(['name' => 'jack', 'age' => 44]);
    }
}
