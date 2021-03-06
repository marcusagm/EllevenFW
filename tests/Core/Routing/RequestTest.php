<?php
/**
 * Elleven Framework
 * Copyright 2017 Marcus Maia <contato@marcusmaia.com>
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.md
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright   Copyright (c) Marcus Maia <contato@marcusmaia.com>
 * @link        http://elleven.marcusmaia.com Elleven Kit
 * @since       1.0.0
 * @license     http://www.opensource.org/licenses/mit-license.php MIT License
 */

namespace EllevenFw\Test\Core\Routing;

use EllevenFw\Core\Routing\Request;
use EllevenFw\Library\Network\UploadedFile;
use EllevenFw\Library\Network\UploadedFileFactory;
use EllevenFw\Library\Network\ServerRequest;
use EllevenFw\Library\Network\ServerRequestUtils;
use PHPUnit\Framework\TestCase;


/**
 * Generated by PHPUnit_SkeletonGenerator on 2015-09-24 at 22:46:22.
 */
class RequestTest extends TestCase
{
    /**
     * @var Request
     */
    protected $object;

    protected $originalServer = array();

    protected $originalEnvironment = array();

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void
    {
        $this->originalServer = $_SERVER;
        $this->originalEnvironment = $_ENV;


        $_SERVER['SERVER_PROTOCOL'] = '1.1';
        $_SERVER['HTTP_ACCEPT'] = 'application/json';
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['REQUEST_URI'] = '';
        $_SERVER['QUERY_STRING'] = '';
        $_SERVER['HTTP_HOST'] = 'subdomain.example.com';
        $_SERVER['HTTPS'] = 'Off';
        $_SERVER['SERVER_PORT'] = '';
        $_SERVER['PHP_AUTH_USER'] = '';
        $_SERVER['PHP_AUTH_PW'] = '';
        $_SERVER['PHP_SELF'] = '/index.php/index/var';
        $_SERVER['SCRIPT_NAME'] = '/index.php';
        $_SERVER['REMOTE_ADDR'] = '192.168.56.1';
        $_SERVER['PATH'] = '';
        $_SERVER['argv'] = array();
        $_SERVER['argc'] = 0;
        $_GET = array();
        $_POST = array();
        $this->object = $this->getUpdatedRequestObject($_SERVER);

        parent::setUp();
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown(): void
    {
        $_SERVER = $this->originalServer;
        $_ENV = $this->originalEnvironment;
        parent::tearDown();
    }

    public function getUpdatedRequestObject($server)
    {
        $ServerRequest = new ServerRequest(
            $server,
            UploadedFileFactory::normalizeFiles($_FILES),
            ServerRequestUtils::extractUri($server, $_GET),
            ServerRequestUtils::extractMethod($server),
            'php://input',
            ServerRequestUtils::filterHeaders($server),
            $_COOKIE,
            $_GET,
            $_POST
        );
        return new Request($ServerRequest);
    }

    public function testCreateRequestFromGlobals()
    {
        $server = ServerRequestUtils::filterInputGlobals(INPUT_SERVER);
        $get = ServerRequestUtils::filterInputGlobals(INPUT_GET);
        $post = ServerRequestUtils::filterInputGlobals(INPUT_POST);
        $cookies = ServerRequestUtils::filterInputGlobals(INPUT_COOKIE);

        $Request = new Request();
        $this->assertEquals($server, $Request->getServer());
        $this->assertEquals($get, $Request->getQuery());
        $this->assertEquals($post, $Request->getPost());
        $this->assertEquals($cookies, $Request->getCookies());
    }

    public function testeGetServerRequest()
    {
        $ServerRequest = new ServerRequest();
        $Request = new Request($ServerRequest);
        $this->assertEquals($ServerRequest, $Request->getServerRequest());
    }

    public function testGetQuery()
    {
        $this->assertEquals(
            $_GET,
            $this->object->getQuery()
        );
    }

    /**
     *
     * @depends testGetQuery
     */
    public function testGetQueryVar()
    {
        $Request = $this->getUpdatedRequestObject($_SERVER);
        $this->assertFalse($Request->getQueryVar('test'));

        $_GET = array( 'test' => 'test success' );
        $Request = $this->getUpdatedRequestObject($_SERVER);
        $this->assertEquals(
            'test success',
            $Request->getQueryVar('test')
        );
    }

    public function testGetPost()
    {
        $this->assertEquals(
            $_POST,
            $this->object->getPost()
        );
    }

    /**
     *
     * @depends testGetPost
     */
    public function testGetPostVar()
    {
        $Request = $this->getUpdatedRequestObject($_SERVER);
        $this->assertFalse($Request->getPostVar('test'));

        $_POST = array( 'test' => 'test success' );
        $Request = $this->getUpdatedRequestObject($_SERVER);
        $this->assertEquals(
            'test success',
            $Request->getPostVar('test')
        );
    }

    public function testGetFiles()
    {
        $_FILES = [ 'fooFiles' => [
            'tmp_name' => ['file' => 'php://temp'],
            'size'     => ['file' => 0],
            'error'    => ['file' => 0],
            'name'     => ['file' => 'foo.bar'],
            'type'     => ['file' => 'text/plain'],
        ]];
        $expectedFiles = [
            'fooFiles' => [ 'file' => new UploadedFile('php://temp', 0, 0, 'foo.bar', 'text/plain') ]
        ];
        $Request = $this->getUpdatedRequestObject($_SERVER);
        $this->assertEquals(
            $expectedFiles,
            $Request->getFiles()
        );
    }

    /**
     *
     * @depends testGetFiles
     */
    public function testGetFilesVar()
    {
        $Request = $this->getUpdatedRequestObject($_SERVER);
        $this->assertFalse($Request->getFilesVar('test-var'));

        $_FILES = [ 'test-var' => [
            'tmp_name' => ['file' => 'php://temp'],
            'size'     => ['file' => 0],
            'error'    => ['file' => 0],
            'name'     => ['file' => 'foo.bar'],
            'type'     => ['file' => 'text/plain'],
        ]];
        $expectedFiles = [
            'file' => new UploadedFile('php://temp', 0, 0, 'foo.bar', 'text/plain')
        ];
        $Request = $this->getUpdatedRequestObject($_SERVER);
        $this->assertEquals(
            $expectedFiles,
            $Request->getFilesVar('test-var')
        );
    }

    public function testGetServer()
    {
        $Request = $this->getUpdatedRequestObject($_SERVER);
        $this->assertEquals(
            $_SERVER,
            $Request->getServer()
        );
    }

    /**
     *
     * @depends testGetServer
     */
    public function testGetServerVar()
    {
        $Request = $this->getUpdatedRequestObject($_SERVER);
        $this->assertFalse($Request->getServerVar('test'));

        $_SERVER['test'] = 'test success';
        $Request = $this->getUpdatedRequestObject($_SERVER);
        $this->assertEquals(
            'test success',
            $Request->getServerVar('test')
        );
    }

    public function testGetEnvironment()
    {
        $this->assertEquals(
            $_ENV,
            $this->object->getEnvironment()
        );
    }

    /**
     *
     * @depends testGetEnvironment
     */
    public function testGetEnvironmentVar()
    {
        $Request = clone $this->object;
        $this->assertFalse($Request->getEnvironmentVar('test'));
        $Request->setEnvironment(array(
            'test' => 'test success'
        ));
        $this->assertEquals(
            'test success',
            $Request->getEnvironmentVar('test')
        );
    }

    public function testGetCookie()
    {
        $Cookie = $this->object->getCookies();
        $this->assertEquals(array(), $Cookie );
    }

    public function testGetCookieVar()
    {
        $_COOKIE['foo'] = 'bar';
        $Request = $this->getUpdatedRequestObject($_SERVER);
        $Cookie = $Request->getCookiesVar('foo');
        $this->assertEquals('bar', $Cookie );

        $Cookie = $Request->getCookiesVar('foo-bar');
        $this->assertFalse($Cookie );
    }

    public function testGetContentType()
    {
        unset($_SERVER['CONTENT_TYPE']);
        unset($_SERVER['HTTP_CONTENT_TYPE']);
        $Request = $this->getUpdatedRequestObject($_SERVER);
        $this->assertFalse($Request->getContentType());

        $_SERVER['CONTENT_TYPE'] = 'application/xml';
        $Request = $this->getUpdatedRequestObject($_SERVER);
        $this->assertEquals(
            'application/xml',
            $Request->getContentType()
        );

        $_SERVER['HTTP_CONTENT_TYPE'] = 'application/json';
        $Request = $this->getUpdatedRequestObject($_SERVER);
        $this->assertEquals(
            'application/json',
            $Request->getContentType()
        );
    }

    public function testGetClientIp()
    {
        $_SERVER['HTTP_X_FORWARDED_FOR'] = '192.168.1.5, 10.0.1.1, proxy.com';
        $_SERVER['HTTP_CLIENT_IP'] = '192.168.1.2';
        $_SERVER['REMOTE_ADDR'] = '192.168.1.3';

        $Request = $this->getUpdatedRequestObject($_SERVER);
        ServerRequestUtils::setTrustedProxies(array('192.168.1.3'));

        $this->assertEquals('192.168.1.5', $Request->getClientIp());

        ServerRequestUtils::setTrustedProxies(array());
        $this->assertEquals('192.168.1.2', $Request->getClientIp());

        $_SERVER['HTTP_X_FORWARDED_FOR'] = '';
        $Request = $this->getUpdatedRequestObject($_SERVER);
        $this->assertEquals('192.168.1.2', $Request->getClientIp());

        $_SERVER['HTTP_CLIENT_IP'] = '';
        $Request = $this->getUpdatedRequestObject($_SERVER);
        $this->assertEquals('192.168.1.3', $Request->getClientIp());

        $_SERVER['HTTP_CLIENTADDRESS'] = '10.0.1.2, 10.0.1.1';
        $Request = $this->getUpdatedRequestObject($_SERVER);
        $this->assertEquals('10.0.1.2', $Request->getClientIp());
    }

    public function testGetInvalidIp()
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('O IP "192.168.1" é inválido.');

        $_SERVER['REMOTE_ADDR'] = '192.168.1';

        $Request = $this->getUpdatedRequestObject($_SERVER);
        $Request->getClientIp();
    }

