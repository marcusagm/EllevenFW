<?php

namespace EllevenFw\Library\Network;

/**
 * Generated by PHPUnit_SkeletonGenerator on 2017-10-02 at 06:14:26.
 */
class ServerRequestUtilsTest extends \PHPUnit_Framework_TestCase
{

    protected $server = array();



    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->server['SERVER_PROTOCOL'] = '1.1';
        $this->server['HTTP_ACCEPT'] = 'application/json';
        $this->server['REQUEST_METHOD'] = 'POST';
        $this->server['REQUEST_URI'] = '';
        $this->server['QUERY_STRING'] = '';
        $this->server['HTTP_HOST'] = 'subdomain.example.com';
        $this->server['HTTPS'] = 'Off';
        $this->server['SERVER_PORT'] = '';
        $this->server['PHP_AUTH_USER'] = '';
        $this->server['PHP_AUTH_PW'] = '';
        $this->server['PHP_SELF'] = '/index.php/index/var';
        $this->server['SCRIPT_NAME'] = '/index.php';
        $this->server['REMOTE_ADDR'] = '192.168.56.1';
        $this->server['PATH'] = '';
        $this->server['argv'] = array();
        $this->server['argc'] = 0;
        $_GET = array();
        $_POST = array();

        parent::setUp();
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        parent::tearDown();
    }

    public function testCreateFromGlobals()
    {
        $server = ServerRequestUtils::filterInputGlobals(INPUT_SERVER);
        $get = ServerRequestUtils::filterInputGlobals(INPUT_GET);
        $post = ServerRequestUtils::filterInputGlobals(INPUT_POST);
        $cookies = ServerRequestUtils::filterInputGlobals(INPUT_COOKIE);

        $Request = ServerRequestUtils::createFromGlobals();
        $this->assertEquals($server, $Request->getServerParams());
        $this->assertEquals($get, $Request->getQueryParams());
        $this->assertEquals($post, $Request->getParsedBody());
        $this->assertEquals($cookies, $Request->getCookieParams());
    }

    public function testFilterInputGlobals()
    {
        $expectedServer = filter_input_array(INPUT_SERVER);
        $this->assertEquals($expectedServer, ServerRequestUtils::filterInputGlobals(INPUT_SERVER));
        $this->assertEquals(array(), ServerRequestUtils::filterInputGlobals(3));
    }

    public function testFilterHeaders()
    {
        $server = [
            'HTTP_X_FOO_BAR' => 'nonprefixed',
            'REDIRECT_HTTP_AUTHORIZATION' => 'token',
            'REDIRECT_HTTP_X_FOO_BAR' => 'prefixed',
            'CONTENT_TYPE' => 'application/xml'
        ];
        $expected = [
            'authorization' => 'token',
            'x-foo-bar' => 'nonprefixed',
            'content-type' => 'application/xml'
        ];
        $this->assertEquals($expected, ServerRequestUtils::filterHeaders($server));
    }

    public function testTrustedProxies()
    {
        $proxies = array(
            '192.168.0.1',
            '192.168.0.2',
            '192.168.56.2'
        );
        ServerRequestUtils::setTrustedProxies($proxies);
        $this->assertEquals(
            $proxies,
            ServerRequestUtils::getTrustedProxies()
        );
    }

    public function testIsFromTrustedProxy()
    {
        $server = $this->server;
        $server['REMOTE_ADDR'] = '192.168.56.1';

        ServerRequestUtils::setTrustedProxies(array('192.168.56.1'));
        $this->assertTrue(ServerRequestUtils::isFromTrustedProxy($server));
    }

    public function testIsNotFromTrustedProxy()
    {
        $server = $this->server;
        $server['REMOTE_ADDR'] = '192.168.56.1';

        ServerRequestUtils::setTrustedProxies(array('192.168.56.2'));
        $this->assertFalse(ServerRequestUtils::isFromTrustedProxy($server));
    }

    public function testIsNotFromTrustedProxyWithoutProxyList()
    {
        $server = $this->server;
        $server['REMOTE_ADDR'] = '192.168.56.1';
        ServerRequestUtils::setTrustedProxies(array());
        $this->assertFalse(ServerRequestUtils::isFromTrustedProxy($server));
    }

    public function testExtractMethod()
    {
        $server = $this->server;
        unset($server['REQUEST_METHOD']);
        $this->assertEquals('GET', ServerRequestUtils::extractMethod($server));

        $server['REQUEST_METHOD'] = 'get';
        $this->assertEquals('GET', ServerRequestUtils::extractMethod($server));

        $server['REQUEST_METHOD'] = 'post';
        $this->assertEquals('POST', ServerRequestUtils::extractMethod($server));
    }

    public function testExtractMethodOverrided()
    {
        $server = $this->server;
        $server['REQUEST_METHOD'] = 'post';
        $server['X-HTTP-METHOD-OVERRIDE'] = 'put';
        $this->assertEquals('PUT', ServerRequestUtils::extractMethod($server));
    }

    public function testExtractHostFromServerAddr()
    {
        $server = $this->server;
        $server['SERVER_ADDR'] = '192.168.0.2';
        unset($server['SERVER_NAME']);
        unset($server['HTTP_HOST']);
        $this->assertEquals('192.168.0.2', ServerRequestUtils::extractHost($server));
    }

    public function testExtractHostFromServerName()
    {
        $server = $this->server;
        unset($server['HTTP_HOST']);
        $server['SERVER_NAME'] = 'elleven.fw';
        $this->assertEquals('elleven.fw', ServerRequestUtils::extractHost($server));
    }

    public function testExtractHostFromHttpHost()
    {
        $server = $this->server;
        $server['REMOTE_ADDR'] = '192.168.0.1';
        $server['HTTP_HOST'] = 'localhost';
        $server['HTTP_X_FORWARDED_HOST'] = 'ellevenfw.com.br';
        ServerRequestUtils::setTrustedProxies(array());
        $this->assertEquals('localhost', ServerRequestUtils::extractHost($server));
    }

    public function testExtractHostFromHttpHostOverrided()
    {
        $server = $this->server;
        $server['REMOTE_ADDR'] = '192.168.0.1';
        $server['HTTP_HOST'] = 'localhost';
        $server['HTTP_X_FORWARDED_HOST'] = 'ellevenfw.com.br';

        ServerRequestUtils::setTrustedProxies(array('192.168.0.1'));
        $this->assertEquals('ellevenfw.com.br', ServerRequestUtils::extractHost($server));
    }

    public function testExtractPort()
    {
        $server = $this->server;
        $server['REMOTE_ADDR'] = '192.168.0.1';
        $server['SERVER_PORT'] = '80';
        $server['HTTP_X_FORWARDED_PORT'] = '443';

        ServerRequestUtils::setTrustedProxies(array());
        $this->assertEquals('80', ServerRequestUtils::extractPort($server));
    }

    public function testExtractPortFromTrustedProxies()
    {
        $server = $this->server;
        $server['REMOTE_ADDR'] = '192.168.0.1';
        $server['SERVER_PORT'] = '80';
        $server['HTTP_X_FORWARDED_PORT'] = '443';

        ServerRequestUtils::setTrustedProxies(array('192.168.0.1'));
        $this->assertEquals('443', ServerRequestUtils::extractPort($server));
    }

    public function testExtractScheme()
    {
        $server = $this->server;
        $server['REMOTE_ADDR'] = '192.168.0.1';
        $server['HTTPS'] = 'on';
        $server['HTTP_X_FORWARDED_PROTO'] = 'http';

        ServerRequestUtils::setTrustedProxies(array());
        $this->assertEquals('https', ServerRequestUtils::extractScheme($server));
    }

    public function testExtractSchemeFromTrustedProxies()
    {
        $server = $this->server;
        $server['REMOTE_ADDR'] = '192.168.0.1';
        $server['HTTPS'] = 'on';
        $server['HTTP_X_FORWARDED_PROTO'] = 'http';

        ServerRequestUtils::setTrustedProxies(array('192.168.0.1'));
        $this->assertEquals('http', ServerRequestUtils::extractScheme($server));
    }

    public function testExtractProcol()
    {
        $server = $this->server;
        unset($server['SERVER_PROTOCOL']);
        $this->assertEquals('1.1', ServerRequestUtils::extractProtocol($server));

        $server['SERVER_PROTOCOL'] = 'HTTP/1.1';
        $this->assertEquals('1.1', ServerRequestUtils::extractProtocol($server));
    }

    /**
     * @expectedException UnexpectedValueException
     */
    public function testInvalidProtocol()
    {
        $server = $this->server;
        $server['SERVER_PROTOCOL'] = 'abc';
        $this->assertEquals('1.1', ServerRequestUtils::extractProtocol($server));
    }

    public function testExtractUser()
    {
        $server = $this->server;
        unset($server['PHP_AUTH_USER']);
        $this->assertFalse(ServerRequestUtils::extractUser($server));

        $server['PHP_AUTH_USER'] = 'root';
        $this->assertEquals('root', ServerRequestUtils::extractUser($server));
    }

    public function testExtractPassword()
    {
        $server = $this->server;
        unset($server['PHP_AUTH_PW']);
        $this->assertFalse(ServerRequestUtils::extractPassword($server));

        $server['PHP_AUTH_PW'] = '123456';
        $this->assertEquals('123456', ServerRequestUtils::extractPassword($server));
    }

    public function testExtractPath()
    {
        $server = $this->server;
        $server['HTTP_HOST'] = 'example.com';
        $server['HTTPS'] = 'Off';

        $server['PATH_INFO'] = '/';
        $this->assertEquals('/', ServerRequestUtils::extractPath($server));

        $server['PATH_INFO'] = '//';
        $this->assertEquals('/', ServerRequestUtils::extractPath($server));

        $server['PATH_INFO'] = '';
        $this->assertEquals('/', ServerRequestUtils::extractPath($server));

        $server['PATH_INFO'] = '/index';
        $this->assertEquals('/index', ServerRequestUtils::extractPath($server));

        $server['PATH_INFO'] = '/index/test';
        $this->assertEquals('/index/test', ServerRequestUtils::extractPath($server));

        $server['PATH_INFO'] = '/index.php';
        $this->assertEquals('/', ServerRequestUtils::extractPath($server));

        $server['PATH_INFO'] = '/index/var';
        $this->assertEquals('/index/var', ServerRequestUtils::extractPath($server));

        unset($server['PATH_INFO']);
        $server['REQUEST_URI'] = '/index/var';
        $this->assertEquals('/index/var', ServerRequestUtils::extractPath($server));

        $server['REQUEST_URI'] = 'http://example.com/index/var';
        $this->assertEquals('/index/var', ServerRequestUtils::extractPath($server));

        $server['REQUEST_URI'] = 'http://example.com/index/var?query=value';
        $this->assertEquals('/index/var', ServerRequestUtils::extractPath($server));

        $server['REQUEST_URI'] = '/index/var?query=value';
        $this->assertEquals('/index/var', ServerRequestUtils::extractPath($server));

        $server['REQUEST_URI'] = '/index/var?query=http://example.com';
        $this->assertEquals('/index/var', ServerRequestUtils::extractPath($server));

        unset($server['REQUEST_URI']);
        $server['PHP_SELF'] = '/index.php/index/var';
        $server['SCRIPT_NAME'] = '/index.php';
        $this->assertEquals('/index/var', ServerRequestUtils::extractPath($server));

        unset($server['PHP_SELF']);
        unset($server['SCRIPT_NAME']);
        $server['HTTP_X_REWRITE_URL'] = '/index/var';
        $this->assertEquals('/index/var', ServerRequestUtils::extractPath($server));

        unset($server['HTTP_X_REWRITE_URL']);
        $server['argv'] = array('/index/var');
        $server['argc'] = 1;
        $this->assertEquals('/index/var', ServerRequestUtils::extractPath($server));
    }

    public function testeMakeQueryString()
    {
        $server = $this->server;
        $query = array();
        $this->assertEquals('', ServerRequestUtils::makeQueryString($query));

        $query = array('test' => 'value');
        $this->assertEquals(
            'test=value',
            ServerRequestUtils::makeQueryString($query)
        );

        $query = array(
            'test' => 'value',
            'test2' => 'value2'
        );
        $this->assertEquals(
            new Uri('test=value&test2=value2'),
            ServerRequestUtils::makeQueryString($query)
        );
    }

    public function testExtractUriWithCompleteParams()
    {
        $server = $this->server;
        $server['HTTP_HOST'] = 'subdomain.example.com';
        $server['HTTPS'] = 'Off';
        $server['SERVER_PORT'] = '8080';
        $server['PHP_AUTH_USER'] = 'user';
        $server['PHP_AUTH_PW'] = '123456';
        $query = array(
            'test' => 'value',
            'test2' => 'value2'
        );
        $this->assertEquals(
            new Uri('http://user:123456@subdomain.example.com:8080/?test=value&test2=value2'),
            ServerRequestUtils::extractUri($server, $query)
        );
    }

    public function testExtractUriWithoutPort()
    {
        $server = $this->server;
        $server['HTTP_HOST'] = 'subdomain.example.com';
        $server['HTTPS'] = 'Off';
        $server['SERVER_PORT'] = '80';
        $server['PHP_AUTH_USER'] = 'user';
        $server['PHP_AUTH_PW'] = '123456';
        $query = array(
            'test' => 'value',
            'test2' => 'value2'
        );
        $this->assertEquals(
            new Uri('http://user:123456@subdomain.example.com/?test=value&test2=value2'),
            ServerRequestUtils::extractUri($server, $query)
        );
    }

    public function testExtractUriWithoutPortAndQuerystring()
    {
        $server = $this->server;
        $server['HTTP_HOST'] = 'subdomain.example.com';
        $server['HTTPS'] = 'Off';
        $server['SERVER_PORT'] = '80';
        $server['PHP_AUTH_USER'] = 'user';
        $server['PHP_AUTH_PW'] = '123456';
        $query = array();
        $this->assertEquals(
            new Uri('http://user:123456@subdomain.example.com/'),
            ServerRequestUtils::extractUri($server, $query)
        );
    }

    public function testExtractUriOnlySchemeAndHost()
    {
        $server = $this->server;
        $server['HTTP_HOST'] = 'subdomain.example.com';
        $server['HTTPS'] = 'Off';
        $server['SERVER_PORT'] = '80';
        $server['PHP_AUTH_USER'] = '';
        $server['PHP_AUTH_PW'] = '';
        $query = array();
        $this->assertEquals(
            new Uri('http://subdomain.example.com/'),
            ServerRequestUtils::extractUri($server, $query)
        );
    }

    public function testExtractUriOnlySchemeAndHostWithHttps()
    {
        $server = $this->server;
        $server['HTTP_HOST'] = 'subdomain.example.com';
        $server['HTTPS'] = 'On';
        $server['SERVER_PORT'] = '80';
        $server['PHP_AUTH_USER'] = '';
        $server['PHP_AUTH_PW'] = '';
        $query = array();
        $this->assertEquals(
            new Uri('http://subdomain.example.com/'),
            ServerRequestUtils::extractUri($server, $query)
        );
    }

}