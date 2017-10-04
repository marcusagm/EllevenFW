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

namespace EllevenFw\Core\Routing;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
use EllevenFw\Library\Network\Uri;
use EllevenFw\Core\Routing\MiddlewareInterface;
use EllevenFw\Core\Routing\Request;
use EllevenFw\Core\Routing\Mapper;

/**
 * Description of Dispatcher
 *
 * @author Marcus Maia <contato@marcusmaia.com>
 */
class Dispatcher implements MiddlewareInterface
{
    /**
     *
     * @var  Uri[]
     */
    private $baseUrl = array();

    /**
     *
     * @param string|array $url
     */
    public function setBaseUrl($url)
    {
        $urlList = array();
        if (is_string($url) === true) {
            $urlList[] = $url;
        } elseif (is_array($url) === true) {
            $urlList = $url;
        }

        foreach ( $urlList as $uri ) {
            $this->baseUrl[] = new Uri($uri);
        }
    }

    /**
     *
     * @return array
     */
    public function getBaseUrl()
    {
        return $this->baseUrl;
    }

    /**
     *
     * @param ServerRequestInterface $Request
     * @param object $Handler
     * @return ResponseInterface
     */
    public function process(
        ServerRequestInterface $Request,
        RequestHandlerInterface $Handler
    ) {
        $this->parseRewiteUri($Request->getUri());
        $Handler->handle($Request);
    }

    /**
     *
     * @param ServerRequestInterface $Request
     * @return ServerRequestInterface
     */
    public function parseRewiteUri(ServerRequestInterface $Request)
    {
        $queryParams = $Request->getQueryParams();
        if (isset($queryParams['efw-path']) !== false) {
            return new Request($Request);
        }

        $replace = array(
            '/.*index\.php\//',
            '/.*index\.php/'
        );
        foreach ( $this->baseUrl as $Uri ) {
            $replace[] = '/' . preg_quote($Uri->getPath(), '/') . '/';
        }
        $queryParams['efw-path'] = preg_replace($replace, '/', $Request->getUri()->getPath());
        $NewRequest = $Request->withQueryParams($queryParams);

        return new $NewRequest;
    }
}