    public function testGetMethod()
    {
        unset($_SERVER['REQUEST_METHOD']);
        $Request = $this->getUpdatedRequestObject($_SERVER);
        $this->assertEquals('GET', $Request->getMethod());

        $_SERVER['REQUEST_METHOD'] = 'get';
        $Request = $this->getUpdatedRequestObject($_SERVER);
        $this->assertEquals('GET', $Request->getMethod());

        $_SERVER['REQUEST_METHOD'] = 'post';
        $Request = $this->getUpdatedRequestObject($_SERVER);
        $this->assertEquals('POST', $Request->getMethod());

        $_SERVER['REQUEST_METHOD'] = 'post';
        $_SERVER['X-HTTP-METHOD-OVERRIDE'] = 'put';
        $Request = $this->getUpdatedRequestObject($_SERVER);
        $this->assertEquals('PUT', $Request->getMethod());
    }

    public function testIsMethod()
    {
        $_SERVER['REQUEST_METHOD'] = 'get';
        $Request = $this->getUpdatedRequestObject($_SERVER);
        $this->assertTrue($Request->isMethod('get'));

        $_SERVER['REQUEST_METHOD'] = 'post';
        $Request = $this->getUpdatedRequestObject($_SERVER);
        $this->assertTrue($Request->isMethod('post'));

        $_SERVER['REQUEST_METHOD'] = 'post';
        $_SERVER['X-HTTP-METHOD-OVERRIDE'] = 'put';
        $Request = $this->getUpdatedRequestObject($_SERVER);
        $this->assertTrue($Request->isMethod('put'));
        $this->assertFalse($Request->isMethod('post'));
    }

