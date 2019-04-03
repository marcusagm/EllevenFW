<?php

namespace EllevenFw\Test\Library\Network;

use DOMDocument;
use DOMXPath;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use EllevenFw\Library\Network\Response;
use EllevenFw\Library\Network\Stream;

class ResponseTest extends TestCase
{
    /**
     * @var Response
    */
    protected $response;

    public function setUp(): void
    {
        $this->response = new Response();
    }

    public function testStatusCodeIs200ByDefault()
    {
        $this->assertSame(200, $this->response->getStatusCode());
    }

    public function testStatusCodeMutatorReturnsCloneWithChanges()
    {
        $response = $this->response->withStatus(400);
        $this->assertNotSame($this->response, $response);
        $this->assertSame(400, $response->getStatusCode());
    }

    public function testReasonPhraseDefaultsToStandards()
    {
        $response = $this->response->withStatus(422);
        $this->assertSame('Unprocessable Entity', $response->getReasonPhrase());
    }

    public function testCanSetCustomReasonPhrase()
    {
        $response = $this->response->withStatus(422, 'Foo Bar!');
        $this->assertSame('Foo Bar!', $response->getReasonPhrase());
    }

    public function testConstructorRaisesExceptionForInvalidStream()
    {
       $this->expectException(\InvalidArgumentException::class);
        new Response([ 'TOTALLY INVALID' ]);
    }

    public function testConstructorCanAcceptAllMessageParts()
    {
        $body = new Stream('php://memory');
        $status = 302;
        $headers = [
            'location' => [ 'http://example.com/' ],
        ];
        $response = new Response($body, $status, $headers);
        $this->assertSame($body, $response->getBody());
        $this->assertSame(302, $response->getStatusCode());
        $this->assertSame($headers, $response->getHeaders());
    }

    /**
     * @dataProvider validStatusCodes
     */
    public function testCreateWithValidStatusCodes($code)
    {
        $response = $this->response->withStatus($code);
        $this->assertSame($code, $response->getStatusCode());
    }

    public function validStatusCodes()
    {
        return [
            'minimum' => [100],
            'middle' => [300],
            'maximum' => [599],
        ];
    }

    /**
     * @dataProvider invalidStatusCodes
     */
    public function testConstructorRaisesExceptionForInvalidStatus($code)
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid status code');
        new Response('php://memory', $code);
    }

    /**
     * @dataProvider invalidStatusCodes
     */
    public function testCannotSetInvalidStatusCode($code)
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->response->withStatus($code);
    }

    public function invalidStatusCodes()
    {
        return [
            'true' => [ true ],
            'false' => [ false ],
            'array' => [ [ 200 ] ],
            'object' => [ (object) [ 'statusCode' => 200 ] ],
            'too-low' => [99],
            'float' => [400.5],
            'too-high' => [600],
            'null' => [null],
            'string' => ['foo'],
        ];
    }

    public function invalidResponseBody()
    {
        return [
            'true'       => [ true ],
            'false'      => [ false ],
            'int'        => [ 1 ],
            'float'      => [ 1.1 ],
            'array'      => [ ['BODY'] ],
            'stdClass'   => [ (object) [ 'body' => 'BODY'] ],
        ];
    }

    /**
     * @dataProvider invalidResponseBody
     */
    public function testConstructorRaisesExceptionForInvalidBody($body)
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('stream');
        new Response($body);
    }

    public function invalidHeaderTypes()
    {
        return [
            'indexed-array' => [[['INVALID']], 'header name'],
            'null' => [['x-invalid-null' => null]],
            'true' => [['x-invalid-true' => true]],
            'false' => [['x-invalid-false' => false]],
            'object' => [['x-invalid-object' => (object) ['INVALID']]],
        ];
    }

    /**
     * @dataProvider invalidHeaderTypes
     */
    public function testConstructorRaisesExceptionForInvalidHeaders($headers, $contains = 'header value type')
    {
        $this->expectException(\InvalidArgumentException::class);
//        $this->expectExceptionMessage($contains);
        new Response('php://memory', 200, $headers);
    }

    public function testInvalidStatusCodeInConstructor()
    {
        $this->expectException(\InvalidArgumentException::class);
        new Response('php://memory', null);
    }

    public function testReasonPhraseCanBeEmpty()
    {
        $response = $this->response->withStatus(555);
        $this->assertIsString($response->getReasonPhrase());
        $this->assertEmpty($response->getReasonPhrase());
    }

    public function headersWithInjectionVectors()
    {
        return [
            'name-with-cr'           => ["X-Foo\r-Bar", 'value'],
            'name-with-lf'           => ["X-Foo\n-Bar", 'value'],
            'name-with-crlf'         => ["X-Foo\r\n-Bar", 'value'],
            'name-with-2crlf'        => ["X-Foo\r\n\r\n-Bar", 'value'],
            'value-with-cr'          => ['X-Foo-Bar', "value\rinjection"],
            'value-with-lf'          => ['X-Foo-Bar', "value\ninjection"],
            'value-with-crlf'        => ['X-Foo-Bar', "value\r\ninjection"],
            'value-with-2crlf'       => ['X-Foo-Bar', "value\r\n\r\ninjection"],
            'array-value-with-cr'    => ['X-Foo-Bar', ["value\rinjection"]],
            'array-value-with-lf'    => ['X-Foo-Bar', ["value\ninjection"]],
            'array-value-with-crlf'  => ['X-Foo-Bar', ["value\r\ninjection"]],
            'array-value-with-2crlf' => ['X-Foo-Bar', ["value\r\n\r\ninjection"]],
        ];
    }

    /**
     * @dataProvider headersWithInjectionVectors
     */
    public function testConstructorRaisesExceptionForHeadersWithCRLFVectors($name, $value)
    {
        $this->expectException(\InvalidArgumentException::class);
        new Response('php://memory', 200, [$name => $value]);
    }
}
