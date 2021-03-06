<?php

namespace EllevenFw\Test\Core\Configure;

use \stdClass;
use EllevenFw\Core\Configure\Configure;
use EllevenFw\Core\Configure\Engine\JsonConfigureEngine;
use EllevenFw\Core\Exception\Types\CoreException;
use PHPUnit\Framework\TestCase;

/**
 * Generated by PHPUnit_SkeletonGenerator on 2015-10-02 at 18:40:02.
 */
class ConfigureTest extends TestCase
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
        $pathDump1 = $this->path . 'dump1.json';
        $pathDump2 = $this->path . 'dump2.json';

        $dump1 = array(
            "Dump" => "1"
        );

        $dump1Content = json_encode($dump1, JSON_PRETTY_PRINT);
        file_put_contents($pathDump1, $dump1Content);

        $dump2 = array(
            "Dump" => "2"
        );

        $dump2Content = json_encode($dump2, JSON_PRETTY_PRINT);
        file_put_contents($pathDump2, $dump2Content);
    }

    public function testRegisty()
    {
        Configure::clear();

        $path = $this->path . 'valid.json';
        $engine = new JsonConfigureEngine($path);

        Configure::registry('Test', $engine);
        $this->assertTrue(Configure::checkEngine('Test', $engine));

        $expected = Configure::readAll('Test');
        $actual = $engine->read();
        $this->assertEquals($expected, $actual);
    }

    public function testRegistryByJsonFile()
    {
        $path = $this->path . 'valid.json';
        Configure::registryByFile('Valid', $path);
        $this->assertInstanceOf(
            'EllevenFw\Core\Configure\Engine\JsonConfigureEngine',
            Configure::getEngine('Valid')
        );
    }

    public function testRegistryByPhpFile()
    {
        $path = $this->path . 'valid.php';
        Configure::registryByFile('Valid', $path);
        $this->assertInstanceOf(
            'EllevenFw\Core\Configure\Engine\PhpConfigureEngine',
            Configure::getEngine('Valid')
        );
    }

    public function testRegistryByInvalidFile()
    {
        $this->expectException(CoreException::class);
        $this->expectExceptionMessage('Não foi possível carregar o arquivo de configuração "invalid.txt". O tipo de engine que deve ser usado não foi reconhecido.');

        $path = $this->path . 'invalid.txt';
        Configure::registryByFile('Invalid', $path);
    }

    public function testRegistryByNonExistentFile()
    {
        $this->expectException(CoreException::class);
        $this->expectExceptionMessage('Arquivo de configuração não existente.');

        $path = $this->path . 'nonexistent';
        Configure::registryByFile('NonExistent', $path);
    }

    public function testIsValidFileJson()
    {
        $path = $this->path . 'valid.json';
        $this->assertTrue(Configure::isValidFile($path));
    }

    public function testIsValidFilePhp()
    {
        $path = $this->path . 'valid.php';
        $this->assertTrue(Configure::isValidFile($path));
    }

    public function testIsInvalidFile()
    {
        $path = $this->path . 'invalid.txt';
        $this->assertFalse(Configure::isValidFile($path));
    }

    public function testCheckEngine()
    {
        Configure::clear();

        $path = $this->path . 'valid.json';
        $engine1 = new JsonConfigureEngine($path);

        $path = $this->path . 'valid.json';
        $engine2 = new JsonConfigureEngine($path);

        Configure::registry('Test1', $engine1);
        Configure::registry('Test2', $engine2);
        $this->assertTrue(Configure::checkEngine('Test1', $engine1));
        $this->assertTrue(Configure::checkEngine('Test2', $engine2));

        $this->assertFalse(Configure::checkEngine('Test1', $engine2));
        $this->assertFalse(Configure::checkEngine('Test2', $engine1));

        $this->assertFalse(Configure::checkEngine('NonExistent', $engine1));
    }

    public function testGetEngine()
    {
        Configure::clear();

        $path = $this->path . 'valid.json';
        $engine = new JsonConfigureEngine($path);

        Configure::registry('Test', $engine);
        $this->assertEquals(Configure::getEngine('Test'), $engine);
    }

    public function testGetEngineWithInvalidParam()
    {
        $this->expectException(CoreException::class);
        $this->expectExceptionMessage('O mecanismo de configurações com o nome "NonExistent" não foi encontrado.');

        Configure::clear();

        $path = $this->path . 'valid.json';
        $engine = new JsonConfigureEngine($path);

        Configure::registry('Test', $engine);
        $this->assertEquals(Configure::getEngine('NonExistent'), $engine);
    }

    public function testWrite()
    {
        Configure::clear();

        $data = array(
            'Test' => 'firstTest',
            'Deep' => array(
                'Deeper' => array(
                    'Test' => 'secondTest'
                )
            )
        );

        $expected = array(
            'Test' => 'firstTest',
            'Deep' => array(
                'Deeper' => array(
                    'Test' => 'secondTest',
                    "Deepest" => "buried"
                )
            ),
            "Json" => "value",
            "TestAcl" => array(
                "classname" => "Original"
            )
        );

        $path = $this->path . 'valid.json';
        $engine = new JsonConfigureEngine($path);

        Configure::registry('Test', $engine);
        Configure::write('Test', $data);

        $actual = Configure::readAll('Test');
        $this->assertEquals($expected, $actual);
    }

    public function testWriteWithInvalidParam()
    {
        $this->expectException(CoreException::class);
        $this->expectExceptionMessage('Parâmetro inválido: "invalid". Não é um valor do tipo array.');

        Configure::clear();

        $data = 'invalid';

        $path = $this->path . 'valid.json';
        $engine = new JsonConfigureEngine($path);

        Configure::registry('Test', $engine);
        Configure::write('Test', $data);
    }

    public function testRead()
    {
        Configure::clear();

        $path = $this->path . 'valid.json';
        $engine = new JsonConfigureEngine($path);

        Configure::registry('Test', $engine);
        $actual = Configure::read('Test', 'Deep.Deeper.Deepest');
        $this->assertEquals('buried', $actual);

        $actual = Configure::read('Test', 'Json');
        $this->assertEquals('value', $actual);
    }

    public function testReadWithArrayKey()
    {
        Configure::clear();

        $path = $this->path . 'valid.json';
        $engine = new JsonConfigureEngine($path);

        Configure::registry('Test', $engine);
        $actual = Configure::read('Test', array('Json'));

        $this->assertEquals('value', $actual);
    }

    public function testReadWithDefaultValue()
    {
        Configure::clear();

        $path = $this->path . 'valid.json';
        $engine = new JsonConfigureEngine($path);

        Configure::registry('Test', $engine);
        $actual = Configure::read('Test', 'Deep.Deeper.Deepest.NonExistent', 'value');

        $this->assertEquals('value', $actual);
    }

    public function testReadWithNonExistentConfigurationName()
    {
        $this->expectException(CoreException::class);
        $this->expectExceptionMessage('O conjunto de configurações com o nome "NonExistent" não foi encontrado.');

        Configure::clear();

        $path = $this->path . 'valid.json';
        $engine = new JsonConfigureEngine($path);

        Configure::registry('Test', $engine);
        Configure::read('NonExistent', 'Deep.Deeper.Deepest');
    }

    public function testReadWithEmptyKey()
    {
        Configure::clear();

        $path = $this->path . 'valid.json';
        $engine = new JsonConfigureEngine($path);

        Configure::registry('Test', $engine);
        $expected = Configure::read('Test', '');
        $this->assertEquals($expected, null);
    }

    public function testReadWithInvalidKey()
    {
        $this->expectException(CoreException::class);
        $this->expectExceptionMessage('Parâmetro inválido e não pode ser convertido em array.');

        Configure::clear();

        $path = $this->path . 'valid.json';
        $engine = new JsonConfigureEngine($path);

        Configure::registry('Test', $engine);
        Configure::read('Test', new stdClass());
    }

    public function testReadAll()
    {
        Configure::clear();

        $path = $this->path . 'valid.json';
        $engine = new JsonConfigureEngine($path);

        Configure::registry('Test', $engine);

        $actual = array(
            "Test" => array(
                "Json" => "value",
                "Deep" => array(
                    "Deeper" => array(
                        "Deepest" => "buried"
                    )
                ),
                "TestAcl" => array(
                    "classname" => "Original"
                )
            )
        );
        $expected = Configure::readAll();
        $this->assertEquals($expected, $actual);

        $expected = Configure::readAll('Test');
        $this->assertEquals($expected, $actual['Test']);
    }

    public function testReadAllWithNonExistentConfigurationName()
    {
        $this->expectException(CoreException::class);
        $this->expectExceptionMessage('O conjunto de configurações com o nome "NonExistent" não foi encontrado.');

        Configure::clear();

        $path = $this->path . 'valid.json';
        $engine = new JsonConfigureEngine($path);

        Configure::registry('Test', $engine);
        Configure::readAll('NonExistent');
    }

    public function testCheck()
    {
        Configure::clear();

        $path = $this->path . 'valid.json';
        $engine = new JsonConfigureEngine($path);

        Configure::registry('Test', $engine);

        $actual = Configure::check('Test', 'Json', 'value');
        $this->assertTrue($actual);

        $actual = Configure::check('Test', 'Deep.Deeper.Deepest', 'buried');
        $this->assertTrue($actual);

        $actual = Configure::check('Test', 'Deep.Deeper.Deepest', 'invalid');
        $this->assertFalse($actual);

        $actual = Configure::check('Test', 'Deep.Deeper.NonExistent', 'invalid');
        $this->assertFalse($actual);
    }

    public function testDump()
    {
        Configure::clear();

        $pathDump1 = $this->path . 'dump1.json';
        $engine1 = new JsonConfigureEngine($pathDump1);

        $pathDump2 = $this->path . 'dump2.json';
        $engine2 = new JsonConfigureEngine($pathDump2);

        Configure::registry('Dump1', $engine1);
        Configure::registry('Dump2', $engine2);

        Configure::write('Dump1', array('Test' => 1));
        Configure::write('Dump2', array('Test' => 2));

        Configure::dump('Dump1');

        $dump1 = array(
            "Dump" => "1",
            "Test" => 1
        );

        $expected = json_encode($dump1, JSON_PRETTY_PRINT);
        $actual = file_get_contents($pathDump1);
        $this->assertEquals($expected, $actual);

        Configure::dump();

        $dump2 = array(
            "Dump" => "2",
            "Test" => 2
        );

        $expected = json_encode($dump2, JSON_PRETTY_PRINT);
        $actual = file_get_contents($pathDump2);
        $this->assertEquals($expected, $actual);
    }

    public function testClear()
    {
        Configure::clear();

        $expected = array();
        $actual = Configure::readAll();

        $this->assertEquals($expected, $actual);
    }
}