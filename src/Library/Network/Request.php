<?php
/**
 * Elleven Framework
 * Copyright 2015 Marcus Maia <contato@marcusmaia.com>.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright   Copyright (c) Marcus Maia <contato@marcusmaia.com>
 * @link        http://elleven.marcusmaia.com Elleven Kit
 * @since       1.0.0
 * @license     http://www.opensource.org/licenses/mit-license.php MIT License
 */

namespace EllevenFw\Library\Network;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;

/**
 * HTTP Request encapsulation
 *
 * Requests are considered immutable; all methods that might change state are
 * implemented such that they retain the internal state of the current
 * message and return a new instance that contains the changed state.
 */
class Request implements RequestInterface
{
    use RequestTrait;

    /**
     * @param null|string|UriInterface $uri URI for the request, if any.
     * @param null|string $method HTTP method for the request, if any.
     * @param string|resource|StreamInterface $body Message body, if any.
     * @param array $headers Headers for the message, if any.
     * @throws \InvalidArgumentException for any invalid value.
     */
    public function __construct($uri = null, $method = null, $body = 'php://temp', array $headers = [])
    {
        $this->initialize($uri, $method, $body, $headers);
    }

    /**
     * {@inheritdoc}
     */
    public function getHeaders()
    {
        $headers = $this->headers;
        if (! $this->hasHeader('host')
            && $this->uri->getHost()
        ) {
            $headers['Host'] = [$this->getHostFromUri()];
        }
        return $headers;
    }

    /**
     * {@inheritdoc}
     */
    public function getHeader($header)
    {
        if (! $this->hasHeader($header)) {
            if (strtolower($header) === 'host'
                && $this->uri->getHost()
            ) {
                return [$this->getHostFromUri()];
            }
            return [];
        }
        $header = $this->headerNames[strtolower($header)];
        return $this->headers[$header];
    }
}
