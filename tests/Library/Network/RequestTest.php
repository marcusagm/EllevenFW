<?php

namespace EllevenFw\Test\Library\Network;

use EllevenFw\Library\Basic\Cookie;
use EllevenFw\Library\Basic\Session;
use EllevenFw\Library\Network\Request;

/**
 * Generated by PHPUnit_SkeletonGenerator on 2015-09-24 at 22:46:22.
 */
class RequestTest extends \PHPUnit_Framework_TestCase
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
    protected function setUp()
    {
        $this->object = new Request();
        $this->originalServer = $_SERVER;
        $this->originalEnvironment = $_ENV;
        parent::setUp();
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        $_SERVER = $this->originalServer;
        $_ENV = $this->originalEnvironment;
        parent::tearDown();
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
        $Request = new Request();
        $this->assertFalse($Request->getQueryVar('test'));
        $Request->setQuery(array(
            'test' => 'test success'
        ));
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
        $Request = new Request();
        $this->assertFalse($Request->getPostVar('test'));
        $Request->setPost(array(
            'test' => 'test success'
        ));
        $this->assertEquals(
            'test success',
            $Request->getPostVar('test')
        );
    }

    public function testGetFiles()
    {
        $this->assertEquals(
            $_FILES,
            $this->object->getFiles()
        );
    }

    /**
     *
     * @depends testGetFiles
     */
    public function testGetFilesVar()
    {
        $Request = new Request();
        $this->assertFalse($Request->getFilesVar('test'));
        $Request->setFiles(array(
            'test' => 'test success'
        ));
        $this->assertEquals(
            'test success',
            $Request->getFilesVar('test')
        );
    }

    public function testGetServer()
    {
        $this->assertEquals(
            $_SERVER,
            $this->object->getServer()
        );
    }

    /**
     *
     * @depends testGetServer
     */
    public function testGetServerVar()
    {
        $Request = new Request();
        $this->assertFalse($Request->getServerVar('test'));
        $Request->setServer(array(
            'test' => 'test success'
        ));
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
        $Request = new Request();
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
        $this->assertInstanceOf(
            'EllevenFw\Library\Basic\Cookie',
            $Cookie
        );
        $this->assertEquals(
            new Cookie(),
            $Cookie
        );
    }

    public function testGetSession()
    {
        $Session = $this->object->getSession();
        $this->assertInstanceOf(
            'EllevenFw\Library\Basic\Session',
            $Session
        );
        $this->assertEquals(
            new Session(),
            $Session
        );
    }

    public function testTrustedProxies()
    {
        $proxies = array(
            '192.168.0.1',
            '192.168.0.2',
            '192.168.56.2'
        );
        $this->object->setTrustedProxies($proxies);
        $this->assertEquals(
            $proxies,
            $this->object->getTrustedProxies()
        );
    }

    public function testIsFromTrustedProxy()
    {
        $_SERVER['REMOTE_ADDR'] = '192.168.56.1';
        $Request = new Request();
        $Request->setTrustedProxies(array('192.168.56.1'));
        $this->assertTrue($Request->isFromTrustedProxy());
    }

    public function testGetContentType()
    {
        $Request = new Request();

        unset($_SERVER['CONTENT_TYPE']);
        unset($_SERVER['HTTP_CONTENT_TYPE']);
        $this->assertFalse($Request->getContentType());

        $_SERVER['CONTENT_TYPE'] = 'application/xml';
        $Request->setServer($_SERVER);
        $this->assertEquals(
            'application/xml',
            $Request->getContentType()
        );

        $_SERVER['HTTP_CONTENT_TYPE'] = 'application/json';
        $Request->setServer($_SERVER);
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

        $Request = new Request();
        $Request->setTrustedProxies(array('192.168.1.3'));

        $this->assertEquals('192.168.1.5', $Request->getClientIp());

        $Request->setTrustedProxies(array());
        $this->assertEquals('192.168.1.2', $Request->getClientIp());

        $_SERVER['HTTP_X_FORWARDED_FOR'] = '';
        $Request->setServer($_SERVER);
        $this->assertEquals('192.168.1.2', $Request->getClientIp());

        $_SERVER['HTTP_CLIENT_IP'] = '';
        $Request->setServer($_SERVER);
        $this->assertEquals('192.168.1.3', $Request->getClientIp());

        $_SERVER['HTTP_CLIENTADDRESS'] = '10.0.1.2, 10.0.1.1';
        $Request->setServer($_SERVER);
        $this->assertEquals('10.0.1.2', $Request->getClientIp());
    }

    /**
     * @expectedException DomainException
     * @expectedExceptionMessage O IP "192.168.1" é inválido.
     */
    public function testGetInavlidIp()
    {
        $_SERVER['REMOTE_ADDR'] = '192.168.1';

        $Request = new Request();
        $Request->getClientIp();
    }

    public function testGetMethod()
    {
        $Request = new Request();

        $_SERVER['REQUEST_METHOD'] = 'get';
        $Request->setServer($_SERVER);
        $this->assertEquals('GET', $Request->getMethod());

        $_SERVER['REQUEST_METHOD'] = 'post';
        $Request->setServer($_SERVER);
        $this->assertEquals('POST', $Request->getMethod());

        $_SERVER['REQUEST_METHOD'] = 'post';
        $_SERVER['X-HTTP-METHOD-OVERRIDE'] = 'put';
        $Request->setServer($_SERVER);
        $this->assertEquals('PUT', $Request->getMethod());
    }

    public function testIsMethod()
    {
        $Request = new Request();

        $_SERVER['REQUEST_METHOD'] = 'get';
        $Request->setServer($_SERVER);
        $this->assertTrue($Request->isMethod('get'));

        $_SERVER['REQUEST_METHOD'] = 'post';
        $Request->setServer($_SERVER);
        $this->assertTrue($Request->isMethod('post'));

        $_SERVER['REQUEST_METHOD'] = 'post';
        $_SERVER['X-HTTP-METHOD-OVERRIDE'] = 'put';
        $Request->setServer($_SERVER);
        $this->assertTrue($Request->isMethod('put'));
        $this->assertFalse($Request->isMethod('post'));
    }

    public function testIsMethodSafe()
    {
        $Request = new Request();

        $_SERVER['REQUEST_METHOD'] = 'get';
        $Request->setServer($_SERVER);
        $this->assertTrue($Request->isMethodSafe('get'));

        $_SERVER['REQUEST_METHOD'] = 'HEAD';
        $Request->setServer($_SERVER);
        $this->assertTrue($Request->isMethodSafe('head'));

        $_SERVER['REQUEST_METHOD'] = 'post';
        $Request->setServer($_SERVER);
        $this->assertFalse($Request->isMethodSafe('post'));
    }

    public function testGetHost()
    {
        $_SERVER['REMOTE_ADDR'] = '192.168.0.1';
        $_SERVER['SERVER_ADDR'] = '192.168.0.2';

        $Request = new Request();
        $this->assertEquals('192.168.0.2', $Request->getHost());

        $_SERVER['SERVER_NAME'] = 'elleven.fw';
        $Request->setServer($_SERVER);
        $this->assertEquals('elleven.fw', $Request->getHost());

        $_SERVER['HTTP_HOST'] = 'localhost';
        $_SERVER['HTTP_X_FORWARDED_HOST'] = 'ellevenfw.com.br';
        $Request->setServer($_SERVER);
        $this->assertEquals('localhost', $Request->getHost());

        $Request->setTrustedProxies(array('192.168.0.1'));
        $this->assertEquals('ellevenfw.com.br', $Request->getHost());
    }

    public function testGetPort()
    {
        $_SERVER['REMOTE_ADDR'] = '192.168.0.1';
        $_SERVER['SERVER_PORT'] = '80';
        $_SERVER['HTTP_X_FORWARDED_PORT'] = '443';

        $Request = new Request();

        $this->assertEquals('80', $Request->getPort());

        $Request->setTrustedProxies(array('192.168.0.1'));
        $this->assertEquals('443', $Request->getPort());
    }

    public function testGetScheme()
    {
        $_SERVER['REMOTE_ADDR'] = '192.168.0.1';
        $_SERVER['HTTPS'] = 'on';
        $_SERVER['HTTP_X_FORWARDED_PROTO'] = 'http';

        $Request = new Request();

        $this->assertEquals('https', $Request->getScheme());

        $Request->setTrustedProxies(array('192.168.0.1'));
        $this->assertEquals('http', $Request->getScheme());
    }

    public function testGetDomain()
    {
        $_SERVER['HTTP_HOST'] = 'app.ellevenfw.com';

        $Request = new Request();
        $this->assertEquals('ellevenfw.com', $Request->getDomain());

        $_SERVER['HTTP_HOST'] = 'app.ellevenfw.com.br';
        $Request->setServer($_SERVER);
        $this->assertEquals('ellevenfw.com.br', $Request->getDomain(2));
    }

    public function testGetSubDomain()
    {
        $_SERVER['HTTP_HOST'] = 'app.ellevenfw.com';

        $Request = new Request();
        $this->assertEquals(
            array('app'),
            $Request->getSubdomain()
        );

        $_SERVER['HTTP_HOST'] = 'test.app.ellevenfw.com';
        $Request->setServer($_SERVER);
        $this->assertEquals(
            array('test','app'),
            $Request->getSubdomain()
        );

        $_SERVER['HTTP_HOST'] = 'test.app.ellevenfw.com.br';
        $Request->setServer($_SERVER);
        $this->assertEquals(
            array('test','app'),
            $Request->getSubdomain(2)
        );

        $_SERVER['HTTP_HOST'] = 'ellevenfw.com.br';
        $Request->setServer($_SERVER);
        $this->assertEquals(
            array(),
            $Request->getSubdomain(2)
        );
    }

    public function testGetAccepts()
    {
        $_SERVER['HTTP_ACCEPT'] = 'text/xml,application/xml;q=0.9,application/xhtml+xml,text/html,text/plain,image/png';

        $Request = new Request();
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

        $Request = new Request();
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

        $Request = new Request();
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
        $Request = new Request();

        $result = $Request->checkAcceptType('text/html');
        $this->assertTrue($result);

        $result = $Request->checkAcceptType('image/gif');
        $this->assertFalse($result);
    }

    public function testParseAcceptWithQValue()
    {
        $string = 'text/html;q=0.8,application/json;q=0.7,application/xml;q=1.0,image/png';

        $Request = new Request();
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

        $Request = new Request();
        $result = $Request->parseAcceptWithQualifier($string);
        $expected = [
            '1.0' => ['application/json', 'text/plain', '*/*'],
        ];
        $this->assertEquals($expected, $result);
    }

    public function testParseAcceptIgnoreAcceptExtensions()
    {
        $string = 'application/json;level=1, text/plain, */*';

        $Request = new Request();
        $result = $Request->parseAcceptWithQualifier($string);
        $expected = [
            '1.0' => ['application/json', 'text/plain', '*/*'],
        ];
        $this->assertEquals($expected, $result);
    }

    public function testParseAcceptInvalidSyntax()
    {
        $string = 'text/html,application/xhtml+xml,application/xml;image/png,image/jpeg,image/*;q=0.9,*/*;q=0.8';

        $Request = new Request();
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
        $Request = new Request();

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
        $Request = new Request();
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
        $_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';
        $Request = new Request();
        $this->assertEquals('HTTP/1.1', $Request->getProtocol());
    }

    public function testGetEncoding()
    {
        $_SERVER['HTTP_ACCEPT_ENCODING'] = 'gzip, deflate';
        $Request = new Request();

        $this->assertEquals(
            array('gzip', 'deflate'),
            $Request->getEncoding()
        );
    }

    public function testGetContentLength()
    {
        $Request = new Request();

        $this->assertFalse($Request->getContentLength());

        $_SERVER['CONTENT_LENGTH'] = '900';
        $Request->setServer($_SERVER);
        $this->assertEquals(900, $Request->getContentLength());

        $_SERVER['HTTP_CONTENT_LENGTH'] = '950';
        $Request->setServer($_SERVER);
        $this->assertEquals(950, $Request->getContentLength());
    }

    public function testGetUser()
    {
        $Request = new Request();
        $this->assertFalse($Request->getUser());

        $_SERVER['PHP_AUTH_USER'] = 'root';
        $Request->setServer($_SERVER);
        $this->assertEquals('root', $Request->getUser());
    }

    public function testGetPassword()
    {
        $Request = new Request();
        $this->assertFalse($Request->getPassword());

        $_SERVER['PHP_AUTH_PW'] = '123456';
        $Request->setServer($_SERVER);
        $this->assertEquals('123456', $Request->getPassword());
    }

    public function testGetResquestTime()
    {
        $Request = new Request();

        $time = time();
        $_SERVER['REQUEST_TIME'] = $time;
        $Request->setServer($_SERVER);
        $this->assertEquals($time, $Request->getRequestTime());
    }

    public function testIsXmlHttpRequest()
    {
        $Request = new Request();

        $this->assertFalse($Request->isXmlHttpRequest());

        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
        $Request->setServer($_SERVER);
        $this->assertTrue($Request->isXmlHttpRequest());
    }

    public function testGetUrlPath()
    {
        $Request = new Request();

        $_SERVER['HTTP_HOST'] = 'example.com';
        $_SERVER['HTTPS'] = 'Off';

        $_SERVER['PATH_INFO'] = '/';
        $Request->setServer($_SERVER);
        $Request->resetUrlPath();
        $this->assertEquals('/', $Request->getUrlPath());

        $_SERVER['PATH_INFO'] = '//';
        $Request->setServer($_SERVER);
        $Request->resetUrlPath();
        $this->assertEquals('/', $Request->getUrlPath());

        $_SERVER['PATH_INFO'] = '';
        $Request->setServer($_SERVER);
        $Request->resetUrlPath();
        $this->assertEquals('/', $Request->getUrlPath());

        $_SERVER['PATH_INFO'] = '/index';
        $Request->setServer($_SERVER);
        $Request->resetUrlPath();
        $this->assertEquals('/index', $Request->getUrlPath());

        $_SERVER['PATH_INFO'] = '/index/test';
        $Request->setServer($_SERVER);
        $this->assertEquals('/index', $Request->getUrlPath());

        $_SERVER['PATH_INFO'] = '/index.php';
        $Request->setServer($_SERVER);
        $Request->resetUrlPath();
        $this->assertEquals('/', $Request->getUrlPath());

        $_SERVER['PATH_INFO'] = '/index/var';
        $Request->setServer($_SERVER);
        $Request->resetUrlPath();
        $this->assertEquals('/index/var', $Request->getUrlPath());

        unset($_SERVER['PATH_INFO']);
        $_SERVER['REQUEST_URI'] = '/index/var';
        $Request->setServer($_SERVER);
        $Request->resetUrlPath();
        $this->assertEquals('/index/var', $Request->getUrlPath());

        $_SERVER['REQUEST_URI'] = 'http://example.com/index/var';
        $Request->setServer($_SERVER);
        $Request->resetUrlPath();
        $this->assertEquals('/index/var', $Request->getUrlPath());

        $_SERVER['REQUEST_URI'] = 'http://example.com/index/var?query=value';
        $Request->setServer($_SERVER);
        $Request->resetUrlPath();
        $this->assertEquals('/index/var', $Request->getUrlPath());

        $_SERVER['REQUEST_URI'] = '/index/var?query=value';
        $Request->setServer($_SERVER);
        $Request->resetUrlPath();
        $this->assertEquals('/index/var', $Request->getUrlPath());

        $_SERVER['REQUEST_URI'] = '/index/var?query=http://example.com';
        $Request->setServer($_SERVER);
        $Request->resetUrlPath();
        $this->assertEquals('/index/var', $Request->getUrlPath());

        unset($_SERVER['REQUEST_URI']);
        $_SERVER['PHP_SELF'] = '/index.php/index/var';
        $_SERVER['SCRIPT_NAME'] = '/index.php';
        $Request->setServer($_SERVER);
        $Request->resetUrlPath();
        $this->assertEquals('/index/var', $Request->getUrlPath());

        unset($_SERVER['PHP_SELF']);
        unset($_SERVER['SCRIPT_NAME']);
        $_SERVER['HTTP_X_REWRITE_URL'] = '/index/var';
        $Request->setServer($_SERVER);
        $Request->resetUrlPath();
        $this->assertEquals('/index/var', $Request->getUrlPath());

        unset($_SERVER['HTTP_X_REWRITE_URL']);
        $_SERVER['argv'] = array('/index/var');
        $_SERVER['argc'] = 1;
        $Request->setServer($_SERVER);
        $Request->resetUrlPath();
        $this->assertEquals('/index/var', $Request->getUrlPath());
    }

    public function testeGetQueryString()
    {
        $Request = new Request();

        $_GET = array();
        $Request->setServer($_SERVER);
        $this->assertEquals('', $Request->getQueryString());

        $_GET = array(
            'test' => 'value'
        );
        $Request = new Request();
        $this->assertEquals(
            'test=value',
            $Request->getQueryString()
        );

        $_GET = array(
            'test' => 'value',
            'test2' => 'value2'
        );
        $Request = new Request();
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
        $Request = new Request();
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
        $Request = new Request();
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
        $Request = new Request();
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
        $_SERVER['PHP_AUTH_USER'] = null;
        $_SERVER['PHP_AUTH_PW'] = null;
        $_GET = array();
        $Request = new Request();
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
        $_SERVER['PHP_AUTH_USER'] = null;
        $_SERVER['PHP_AUTH_PW'] = null;
        $_GET = array();
        $Request = new Request();
        $this->assertEquals(
            'http://subdomain.example.com/',
            $Request->getFullUrl()
        );
    }

    public function testInput()
    {
        $this->assertEquals('', $this->object->getInput());

        $this->object->setInput('{"name":"value"}');
        $this->assertEquals('', $this->object->getInput('json_decode'));

        $this->object->setInput('{"name":"value"}');
        $this->assertEquals('', $this->object->getInput('json_decode'));
    }
}