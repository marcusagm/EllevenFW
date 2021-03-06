<?php

namespace EllevenFw\Test\Core\Configure\Engine;

use EllevenFw\Core\Configure\Engine\JsonConfigureEngine;
use EllevenFw\Core\Exception\Types\CoreException;
use PHPUnit\Framework\TestCase;

/**
 * Generated by PHPUnit_SkeletonGenerator on 2015-10-02 at 18:40:02.
 */
class JsonConfigureEngineTest extends TestCase
{
    /**
     * @var Configure
     */
    protected $path;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void
    {
        $this->path = APP_CONFIG . 'tests' . DS;
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown(): void
    {
        if (file_exists($this->path . 'invalid.json')) {
            unlink($this->path . 'invalid.json');
        }
    }

    public function testDump()
    {
        $path = $this->path . 'valid.json';
        $engine = new JsonConfigureEngine($path);

        $tmpPath = $this->path . 'tmp.json';
        $result = $engine->dump($tmpPath);
        $this->assertTrue($result);

        $data = array(
            "Json" => "value",
            "Deep" => array(
                "Deeper" => array(
                    "Deepest" => "buried"
                )
            ),
            "TestAcl" => array(
                "classname" => "Original"
            )
        );
        $expected = json_encode($data, JSON_PRETTY_PRINT);
        $contents = file_get_contents($tmpPath);
        $this->assertEquals($expected, $contents);

        unlink($tmpPath);
    }

    public function testDumpWithNewData()
    {
        $path = $this->path . 'valid.json';
        $engine = new JsonConfigureEngine($path);

        $data = array(
            'Test' => 'firstTest',
            'Deep' => array(
                'Deeper' => array(
                    'Test' => 'secondTest'
                )
            )
        );
        $engine->write($data, false);

        $tmpPath = $this->path . 'tmp.json';
        $engine->dump($tmpPath);

        $expected = json_encode($data, JSON_PRETTY_PRINT);
        $contents = file_get_contents($tmpPath);
        $this->assertEquals($expected, $contents);

        $engineTmp = new JsonConfigureEngine($tmpPath);
        $result = $engineTmp->read();
        $this->assertEquals($data, $result);

        unlink($tmpPath);
    }

    public function testWriteWithInvalidParam()
    {
        $this->expectException(CoreException::class);
        $this->expectExceptionMessage('O valor informado para adicionar no arquivo de configuração não é um array.');
        $path = $this->path . 'valid.json';
        $engine = new JsonConfigureEngine($path);
        $engine->write('test');
    }

    public function testWriteWithValidParam()
    {
        $path = $this->path . 'valid.json';
        $engine = new JsonConfigureEngine($path);

        $data = array(
            'Test' => 'firstTest',
            'Deep' => array(
                'Deeper' => array(
                    'Test' => 'secondTest'
                )
            )
        );
        $engine->write($data);
        $values = $engine->read();

        $this->assertEquals('value', $values['Json']);
        $this->assertEquals('buried', $values['Deep']['Deeper']['Deepest']);
        $this->assertEquals('firstTest', $values['Test']);
        $this->assertEquals('secondTest', $values['Deep']['Deeper']['Test']);
    }

    public function testWriteWithReplaceContent()
    {
        $path = $this->path . 'valid.json';
        $engine = new JsonConfigureEngine($path);

        $data = array(
            'Test' => 'firstTest',
            'Deep' => array(
                'Deeper' => array(
                    'Test' => 'secondTest'
                )
            )
        );
        $engine->write($data, false);
        $values = $engine->read();

        $this->assertFalse(isset($values['Json']));
        $this->assertFalse(isset($values['Deep']['Deeper']['Deepest']));

        $this->assertTrue(isset($values['Test']));
        $this->assertTrue(isset($values['Deep']['Deeper']['Test']));
    }

    public function testRead()
    {
        $path = $this->path . 'valid.json';
        $engine = new JsonConfigureEngine($path);
        $values = $engine->read();

        $this->assertEquals('value', $values['Json']);
        $this->assertEquals('buried', $values['Deep']['Deeper']['Deepest']);
    }

    public function testReadWithNonExistentFile()
    {
        $this->expectException(CoreException::class);
        $this->expectExceptionMessage('Arquivo de configuração não existente.');

        $path = $this->path . 'nonexistent.json';
        $engine = new JsonConfigureEngine($path);
        $values = $engine->read();

        $this->assertEquals('value', $values['Json']);
        $this->assertEquals('buried', $values['Deep']['Deeper']['Deepest']);
    }

    public function testReadWithInvalidJson()
    {
        $this->expectException(CoreException::class);
        $this->expectExceptionMessage('Erro ao ler o arquivo de configuração. O formato do conteúdo está incorreto.');

        $path = $this->path . 'invalid.json';
        file_put_contents($path, '{{{{{');
        $engine = new JsonConfigureEngine($path);
        $engine->read();
    }

    public function testReadWithEmptyFile()
    {
        $this->expectException(CoreException::class);
        $this->expectExceptionMessage('Erro ao ler o arquivo de configuração. O formato do conteúdo está incorreto.');

        $path = $this->path . 'empty.json';
        $engine = new JsonConfigureEngine($path);
        $engine->read();
    }
}
