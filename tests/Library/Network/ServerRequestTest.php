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

namespace EllevenFw\Test\Library\Network;

use ReflectionProperty;
use InvalidArgumentException;
use EllevenFw\Library\Network\Uri;
use EllevenFw\Library\Network\ServerRequest;
use EllevenFw\Library\Network\UploadedFile;

class ServerRequestTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var ServerRequest
     */
    protected $request;

    public function setUp()
    {
        $this->request = new ServerRequest();
    }

    public function testServerParamsAreEmptyByDefault()
    {
        $this->assertEmpty($this->request->getServerParams());
    }

    public function testQueryParamsAreEmptyByDefault()
    {
        $this->assertEmpty($this->request->getQueryParams());
    }

    public function testMethodMutatorReturnsCloneWithChangedMethod()
    {
        $request = $this->request->withMethod('GET');
        $this->assertNotSame($this->request, $request);
        $this->assertSame('GET', $request->getMethod());
    }

    public function testQueryParamsMutatorReturnsCloneWithChanges()
    {
        $value = ['foo' => 'bar'];
        $request = $this->request->withQueryParams($value);
        $this->assertNotSame($this->request, $request);
        $this->assertSame($value, $request->getQueryParams());
    }

    public function testCookiesAreEmptyByDefault()
    {
        $this->assertEmpty($this->request->getCookieParams());
    }

    public function testCookiesMutatorReturnsCloneWithChanges()
    {
        $value = ['foo' => 'bar'];
        $request = $this->request->withCookieParams($value);
        $this->assertNotSame($this->request, $request);
        $this->assertSame($value, $request->getCookieParams());
    }

    public function testUploadedFilesAreEmptyByDefault()
    {
        $this->assertEmpty($this->request->getUploadedFiles());
    }

    public function testParsedBodyIsEmptyByDefault()
    {
        $this->assertEmpty($this->request->getParsedBody());
    }

    public function testParsedBodyMutatorReturnsCloneWithChanges()
    {
        $value = ['foo' => 'bar'];
        $request = $this->request->withParsedBody($value);
        $this->assertNotSame($this->request, $request);
        $this->assertSame($value, $request->getParsedBody());
    }

    public function testAttributesAreEmptyByDefault()
    {
        $this->assertEmpty($this->request->getAttributes());
    }

    public function testSingleAttributesWhenEmptyByDefault()
    {
        $this->assertEmpty($this->request->getAttribute('does-not-exist'));
    }

    /**
     * @depends testAttributesAreEmptyByDefault
     */
    public function testAttributeMutatorReturnsCloneWithChanges()
    {
        $request = $this->request->withAttribute('foo', 'bar');
        $this->assertNotSame($this->request, $request);
        $this->assertSame('bar', $request->getAttribute('foo'));
        return $request;
    }

    /**
     * @depends testAttributeMutatorReturnsCloneWithChanges
     */
    public function testRemovingAttributeReturnsCloneWithoutAttribute($request)
    {
        $new = $request->withoutAttribute('foo');
        $this->assertNotSame($request, $new);
        $this->assertNull($new->getAttribute('foo', null));
    }

    public function provideMethods()
    {
        return [
            'post' => ['POST', 'POST'],
            'get' => ['GET', 'GET'],
            'null' => [null, 'GET'],
        ];
    }

    /**
     * @dataProvider provideMethods
     */
    public function testUsesProvidedConstructorArguments($parameterMethod, $methodReturned)
    {
        $server = [
            'foo' => 'bar',
            'baz' => 'bat',
        ];
        $server['server'] = true;
        $files = [
            'files' => new UploadedFile('php://temp', 0, 0),
        ];
        $uri = new Uri('http://example.com');
        $headers = [
            'host' => ['example.com'],
        ];
        $cookies = [
            'boo' => 'foo',
        ];
        $queryParams = [
            'bar' => 'bat',
        ];
        $parsedBody = 'bazbar';
        $protocol = '1.2';
        $request = new ServerRequest(
            $server, $files, $uri, $parameterMethod, 'php://memory', $headers, $cookies, $queryParams, $parsedBody, $protocol
        );
        $this->assertSame($server, $request->getServerParams());
        $this->assertSame($files, $request->getUploadedFiles());
        $this->assertSame($uri, $request->getUri());
        $this->assertSame($methodReturned, $request->getMethod());
        $this->assertSame($headers, $request->getHeaders());
        $this->assertSame($cookies, $request->getCookieParams());
        $this->assertSame($queryParams, $request->getQueryParams());
        $this->assertSame($parsedBody, $request->getParsedBody());
        $this->assertSame($protocol, $request->getProtocolVersion());
        $body = $request->getBody();
        $r = new ReflectionProperty($body, 'stream');
        $r->setAccessible(true);
        $stream = $r->getValue($body);
        $this->assertSame('php://memory', $stream);
    }

    /**
     * @group 46
     */
    public function testCookieParamsAreAnEmptyArrayAtInitialization()
    {
        $request = new ServerRequest();
        $this->assertInternalType('array', $request->getCookieParams());
        $this->assertCount(0, $request->getCookieParams());
    }

    /**
     * @group 46
     */
    public function testQueryParamsAreAnEmptyArrayAtInitialization()
    {
        $request = new ServerRequest();
        $this->assertInternalType('array', $request->getQueryParams());
        $this->assertCount(0, $request->getQueryParams());
    }

    /**
     * @group 46
     */
    public function testParsedBodyIsNullAtInitialization()
    {
        $request = new ServerRequest();
        $this->assertNull($request->getParsedBody());
    }

    public function testAllowsRemovingAttributeWithNullValue()
    {
        $request = new ServerRequest();
        $request = $request->withAttribute('boo', null);
        $request = $request->withoutAttribute('boo');
        $this->assertSame([], $request->getAttributes());
    }

    public function testAllowsRemovingNonExistentAttribute()
    {
        $request = new ServerRequest();
        $request = $request->withoutAttribute('boo');
        $this->assertSame([], $request->getAttributes());
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testTryToAddInvalidUploadedFiles()
    {
        $request = new ServerRequest();
        //$this->expectException(InvalidArgumentException::class);
        $request->withUploadedFiles([null]);
    }

    public function testNestedUploadedFiles()
    {
        $request = new ServerRequest();
        $uploadedFiles = [
            [
                new UploadedFile('php://temp', 0, 0),
                new UploadedFile('php://temp', 0, 0),
            ]
        ];
        $request = $request->withUploadedFiles($uploadedFiles);
        $this->assertSame($uploadedFiles, $request->getUploadedFiles());
    }

}