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

use InvalidArgumentException;
use ReflectionProperty;
use RuntimeException;
use PHPUnit\Framework\TestCase;
use EllevenFw\Library\Network\Stream;

class StreamTest extends TestCase
{
    public static $isFReadError = false;
    public static $isFWriteError = false;
    public static $isFTellError = false;
    public static $isFSeekError = false;
    public static $isStreamGetContentError = false;
    public static $isStreamGetMetaDataError = false;

    public $tmpnam;

    /**
     * @var Stream
     */
    protected $stream;

    public function setUp(): void
    {
        $this->tmpnam = null;
        $this->stream = new Stream('php://memory', 'wb+');
    }

    public function tearDown(): void
    {
        if ($this->tmpnam && file_exists($this->tmpnam)) {
            unlink($this->tmpnam);
        }
    }

    public function testCanInstantiateWithStreamIdentifier()
    {
        $this->assertInstanceOf(Stream::class, $this->stream);
    }

    public function testCanInstantiteWithStreamResource()
    {
        $resource = fopen('php://memory', 'wb+');
        $stream   = new Stream($resource);
        $this->assertInstanceOf(Stream::class, $stream);
    }

    public function testIsReadableReturnsFalseIfStreamIsNotReadable()
    {
        $this->tmpnam = tempnam(sys_get_temp_dir(), 'diac');
        $stream = new Stream($this->tmpnam, 'w');
        $this->assertFalse($stream->isReadable());
    }

    public function testIsWritableReturnsFalseIfStreamIsNotWritable()
    {
        $stream = new Stream('php://memory', 'r');
        $this->assertFalse($stream->isWritable());
    }

    public function testToStringRetrievesFullContentsOfStream()
    {
        $message = 'foo bar';
        $this->stream->write($message);
        $this->assertSame($message, (string) $this->stream);
    }

    public function testDetachReturnsResource()
    {
        $resource = fopen('php://memory', 'wb+');
        $stream   = new Stream($resource);
        $this->assertSame($resource, $stream->detach());
    }

    public function testPassingInvalidStreamResourceToConstructorRaisesException()
    {
        $this->expectException(\InvalidArgumentException::class);
        // $this->expectExceptionMessage('Unsupported HTTP method');
        $stream = new Stream(['  THIS WILL NOT WORK  ']);
    }

    public function testStringSerializationReturnsEmptyStringWhenStreamIsNotReadable()
    {
        $this->tmpnam = tempnam(sys_get_temp_dir(), 'diac');
        file_put_contents($this->tmpnam, 'FOO BAR');
        $stream = new Stream($this->tmpnam, 'w');

        $this->assertSame('', $stream->__toString());
    }

    public function testCloseClosesResource()
    {
        $this->tmpnam = tempnam(sys_get_temp_dir(), 'diac');
        $resource = fopen($this->tmpnam, 'wb+');
        $stream = new Stream($resource);
        $stream->close();
        $this->assertFalse(is_resource($resource));
    }

    public function testCloseUnsetsResource()
    {
        $this->tmpnam = tempnam(sys_get_temp_dir(), 'diac');
        $resource = fopen($this->tmpnam, 'wb+');
        $stream = new Stream($resource);
        $stream->close();

        $this->assertNull($stream->detach());
    }

    public function testCloseDoesNothingAfterDetach()
    {
        $this->tmpnam = tempnam(sys_get_temp_dir(), 'diac');
        $resource = fopen($this->tmpnam, 'wb+');
        $stream = new Stream($resource);
        $detached = $stream->detach();

        $stream->close();
        $this->assertTrue(is_resource($detached));
        $this->assertSame($resource, $detached);
    }

    /**
     * @group 42
     */
    public function testSizeReportsNullWhenNoResourcePresent()
    {
        $this->stream->detach();
        $this->assertNull($this->stream->getSize());
    }

    public function testTellReportsCurrentPositionInResource()
    {
        $this->tmpnam = tempnam(sys_get_temp_dir(), 'diac');
        file_put_contents($this->tmpnam, 'FOO BAR');
        $resource = fopen($this->tmpnam, 'wb+');
        $stream = new Stream($resource);

        fseek($resource, 2);

        $this->assertSame(2, $stream->tell());
    }