    public function testIsMethodSafe()
    {
        $_SERVER['REQUEST_METHOD'] = 'get';
        $Request = $this->getUpdatedRequestObject($_SERVER);
        $this->assertTrue($Request->isMethodSafe('get'));

        $_SERVER['REQUEST_METHOD'] = 'HEAD';
        $Request = $this->getUpdatedRequestObject($_SERVER);
        $this->assertTrue($Request->isMethodSafe('head'));

        $_SERVER['REQUEST_METHOD'] = 'post';
        $Request = $this->getUpdatedRequestObject($_SERVER);
        $this->assertFalse($Request->isMethodSafe('post'));
    }

    public function testGetHost()
    {
        $_SERVER['REMOTE_ADDR'] = '192.168.0.1';
        $_SERVER['SERVER_ADDR'] = '192.168.0.2';
        unset($_SERVER['HTTP_HOST']);
        ServerRequestUtils::setTrustedProxies(array());
        $Request = $this->getUpdatedRequestObject($_SERVER);
        $this->assertEquals('192.168.0.2', $Request->getHost());

        $_SERVER['SERVER_NAME'] = 'elleven.fw';
        $Request = $this->getUpdatedRequestObject($_SERVER);
        $this->assertEquals('elleven.fw', $Request->getHost());

        $_SERVER['HTTP_HOST'] = 'localhost';
        $_SERVER['HTTP_X_FORWARDED_HOST'] = 'ellevenfw.com.br';
        $Request = $this->getUpdatedRequestObject($_SERVER);
        $this->assertEquals('localhost', $Request->getHost());

        ServerRequestUtils::setTrustedProxies(array('192.168.0.1'));
        $Request = $this->getUpdatedRequestObject($_SERVER);
        $this->assertEquals('ellevenfw.com.br', $Request->getHost());
    }

