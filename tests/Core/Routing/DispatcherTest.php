<?php

namespace EllevenFw\Test\Core\Routing;

use EllevenFw\Core\Routing\Dispatcher;
use EllevenFw\Library\Network\Uri;
use EllevenFw\Library\Network\ServerRequest;
use EllevenFw\Library\Network\ServerRequestUtils;
use EllevenFw\Core\Routing\Request;
use PHPUnit\Framework\TestCase;

/**
 * Generated by PHPUnit_SkeletonGenerator on 2015-09-24 at 22:47:45.
 */
class DispatcherTest extends TestCase
{
    /**
     * @var array
     */
    protected $server;

    /**
     * @var array
     */
    protected $get = array();

    /**
     * @var array
     */
    protected $post = array();

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void
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
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown(): void
    {

    }

    public function getServerRequest(array $server, array $get)
    {
        return new ServerRequest(
            $server,
            array(),
            ServerRequestUtils::extractUri($server, $get),
            null,
            'php://input',
            ServerRequestUtils::filterHeaders($server),
            array(),
            $get
        );
    }

    public function testSetBaseUrlWithString()
    {
        $url = 'http://www.test.com/index/index';
        $Dispatcher = new Dispatcher();
        $Dispatcher->setBaseUrl($url);
        $Uri = new Uri($url);
        $baseUrl = $Dispatcher->getBaseUrl();

        $this->assertEquals($Uri, $baseUrl[0]);
    }

    public function testSetBaseUrlWithArray()
    {
        $urls = array(
            'http://www.test.com/index/index',
            'http://localhost/index/index',
        );
        $Dispatcher = new Dispatcher();
        $Dispatcher->setBaseUrl($urls);
        $baseUrl = $Dispatcher->getBaseUrl();

        $Uri = new Uri($urls[0]);
        $this->assertEquals($Uri, $baseUrl[0]);

        $Uri = new Uri($urls[1]);
        $this->assertEquals($Uri, $baseUrl[1]);
    }

    public function testIfParseRewriteUriReturnAInstanceOfCoreRequestWhenIsARewritedRequest()
    {
        $server = $this->server;
        $get = $this->get;
        $get['efw-path'] = '';
        $Request = $this->getServerRequest($server, $get);

        $Dispatcher = new Dispatcher();
        $result = $Dispatcher->parseRewiteUri($Request);

        $this->assertInstanceOf('EllevenFw\Core\Routing\Request', $result);
    }

    public function testIfParseRewriteUriReturnAInstanceOfCoreRequestWhenIsNotARewritedRequest()
    {
        $server = $this->server;
        $get = $this->get;
        $Request = $this->getServerRequest($server, $get);

        $Dispatcher = new Dispatcher();
        $result = $Dispatcher->parseRewiteUri($Request);

        $this->assertInstanceOf('EllevenFw\Core\Routing\Request', $result);
    }

    public function testIfParseRewriteUriReturnARequestWithTheExpectedQueryParamWithoutRewriteRules()
    {
        $server = $this->server;
        $server['PHP_SELF'] = '/index';
        $get = $this->get;
        $Request = $this->getServerRequest($server, $get);

        $Dispatcher = new Dispatcher();
        $result = $Dispatcher->parseRewiteUri($Request);

        $this->assertNotFalse( $result->getQueryVar('efw-path'));
    }

    public function testIfParseRewriteUriReturnARequestWithTheExpectedQueryParamWithRewriteRules()
    {
        $server = $this->server;
        $server['PHP_SELF'] = '/index.php';
        $get = $this->get;
        $get['efw-path'] = '';
        $Request = $this->getServerRequest($server, $get);

        $Dispatcher = new Dispatcher();
        $result = $Dispatcher->parseRewiteUri($Request);

        $this->assertNotFalse( $result->getQueryVar('efw-path'));
    }

    public function testIfParseRewriteUriReturnRequestWithQueryPathEmpty()
    {
        $server = $this->server;
        $server['PHP_SELF'] = '/index.php';
        $get = $this->get;
        $get['efw-path'] = '';
        $Request = $this->getServerRequest($server, $get);

        $Dispatcher = new Dispatcher();
        $result = $Dispatcher->parseRewiteUri($Request);

        $this->assertEquals( '', $result->getQueryVar('efw-path'));
    }

    public function testIfParseRewriteUriReturnRequestWithQueryPathToIndexController()
    {
        $server = $this->server;
        $server['PATH_INFO'] = '/index.php/index';
        $get = $this->get;
        $Request = $this->getServerRequest($server, $get);

        $Dispatcher = new Dispatcher();
        $result = $Dispatcher->parseRewiteUri($Request);

        $this->assertEquals( '/index', $result->getQueryVar('efw-path'));
    }

    public function testIfParseRewriteUriReturnRequestWithQueryPathToIndexAction()
    {
        $server = $this->server;
        $server['PATH_INFO'] = '/index/index';
        $get = $this->get;
        $Request = $this->getServerRequest($server, $get);

        $Dispatcher = new Dispatcher();
        $result = $Dispatcher->parseRewiteUri($Request);
        $expected = new Request($Request);

        $this->assertEquals( '/index/index', $result->getQueryVar('efw-path'));
    }

    public function testIfParseRewriteUriReturnRequestWithQueryPathToIndexWhenHasAPathToFile()
    {
        $server = $this->server;
        $server['PATH_INFO'] = '/path/index.php/index';
        $get = $this->get;
        $Request = $this->getServerRequest($server, $get);

        $Dispatcher = new Dispatcher();
        $result = $Dispatcher->parseRewiteUri($Request);
        $expected = new Request($Request);

        $this->assertEquals( '/index', $result->getQueryVar('efw-path'));
    }

    public function testIfParseRewriteUriReturnRequestWithQueryPathToIndexWhenHasABaseUrl()
    {
        $server = $this->server;
        $server['PATH_INFO'] = '/path/app-test/index';
        $get = $this->get;
        $Request = $this->getServerRequest($server, $get);

        $Dispatcher = new Dispatcher();
        $Dispatcher->setBaseUrl('http://www.test.com/path/app-test/');
        $result = $Dispatcher->parseRewiteUri($Request);

        $this->assertEquals( '/index', $result->getQueryVar('efw-path'));
    }
}