    public function testTellRaisesExceptionIfResourceIsDetached()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('No resource');

        $this->tmpnam = tempnam(sys_get_temp_dir(), 'diac');
        file_put_contents($this->tmpnam, 'FOO BAR');
        $resource = fopen($this->tmpnam, 'wb+');
        $stream = new Stream($resource);

        fseek($resource, 2);
        $stream->detach();

        $stream->tell();
    }

    public function testEofReportsFalseWhenNotAtEndOfStream()
    {
        $this->tmpnam = tempnam(sys_get_temp_dir(), 'diac');
        file_put_contents($this->tmpnam, 'FOO BAR');
        $resource = fopen($this->tmpnam, 'wb+');
        $stream = new Stream($resource);

        fseek($resource, 2);
        $this->assertFalse($stream->eof());
    }

    public function testEofReportsTrueWhenAtEndOfStream()
    {
        $this->tmpnam = tempnam(sys_get_temp_dir(), 'diac');
        file_put_contents($this->tmpnam, 'FOO BAR');
        $resource = fopen($this->tmpnam, 'wb+');
        $stream = new Stream($resource);

        while (! feof($resource)) {
            fread($resource, 4096);
        }
        $this->assertTrue($stream->eof());
    }

    public function testEofReportsTrueWhenStreamIsDetached()
    {
        $this->tmpnam = tempnam(sys_get_temp_dir(), 'diac');
        file_put_contents($this->tmpnam, 'FOO BAR');
        $resource = fopen($this->tmpnam, 'wb+');
        $stream = new Stream($resource);

        fseek($resource, 2);
        $stream->detach();
        $this->assertTrue($stream->eof());
    }

    public function testIsSeekableReturnsTrueForReadableStreams()
    {
        $this->tmpnam = tempnam(sys_get_temp_dir(), 'diac');
        file_put_contents($this->tmpnam, 'FOO BAR');
        $resource = fopen($this->tmpnam, 'wb+');
        $stream = new Stream($resource);
        $this->assertTrue($stream->isSeekable());
    }

    public function testIsSeekableReturnsFalseForDetachedStreams()
    {
        $this->tmpnam = tempnam(sys_get_temp_dir(), 'diac');
        file_put_contents($this->tmpnam, 'FOO BAR');
        $resource = fopen($this->tmpnam, 'wb+');
        $stream = new Stream($resource);
        $stream->detach();
        $this->assertFalse($stream->isSeekable());
    }

    public function testSeekAdvancesToGivenOffsetOfStream()
    {
        $this->tmpnam = tempnam(sys_get_temp_dir(), 'diac');
        file_put_contents($this->tmpnam, 'FOO BAR');
        $resource = fopen($this->tmpnam, 'wb+');
        $stream = new Stream($resource);
        $this->assertTrue($stream->seek(2));
        $this->assertSame(2, $stream->tell());
    }

    public function testRewindResetsToStartOfStream()
    {
        $this->tmpnam = tempnam(sys_get_temp_dir(), 'diac');
        file_put_contents($this->tmpnam, 'FOO BAR');
        $resource = fopen($this->tmpnam, 'wb+');
        $stream = new Stream($resource);
        $this->assertTrue($stream->seek(2));
        $stream->rewind();
        $this->assertSame(0, $stream->tell());
    }

    public function testSeekRaisesExceptionWhenStreamIsDetached()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('No resource');

        $this->tmpnam = tempnam(sys_get_temp_dir(), 'diac');
        file_put_contents($this->tmpnam, 'FOO BAR');
        $resource = fopen($this->tmpnam, 'wb+');
        $stream = new Stream($resource);
        $stream->detach();

        $stream->seek(2);
    }

    public function testIsWritableReturnsFalseWhenStreamIsDetached()
    {
        $this->tmpnam = tempnam(sys_get_temp_dir(), 'diac');
        file_put_contents($this->tmpnam, 'FOO BAR');
        $resource = fopen($this->tmpnam, 'wb+');
        $stream = new Stream($resource);
        $stream->detach();
        $this->assertFalse($stream->isWritable());
    }

    public function testIsWritableReturnsTrueForWritableMemoryStream()
    {
        $stream = new Stream("php://temp", "r+b");
        $this->assertTrue($stream->isWritable());
    }

    public function provideDataForIsWritable()
    {
        return [
            ['a',   true,  true],
            ['a+',  true,  true],
            ['a+b', true,  true],
            ['ab',  true,  true],
            ['c',   true,  true],
            ['c+',  true,  true],
            ['c+b', true,  true],
            ['cb',  true,  true],
            ['r',   true,  false],
            ['r+',  true,  true],
            ['r+b', true,  true],
            ['rb',  true,  false],
            ['rw',  true,  true],
            ['w',   true,  true],
            ['w+',  true,  true],
            ['w+b', true,  true],
            ['wb',  true,  true],
            ['x',   false, true],
            ['x+',  false, true],
            ['x+b', false, true],
            ['xb',  false, true],
        ];
    }

    private function findNonExistentTempName()
    {
        while (true) {
            $tmpnam = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'diac' . uniqid();
            if (! file_exists(sys_get_temp_dir() . $tmpnam)) {
                break;
            }
        }
        return $tmpnam;
    }

    /**
     * @dataProvider provideDataForIsWritable
     */
    public function testIsWritableReturnsCorrectFlagForMode($mode, $fileShouldExist, $flag)
    {
        if ($fileShouldExist) {
            $this->tmpnam = tempnam(sys_get_temp_dir(), 'diac');
            file_put_contents($this->tmpnam, 'FOO BAR');
        } else {
            // "x" modes REQUIRE that file doesn't exist, so we need to find random file name
            $this->tmpnam = $this->findNonExistentTempName();
        }
        $resource = fopen($this->tmpnam, $mode);
        $stream = new Stream($resource);
        $this->assertSame($flag, $stream->isWritable());
    }

    public function provideDataForIsReadable()
    {
        return [
            ['a',   true,  false],
            ['a+',  true,  true],
            ['a+b', true,  true],
            ['ab',  true,  false],
            ['c',   true,  false],
            ['c+',  true,  true],
            ['c+b', true,  true],
            ['cb',  true,  false],
            ['r',   true,  true],
            ['r+',  true,  true],
            ['r+b', true,  true],
            ['rb',  true,  true],
            ['rw',  true,  true],
            ['w',   true,  false],
            ['w+',  true,  true],
            ['w+b', true,  true],
            ['wb',  true,  false],
            ['x',   false, false],
            ['x+',  false, true],
            ['x+b', false, true],
            ['xb',  false, false],
        ];
    }

    /**
     * @dataProvider provideDataForIsReadable
     */
    public function testIsReadableReturnsCorrectFlagForMode($mode, $fileShouldExist, $flag)
    {
        if ($fileShouldExist) {
            $this->tmpnam = tempnam(sys_get_temp_dir(), 'diac');
            file_put_contents($this->tmpnam, 'FOO BAR');
        } else {
            // "x" modes REQUIRE that file doesn't exist, so we need to find random file name
            $this->tmpnam = $this->findNonExistentTempName();
        }
        $resource = fopen($this->tmpnam, $mode);
        $stream = new Stream($resource);
        $this->assertSame($flag, $stream->isReadable());
    }

    public function testWriteRaisesExceptionWhenStreamIsDetached()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('No resource');

        $this->tmpnam = tempnam(sys_get_temp_dir(), 'diac');
        file_put_contents($this->tmpnam, 'FOO BAR');
        $resource = fopen($this->tmpnam, 'wb+');
        $stream = new Stream($resource);
        $stream->detach();

        $stream->write('bar');
    }

    public function testWriteRaisesExceptionWhenStreamIsNotWritable()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Stream is not writable');

        $stream = new Stream('php://memory', 'r');

        $stream->write('bar');
    }

    public function testIsReadableReturnsFalseWhenStreamIsDetached()
    {
        $this->tmpnam = tempnam(sys_get_temp_dir(), 'diac');
        file_put_contents($this->tmpnam, 'FOO BAR');
        $resource = fopen($this->tmpnam, 'wb+');
        $stream = new Stream($resource);
        $stream->detach();

        $this->assertFalse($stream->isReadable());
    }

    public function testReadRaisesExceptionWhenStreamIsDetached()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('No resource');

        $this->tmpnam = tempnam(sys_get_temp_dir(), 'diac');
        file_put_contents($this->tmpnam, 'FOO BAR');
        $resource = fopen($this->tmpnam, 'r');
        $stream = new Stream($resource);
        $stream->detach();

        $stream->read(4096);
    }

    public function testReadReturnsEmptyStringWhenAtEndOfFile()
    {
        $this->tmpnam = tempnam(sys_get_temp_dir(), 'diac');
        file_put_contents($this->tmpnam, 'FOO BAR');
        $resource = fopen($this->tmpnam, 'r');
        $stream = new Stream($resource);
        while (! feof($resource)) {
            fread($resource, 4096);
        }
        $this->assertSame('', $stream->read(4096));
    }

    public function testGetContentsRisesExceptionIfStreamIsNotReadable()
    {
        $this->expectException(\RuntimeException::class);
        // $this->expectExceptionMessage('No resource');

        $this->tmpnam = tempnam(sys_get_temp_dir(), 'diac');
        file_put_contents($this->tmpnam, 'FOO BAR');
        $resource = fopen($this->tmpnam, 'w');
        $stream = new Stream($resource);

        $stream->getContents();
    }

    public function invalidResources()
    {
        $this->tmpnam = tempnam(sys_get_temp_dir(), 'diac');
        return [
            'null' => [ null ],
            'false' => [ false ],
            'true' => [ true ],
            'int' => [ 1 ],
            'float' => [ 1.1 ],
            'string-non-resource' => [ 'foo-bar-baz' ],
            'array' => [ [ fopen($this->tmpnam, 'r+') ] ],
            'object' => [ (object) [ 'resource' => fopen($this->tmpnam, 'r+') ] ],
        ];
    }

    /**
     * @dataProvider invalidResources
     */
    public function testAttachWithNonStringNonResourceRaisesException($resource)
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid stream');

        $this->stream->attach($resource);
    }

    public function testAttachWithResourceAttachesResource()
    {
        $this->tmpnam = tempnam(sys_get_temp_dir(), 'diac');
        $resource = fopen($this->tmpnam, 'r+');
        $this->stream->attach($resource);

        $r = new ReflectionProperty($this->stream, 'resource');
        $r->setAccessible(true);
        $test = $r->getValue($this->stream);
        $this->assertSame($resource, $test);
    }

    public function testAttachWithStringRepresentingResourceCreatesAndAttachesResource()
    {
        $this->tmpnam = tempnam(sys_get_temp_dir(), 'diac');
        $this->stream->attach($this->tmpnam);

        $resource = fopen($this->tmpnam, 'r+');
        fwrite($resource, 'FooBar');

        $this->stream->rewind();
        $test = (string) $this->stream;
        $this->assertSame('FooBar', $test);
    }

    public function testGetContentsShouldGetFullStreamContents()
    {
        $this->tmpnam = tempnam(sys_get_temp_dir(), 'diac');
        $resource = fopen($this->tmpnam, 'r+');
        $this->stream->attach($resource);

        fwrite($resource, 'FooBar');

        // rewind, because current pointer is at end of stream!
        $this->stream->rewind();
        $test = $this->stream->getContents();
        $this->assertSame('FooBar', $test);
    }

    public function testGetContentsShouldReturnStreamContentsFromCurrentPointer()
    {
        $this->tmpnam = tempnam(sys_get_temp_dir(), 'diac');
        $resource = fopen($this->tmpnam, 'r+');
        $this->stream->attach($resource);

        fwrite($resource, 'FooBar');

        // seek to position 3
        $this->stream->seek(3);
        $test = $this->stream->getContents();
        $this->assertSame('Bar', $test);
    }

    public function testGetMetadataReturnsAllMetadataWhenNoKeyPresent()
    {
        $this->tmpnam = tempnam(sys_get_temp_dir(), 'diac');
        $resource = fopen($this->tmpnam, 'r+');
        $this->stream->attach($resource);

        $expected = stream_get_meta_data($resource);
        $test     = $this->stream->getMetadata();

        $this->assertSame($expected, $test);
    }

    public function testGetMetadataReturnsDataForSpecifiedKey()
    {
        $this->tmpnam = tempnam(sys_get_temp_dir(), 'diac');
        $resource = fopen($this->tmpnam, 'r+');
        $this->stream->attach($resource);

        $metadata = stream_get_meta_data($resource);
        $expected = $metadata['uri'];

        $test     = $this->stream->getMetadata('uri');

        $this->assertSame($expected, $test);
    }

    public function testGetMetadataReturnsNullIfNoDataExistsForKey()
    {
        $this->tmpnam = tempnam(sys_get_temp_dir(), 'diac');
        $resource = fopen($this->tmpnam, 'r+');
        $this->stream->attach($resource);

        $this->assertNull($this->stream->getMetadata('TOTALLY_MADE_UP'));
    }

    /**
     * @group 42
     */
    public function testGetSizeReturnsStreamSize()
    {
        $resource = fopen(__FILE__, 'r');
        $expected = fstat($resource);
        $stream = new Stream($resource);
        $this->assertSame($expected['size'], $stream->getSize());
    }

    public function testRaisesExceptionOnConstructionForNonStreamResources()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('stream');

        $resource = $this->getResourceFor67();
        if (false === $resource) {
            $this->markTestSkipped('No acceptable resource available to test ' . __METHOD__);
        }

        new Stream($resource);
    }

    public function testRaisesExceptionOnAttachForNonStreamResources()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('stream');

        $resource = $this->getResourceFor67();
        if (false === $resource) {
            $this->markTestSkipped('No acceptable resource available to test ' . __METHOD__);
        }

        $stream = new Stream(__FILE__);

        $stream->attach($resource);
    }

    public function getResourceFor67()
    {
        if (function_exists('curl_init')) {
            return curl_init();
        }

        if (function_exists('shmop_open')) {
            return shmop_open(ftok(__FILE__, 't'), 'c', 0644, 100);
        }

        if (function_exists('gmp_init')) {
            return gmp_init(1);
        }

        if (function_exists('imagecreate')) {
            return imagecreate(200, 200);
        }

        return false;
    }

    public function testCanReadContentFromNotSeekableResource()
    {
        $this->tmpnam = tempnam(sys_get_temp_dir(), 'diac');
        file_put_contents($this->tmpnam, 'FOO BAR');
        $resource = fopen($this->tmpnam, 'r');
        $stream = $this
            ->getMockBuilder(Stream::class)
            ->setConstructorArgs([$resource])
            ->setMethods(['isSeekable'])
            ->getMock();

        $stream->expects($this->any())->method('isSeekable')
            ->will($this->returnValue(false));

        $this->assertSame('FOO BAR', $stream->__toString());
    }

    public function testStreamClosesHandleOnDestruct()
    {
        $handle = fopen('php://temp', 'r');
        $stream = new Stream($handle);
        unset($stream);
        $this->assertFalse(is_resource($handle));
    }

    public function testStreamReadingFreadError()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Error reading stream');

        self::$isFReadError = true;
        $r = fopen('php://temp', 'r');
        $stream = new Stream($r);
        try {
            $stream->read(1);
            $stream->close();
        } catch (\Exception $e) {
            self::$isFReadError = false;
            $stream->close();
            throw $e;
        }

        self::$isFReadError = false;
        $stream->close();
    }

    public function testStreamReadingFwriteError()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Error writing to stream');

        self::$isFWriteError = true;
        $r = fopen('php://temp', 'w');
        $stream = new Stream($r);
        try {
            $stream->write('asd');
            $stream->close();
        } catch (\Exception $e) {
            self::$isFWriteError = false;
            $stream->close();
            throw $e;
        }

        self::$isFWriteError = false;
        $stream->close();
    }

    public function testStreamIsNotReadable()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Stream is not readable');

        $this->tmpnam = tempnam(sys_get_temp_dir(), 'diac');
        $resource = fopen($this->tmpnam, 'a');
        $stream = new Stream($resource);
        try {
            $stream->read(1);
            $stream->close();
        } catch (\Exception $e) {
            $stream->close();
            throw $e;
        }
        $stream->close();
    }

    public function testStreamFailOnGetContents()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Error reading from stream');

        self::$isStreamGetContentError = true;
        $r = fopen('php://temp', 'r');
        $stream = new Stream($r);
        try {
            $stream->getContents();
            $stream->close();
        } catch (\Exception $e) {
            self::$isStreamGetContentError = false;
            $stream->close();
            throw $e;
        }

        self::$isStreamGetContentError = false;
        $stream->close();
    }

    public function testStreamFailOnTell()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Error occurred during tell operation');

        self::$isFTellError = true;
        $r = fopen('php://temp', 'r');
        $stream = new Stream($r);
        try {
            $stream->tell();
            $stream->close();
        } catch (\Exception $e) {
            self::$isFTellError = false;
            $stream->close();
            throw $e;
        }

        self::$isFTellError = false;
        $stream->close();
    }

    public function testStreamFailOnIsSeekeble()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Stream is not seekable');

        self::$isStreamGetMetaDataError = true;
        $r = fopen('php://temp', 'r');
        $stream = new Stream($r);
        try {
            $stream->seek(3);
            $stream->close();
        } catch (\Exception $e) {
            self::$isStreamGetMetaDataError = false;
            $stream->close();
            throw $e;
        }

        self::$isStreamGetMetaDataError = false;
        $stream->close();
    }

    public function testStreamFailOnSeek()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Error seeking within stream');

        self::$isFSeekError = true;
        $r = fopen('php://temp', 'r');
        $stream = new Stream($r);
        try {
            $stream->seek(3);
            $stream->close();
        } catch (\Exception $e) {
            self::$isFSeekError = false;
            $stream->close();
            throw $e;
        }

        self::$isFSeekError = false;
        $stream->close();
    }

    public function testStreamFailOnConvertToString()
    {
        $this->expectException(\RuntimeException::class);
        // $this->expectExceptionMessage('Error seeking within stream');
        
        self::$isStreamGetContentError = true;
        $r = fopen('php://temp', 'r');
        $stream = new Stream($r);
        try {
            (string) $stream;
            $stream->getContents();
        } catch (\Exception $e) {
            self::$isStreamGetContentError = false;
            $stream->close();
            throw $e;
        }

        self::$isStreamGetContentError = false;
        $stream->close();
    }
}