    public function testGetPort()
    {
        $_SERVER['REMOTE_ADDR'] = '192.168.0.1';
        $_SERVER['SERVER_PORT'] = '80';
        $_SERVER['HTTP_X_FORWARDED_PROTO'] = 'https';
        $_SERVER['HTTP_X_FORWARDED_PORT'] = '443';

        ServerRequestUtils::setTrustedProxies(array());
        $Request = $this->getUpdatedRequestObject($_SERVER);
        $this->assertEquals('80', $Request->getPort());

        ServerRequestUtils::setTrustedProxies(array('192.168.0.1'));
        $Request = $this->getUpdatedRequestObject($_SERVER);
        $this->assertEquals('443', $Request->getPort());
    }

    public function testGetScheme()
    {
        $_SERVER['REMOTE_ADDR'] = '192.168.0.1';
        $_SERVER['HTTPS'] = 'on';
        $_SERVER['HTTP_X_FORWARDED_PROTO'] = 'http';


        ServerRequestUtils::setTrustedProxies(array());
        $Request = $this->getUpdatedRequestObject($_SERVER);
        $this->assertEquals('https', $Request->getScheme());

        ServerRequestUtils::setTrustedProxies(array('192.168.0.1'));
        $Request = $this->getUpdatedRequestObject($_SERVER);
        $this->assertEquals('http', $Request->getScheme());
    }

    public function testGetDomain()
    {
        $_SERVER['HTTP_HOST'] = 'app.ellevenfw.com';

        $Request = $this->getUpdatedRequestObject($_SERVER);
        $this->assertEquals('ellevenfw.com', $Request->getDomain());

        $_SERVER['HTTP_HOST'] = 'app.ellevenfw.com.br';
        $Request = $this->getUpdatedRequestObject($_SERVER);
        $this->assertEquals('ellevenfw.com.br', $Request->getDomain(2));
    }

    public function testGetSubDomain()
    {
        $_SERVER['HTTP_HOST'] = 'app.ellevenfw.com';

        $Request = $this->getUpdatedRequestObject($_SERVER);
        $this->assertEquals(
            array('app'),
            $Request->getSubdomain()
        );

        $_SERVER['HTTP_HOST'] = 'test.app.ellevenfw.com';
        $Request = $this->getUpdatedRequestObject($_SERVER);
        $this->assertEquals(
            array('test','app'),
            $Request->getSubdomain()
        );

        $_SERVER['HTTP_HOST'] = 'test.app.ellevenfw.com.br';
        $Request = $this->getUpdatedRequestObject($_SERVER);
        $this->assertEquals(
            array('test','app'),
            $Request->getSubdomain(2)
        );

        $_SERVER['HTTP_HOST'] = 'ellevenfw.com.br';
        $Request = $this->getUpdatedRequestObject($_SERVER);
        $this->assertEquals(
            array(),
            $Request->getSubdomain(2)
        );
    }

