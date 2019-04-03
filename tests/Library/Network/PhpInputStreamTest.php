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

use PHPUnit\Framework\TestCase;
use EllevenFw\Library\Network\PhpInputStream;

class PhpInputStreamTest extends TestCase
{

    /**
     * @var string
     */
    protected $file;

    /**
     * @var PhpInputStream
     */
    protected $stream;

    public function setUp(): void
    {
        $this->file = __DIR__ . '/assets/php-input-stream.txt';
        $this->stream = new PhpInputStream($this->file);
    }

    public function getFileContents()
    {
        return file_get_contents($this->file);
    }

    public function assertStreamContents($test, $message = '')
    {
        $content = $this->getFileContents();
        $this->assertSame($content, $test, $message);
    }

    public function testStreamIsNeverWritable()
    {
        $this->assertFalse($this->stream->isWritable());
    }

    public function testCanReadStreamIteratively()
    {
        $body = '';
        while ( !$this->stream->eof() ) {
            $body .= $this->stream->read(128);
        }
        $this->assertStreamContents($body);
    }

    public function testGetContentsReturnsRemainingContentsOfStream()
    {
        $start = $this->stream->read(128);
        $remainder = $this->stream->getContents();
        $contents = $this->getFileContents();
        $this->assertSame(substr($contents, 128), $remainder);
    }

    public function testGetContentsReturnCacheWhenReachedEof()
    {
        $this->stream->getContents();
        $this->assertStreamContents($this->stream->getContents());
        $stream = new PhpInputStream('data://,0');
        $stream->read(1);
        $stream->read(1);
        $this->assertSame('0', $stream->getContents(), 'Don\'t evaluate 0 as empty');
    }

    public function testCastingToStringReturnsFullContentsRegardlesOfPriorReads()
    {
        $start = $this->stream->read(128);
        $this->assertStreamContents($this->stream->__toString());
    }

    public function testMultipleCastsToStringReturnSameContentsEvenIfReadsOccur()
    {
        $first = (string) $this->stream;
        $read = $this->stream->read(128);
        $second = (string) $this->stream;
        $this->assertSame($first, $second);
    }

}