namespace EllevenFw\Library\Network;

use EllevenFw\Test\Library\Network\StreamTest;

function fread($handle, $length)
{
    return StreamTest::$isFReadError ? false : \fread($handle, $length);
}

namespace EllevenFw\Library\Network;

use EllevenFw\Test\Library\Network\StreamTest;

function fwrite($resource, $string)
{
    return StreamTest::$isFWriteError ? false : \fwrite($resource, $string);
}

namespace EllevenFw\Library\Network;

use EllevenFw\Test\Library\Network\StreamTest;

function stream_get_contents($resource)
{
    return StreamTest::$isStreamGetContentError ? false : \stream_get_contents($resource);
}

namespace EllevenFw\Library\Network;

use EllevenFw\Test\Library\Network\StreamTest;

function ftell($resource)
{
    return StreamTest::$isFTellError ? false : \ftell($resource);
}

namespace EllevenFw\Library\Network;

use EllevenFw\Test\Library\Network\StreamTest;

function stream_get_meta_data($resource)
{
    return StreamTest::$isStreamGetMetaDataError ? false : \stream_get_meta_data($resource);
}

namespace EllevenFw\Library\Network;

use EllevenFw\Test\Library\Network\StreamTest;

function fseek($resource, $offset, $whence = SEEK_SET)
{
    return StreamTest::$isFSeekError ? -1 : \fseek($resource, $offset, $whence);
}