    public function testGetAccepts()
    {
        $_SERVER['HTTP_ACCEPT'] = 'text/xml,application/xml;q=0.9,application/xhtml+xml,text/html,text/plain,image/png';

        $Request = $this->getUpdatedRequestObject($_SERVER);
        $result = $Request->getAccepts();
        $expected = array(
            'text/xml',
            'application/xhtml+xml',
            'text/html',
            'text/plain',
            'image/png',
            'application/xml'
        );
        $this->assertEquals($expected, $result);
    }

    public function testGetAcceptsWithWhitespace()
    {
        $_SERVER['HTTP_ACCEPT'] = 'text/xml  ,  text/html ,  text/plain,image/png';

        $Request = $this->getUpdatedRequestObject($_SERVER);
        $result = $Request->getAccepts();
        $expected = array(
            'text/xml',
            'text/html',
            'text/plain',
            'image/png'
        );
        $this->assertEquals($expected, $result);

        $this->assertTrue($Request->checkAcceptType('text/html'));
    }

    public function testGetAcceptWithQValueSorting()
    {
        $_SERVER['HTTP_ACCEPT'] = 'text/html;q=0.8,application/json;q=0.7,application/xml;q=1.0';

        $Request = $this->getUpdatedRequestObject($_SERVER);
        $result = $Request->getAccepts();
        $expected = array(
            'application/xml',
            'text/html',
            'application/json'
        );
        $this->assertEquals($expected, $result);
    }

    public function testCheckAcceptType()
    {
        $_SERVER['HTTP_ACCEPT'] = 'text/xml,application/xml;q=0.9,application/xhtml+xml,text/html,text/plain,image/png';
        $Request = $this->getUpdatedRequestObject($_SERVER);

        $result = $Request->checkAcceptType('text/html');
        $this->assertTrue($result);

        $result = $Request->checkAcceptType('image/gif');
        $this->assertFalse($result);
    }

    public function testParseAcceptWithQValue()
    {
        $string = 'text/html;q=0.8,application/json;q=0.7,application/xml;q=1.0,image/png';

        $Request = $this->getUpdatedRequestObject($_SERVER);
        $result = $Request->parseAcceptWithQualifier($string);
        $expected = [
            '1.0' => ['application/xml', 'image/png'],
            '0.8' => ['text/html'],
            '0.7' => ['application/json'],
        ];
        $this->assertEquals($expected, $result);
    }

    public function testParseAcceptNoQValues()
    {
        $string = 'application/json, text/plain, */*';

        $Request = $this->getUpdatedRequestObject($_SERVER);
        $result = $Request->parseAcceptWithQualifier($string);
        $expected = [
            '1.0' => ['application/json', 'text/plain', '*/*'],
        ];
        $this->assertEquals($expected, $result);
    }

    public function testParseAcceptIgnoreAcceptExtensions()
    {
        $string = 'application/json;level=1, text/plain, */*';

        $Request = $this->getUpdatedRequestObject($_SERVER);
        $result = $Request->parseAcceptWithQualifier($string);
        $expected = [
            '1.0' => ['application/json', 'text/plain', '*/*'],
        ];
        $this->assertEquals($expected, $result);
    }

    public function testParseAcceptInvalidSyntax()
    {
        $string = 'text/html,application/xhtml+xml,application/xml;image/png,image/jpeg,image/*;q=0.9,*/*;q=0.8';

        $Request = $this->getUpdatedRequestObject($_SERVER);
        $result = $Request->parseAcceptWithQualifier($string);
        $expected = [
            '1.0' => ['text/html', 'application/xhtml+xml', 'application/xml', 'image/jpeg'],
            '0.9' => ['image/*'],
            '0.8' => ['*/*'],
        ];
        $this->assertEquals($expected, $result);
    }

