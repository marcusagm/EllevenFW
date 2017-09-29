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

namespace EllevenFw\Library\Network;

/**
 * Caching version of php://input
 */
class PhpInputStream extends Stream
{

    /**
     * @var string
     */
    private $cache = '';

    /**
     * @var bool
     */
    private $reachedEof = false;

    /**
     * @param  string|resource $stream
     */
    public function __construct($stream = 'php://input')
    {
        parent::__construct($stream, 'r');
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        if ( $this->reachedEof ) {
            return $this->cache;
        }
        $this->getContents();
        return $this->cache;
    }

    /**
     * {@inheritdoc}
     */
    public function isWritable()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function read($length)
    {
        $content = parent::read($length);
        if ( !$this->reachedEof ) {
            $this->cache .= $content;
        }
        if ( $this->eof() ) {
            $this->reachedEof = true;
        }
        return $content;
    }

    /**
     * {@inheritdoc}
     */
    public function getContents($maxLength = -1)
    {
        if ( $this->reachedEof ) {
            return $this->cache;
        }
        $contents = stream_get_contents($this->resource, $maxLength);
        $this->cache .= $contents;
        if ( $maxLength === -1 || $this->eof() ) {
            $this->reachedEof = true;
        }
        return $contents;
    }

}