    public function testSetAcceptLanguages()
    {
        $Request = $this->getUpdatedRequestObject($_SERVER);

        $Request->setAcceptLanguages('inexistent,en-ca');
        $result = $Request->getAcceptLanguages();
        $expected = array('inexistent', 'en-ca');
        $this->assertEquals($expected, $result);

        $Request->setAcceptLanguages('es_mx,en_ca');
        $result = $Request->getAcceptLanguages();
        $expected = array('es-mx', 'en-ca');
        $this->assertEquals($expected, $result);

        $Request->setAcceptLanguages('en-US,en;q=0.8,pt-BR;q=0.6,pt;q=0.4');
        $result = $Request->getAcceptLanguages();
        $expected = array('en-us', 'en', 'pt-br', 'pt');
        $this->assertEquals($expected, $result);

        $Request->setAcceptLanguages('da, en-gb;q=0.8, en;q=0.7');
        $result = $Request->getAcceptLanguages();
        $expected = array('da', 'en-gb', 'en');
        $this->assertEquals($expected, $result);
    }

    public function testCheckAcceptLanguage()
    {
        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'es_mx,en_ca,pt_br';
        $Request = $this->getUpdatedRequestObject($_SERVER);
        $Request->setAcceptLanguages('es_mx,en_ca');

        $result = $Request->checkAcceptLanguage('es-mx');
        $this->assertTrue($result);

        $result = $Request->checkAcceptLanguage('es-MX');
        $this->assertTrue($result);

        $result = $Request->checkAcceptLanguage('es-mx');
        $this->assertTrue($result);

        $result = $Request->checkAcceptLanguage('es_MX');
        $this->assertFalse($result);

        $result = $Request->checkAcceptLanguage('pt-br');
        $this->assertFalse($result);

        $result = $Request->checkAcceptLanguage('pt-BR');
        $this->assertFalse($result);

        $result = $Request->checkAcceptLanguage('pt_BR');
        $this->assertFalse($result);
    }

    public function testGetProcol()
    {
        unset($_SERVER['SERVER_PROTOCOL']);
        $Request = $this->getUpdatedRequestObject($_SERVER);
        $this->assertEquals('1.1', $Request->getProtocol());

        $_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';
        $Request = $this->getUpdatedRequestObject($_SERVER);
        $this->assertEquals('1.1', $Request->getProtocol());
    }

    public function testGetEncoding()
    {
        $_SERVER['HTTP_ACCEPT_ENCODING'] = 'gzip, deflate';
        $Request = $this->getUpdatedRequestObject($_SERVER);

        $this->assertEquals(
            array('gzip', 'deflate'),
            $Request->getEncoding()
        );
    }

    public function testGetContentLength()
    {
        $Request = $this->getUpdatedRequestObject($_SERVER);

        $this->assertFalse($Request->getContentLength());

        $_SERVER['CONTENT_LENGTH'] = '900';
        $Request = $this->getUpdatedRequestObject($_SERVER);
        $this->assertEquals(900, $Request->getContentLength());

        $_SERVER['HTTP_CONTENT_LENGTH'] = '950';
        $Request = $this->getUpdatedRequestObject($_SERVER);
        $this->assertEquals(950, $Request->getContentLength());
    }

    public function testGetUser()
    {
        unset($_SERVER['PHP_AUTH_USER']);
        $Request = $this->getUpdatedRequestObject($_SERVER);
        $this->assertEquals('', $Request->getUser());

        $_SERVER['PHP_AUTH_USER'] = 'root';
        $Request = $this->getUpdatedRequestObject($_SERVER);
        $this->assertEquals('root', $Request->getUser());
    }

    public function testGetPassword()
    {
        unset($_SERVER['PHP_AUTH_PW']);
        $Request = $this->getUpdatedRequestObject($_SERVER);
        $this->assertEquals('', $Request->getPassword());

        $_SERVER['PHP_AUTH_PW'] = '123456';
        $Request = $this->getUpdatedRequestObject($_SERVER);
        $this->assertEquals('123456', $Request->getPassword());
    }

    public function testGetResquestTime()
    {
        $Request = $this->getUpdatedRequestObject($_SERVER);

        $time = time();
        $_SERVER['REQUEST_TIME'] = $time;
        $Request = $this->getUpdatedRequestObject($_SERVER);
        $this->assertEquals($time, $Request->getRequestTime());
    }

    public function testIsXmlHttpRequest()
    {
        $Request = $this->getUpdatedRequestObject($_SERVER);

        $this->assertFalse($Request->isXmlHttpRequest());

        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
        $Request = $this->getUpdatedRequestObject($_SERVER);
        $this->assertTrue($Request->isXmlHttpRequest());
    }

    public function testGetUrlPath()
    {
        $Request = $this->getUpdatedRequestObject($_SERVER);

        $_SERVER['HTTP_HOST'] = 'example.com';
        $_SERVER['HTTPS'] = 'Off';

        $_SERVER['PATH_INFO'] = '/';
        $Request = $this->getUpdatedRequestObject($_SERVER);
        $this->assertEquals('/', $Request->getPath());

        $_SERVER['PATH_INFO'] = '//';
        $Request = $this->getUpdatedRequestObject($_SERVER);
        $this->assertEquals('/', $Request->getPath());

        $_SERVER['PATH_INFO'] = '';
        $Request = $this->getUpdatedRequestObject($_SERVER);
        $this->assertEquals('/', $Request->getPath());

        $_SERVER['PATH_INFO'] = '/index';
        $Request = $this->getUpdatedRequestObject($_SERVER);
        $this->assertEquals('/index', $Request->getPath());

        $_SERVER['PATH_INFO'] = '/index/test';
        $Request = $this->getUpdatedRequestObject($_SERVER);
        $this->assertEquals('/index/test', $Request->getPath());

        $_SERVER['PATH_INFO'] = '/index.php';
        $Request = $this->getUpdatedRequestObject($_SERVER);
        $this->assertEquals('/', $Request->getPath());

        $_SERVER['PATH_INFO'] = '/index/var';
        $Request = $this->getUpdatedRequestObject($_SERVER);
        $this->assertEquals('/index/var', $Request->getPath());

        unset($_SERVER['PATH_INFO']);
        $_SERVER['REQUEST_URI'] = '/index/var';
        $Request = $this->getUpdatedRequestObject($_SERVER);
        $this->assertEquals('/index/var', $Request->getPath());

        $_SERVER['REQUEST_URI'] = 'http://example.com/index/var';
        $Request = $this->getUpdatedRequestObject($_SERVER);
        $this->assertEquals('/index/var', $Request->getPath());

        $_SERVER['REQUEST_URI'] = 'http://example.com/index/var?query=value';
        $Request = $this->getUpdatedRequestObject($_SERVER);
        $this->assertEquals('/index/var', $Request->getPath());

        $_SERVER['REQUEST_URI'] = '/index/var?query=value';
        $Request = $this->getUpdatedRequestObject($_SERVER);
        $this->assertEquals('/index/var', $Request->getPath());

        $_SERVER['REQUEST_URI'] = '/index/var?query=http://example.com';
        $Request = $this->getUpdatedRequestObject($_SERVER);
        $this->assertEquals('/index/var', $Request->getPath());

        unset($_SERVER['REQUEST_URI']);
        $_SERVER['PHP_SELF'] = '/index.php/index/var';
        $_SERVER['SCRIPT_NAME'] = '/index.php';
        $Request = $this->getUpdatedRequestObject($_SERVER);
        $this->assertEquals('/index/var', $Request->getPath());

        unset($_SERVER['PHP_SELF']);
        unset($_SERVER['SCRIPT_NAME']);
        $_SERVER['HTTP_X_REWRITE_URL'] = '/index/var';
        $Request = $this->getUpdatedRequestObject($_SERVER);
        $this->assertEquals('/index/var', $Request->getPath());

        unset($_SERVER['HTTP_X_REWRITE_URL']);
        $_SERVER['argv'] = array('/index/var');
        $_SERVER['argc'] = 1;
        $Request = $this->getUpdatedRequestObject($_SERVER);
        $this->assertEquals('/index/var', $Request->getPath());
    }

    public function testeGetQueryString()
    {
        $_GET = array();
        $Request = $this->getUpdatedRequestObject($_SERVER);
        $this->assertEquals('', $Request->getQueryString());

        $_GET = array(
            'test' => 'value'
        );
        $Request = $this->getUpdatedRequestObject($_SERVER);
        $this->assertEquals(
            'test=value',
            $Request->getQueryString()
        );

        $_GET = array(
            'test' => 'value',
            'test2' => 'value2'
        );
        $Request = $this->getUpdatedRequestObject($_SERVER);
        $this->assertEquals(
            'test=value&test2=value2',
            $Request->getQueryString()
        );

        $this->assertEquals(
            'test=value&test2=value2',
            $Request->getQueryString()
        );
    }

    public function testGetFullUrlWithCompleteParams()
    {
        $_SERVER['HTTP_HOST'] = 'subdomain.example.com';
        $_SERVER['HTTPS'] = 'Off';
        $_SERVER['SERVER_PORT'] = '8080';
        $_SERVER['PHP_AUTH_USER'] = 'user';
        $_SERVER['PHP_AUTH_PW'] = '123456';
        $_GET = array(
            'test' => 'value',
            'test2' => 'value2'
        );
        $Request = $this->getUpdatedRequestObject($_SERVER);
        $this->assertEquals(
            'http://user:123456@subdomain.example.com:8080/?test=value&test2=value2',
            $Request->getFullUrl()
        );
    }

    public function testGetFullUrlWithoutPort()
    {
        $_SERVER['HTTP_HOST'] = 'subdomain.example.com';
        $_SERVER['HTTPS'] = 'Off';
        $_SERVER['SERVER_PORT'] = '80';
        $_SERVER['PHP_AUTH_USER'] = 'user';
        $_SERVER['PHP_AUTH_PW'] = '123456';
        $_GET = array(
            'test' => 'value',
            'test2' => 'value2'
        );
        $Request = $this->getUpdatedRequestObject($_SERVER);
        $this->assertEquals(
            'http://user:123456@subdomain.example.com/?test=value&test2=value2',
            $Request->getFullUrl()
        );
    }

    public function testGetFullUrlWithoutPortAndQuerystring()
    {
        $_SERVER['HTTP_HOST'] = 'subdomain.example.com';
        $_SERVER['HTTPS'] = 'Off';
        $_SERVER['SERVER_PORT'] = '80';
        $_SERVER['PHP_AUTH_USER'] = 'user';
        $_SERVER['PHP_AUTH_PW'] = '123456';
        $_GET = array();
        $Request = $this->getUpdatedRequestObject($_SERVER);
        $this->assertEquals(
            'http://user:123456@subdomain.example.com/',
            $Request->getFullUrl()
        );
    }

    public function testGetFullUrlOnlySchemeAndHost()
    {
        $_SERVER['HTTP_HOST'] = 'subdomain.example.com';
        $_SERVER['HTTPS'] = 'Off';
        $_SERVER['SERVER_PORT'] = '80';
        $_SERVER['PHP_AUTH_USER'] = '';
        $_SERVER['PHP_AUTH_PW'] = '';
        $_GET = array();
        $Request = $this->getUpdatedRequestObject($_SERVER);
        $this->assertEquals(
            'http://subdomain.example.com/',
            $Request->getFullUrl()
        );
        $this->assertEquals(
            'http://subdomain.example.com/',
            $Request->getFullUrl()
        );
    }

    public function testGetFullUrlOnlySchemeAndHostWithHttps()
    {
        $_SERVER['HTTP_HOST'] = 'subdomain.example.com';
        $_SERVER['HTTPS'] = 'On';
        $_SERVER['SERVER_PORT'] = '80';
        $_SERVER['PHP_AUTH_USER'] = '';
        $_SERVER['PHP_AUTH_PW'] = '';
        $_GET = array();
        $Request = $this->getUpdatedRequestObject($_SERVER);
        $this->assertEquals(
            'http://subdomain.example.com/',
            $Request->getFullUrl()
        );
    }

    public function testInput()
    {
        $this->assertEquals('', $this->object->getInput());

        $this->assertEquals('', $this->object->getInput('json_decode'));
    }
}
