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

use \UnexpectedValueException;
use EllevenFw\Library\Network\ServerRequest;

/**
 * Description of ServerRequestFactory
 *
 * @author Marcus Maia <contato@marcusmaia.com>
 */
class ServerRequestUtils
{
    /**
     * Lista de endereços confiáveis como servidores de proxies.
     *
     * Caso a aplicação esteja usando servidores com balanceamento de carga
     * (load balances), algumas informações podem sofrer alterações devido ao
     * proxy. Para que seja possível obter de forma confiável as informações
     * corretas da requisição como o IP do cliente, porta, etc, é necessário
     * indicar os endereços confiáveis.
     *
     * Para todos os endereços desta lista, será utilizado informações de
     * cabeçalho personalizaveis para obter as informações reais. Um exemplo é
     * que para obter o IP do cliente normalmente se utiliza a variável
     * reservada $_SERVER['REMOTE_ADDR'], o valor desta variável de cabeçalho é
     * preenchida obrigatóriamente pelo servidor ao receber a requisição, porem
     * no caso de uso de servidores de balanceamento de carga, ocorre um
     * redirecionamento, e esta variável é preenchida com o IP do servidor
     * responsável pelo balanceamento, adicionando no cabeçalho a variável
     * $_SERVER[''HTTP_X_FORWARDED_FOR'] com o valor original. Esta variável
     * pode ser adicionada manualmente por qualquer um por não ser reservada,
     * o que pode facilitar ataques.
     *
     * @var array
     */
    private static $trustedProxies = array();

    public static function createFromGlobals()
    {
        $server = static::filterInputGlobals(INPUT_SERVER);
        return new ServerRequest(
            $server,
            UploadedFileFactory::normalizeFiles($_FILES),
            static::extractUri($server),
            static::extractMethod($server),
            'php://input',
            static::filterHeaders($server),
            static::filterInputGlobals(INPUT_COOKIE),
            static::filterInputGlobals(INPUT_GET),
            static::filterInputGlobals(INPUT_POST)
        );
    }

    public static function filterInputGlobals($type)
    {
        $values = filter_input_array($type);
        if ( is_array($values) === false ) {
            return array();
        }
        return $values;
    }

    /**
     *
     * @param array $server
     * @return array
     */
    public static function filterHeaders(array $server)
    {
        $headers = [];
        foreach ($server as $key => $value) {
            // Apache prefixes environment variables with REDIRECT_
            // if they are added by rewrite rules
            if (strpos($key, 'REDIRECT_') === 0) {
                $key = substr($key, 9);
                // We will not overwrite existing variables with the
                // prefixed versions, though
                if (array_key_exists($key, $server)) {
                    continue;
                }
            }
            if ($value && strpos($key, 'HTTP_') === 0) {
                $name = strtr(strtolower(substr($key, 5)), '_', '-');
                $headers[$name] = $value;
                continue;
            }
            if ($value && strpos($key, 'CONTENT_') === 0) {
                $name = 'content-' . strtolower(substr($key, 8));
                $headers[$name] = $value;
                continue;
            }
        }
        return $headers;
    }

    /**
     *
     * @param array $proxies
     * @return void
     */
    public static function setTrustedProxies(array $proxies)
    {
        static::$trustedProxies = $proxies;
    }

    /**
     *
     * @return array
     */
    public static function getTrustedProxies()
    {
        return static::$trustedProxies;
    }

    /**
     *
     * @param array $server
     * @return boolean
     */
    public static function isFromTrustedProxy(array $server)
    {
        if (empty(static::getTrustedProxies())) {
            return false;
        }
        $address = isset($server['REMOTE_ADDR']) ? $server['REMOTE_ADDR'] : '';
        return in_array($address, static::$trustedProxies);
    }

    /**
     *
     * @param array $server
     * @return string
     */
    public static function extractMethod(array $server)
    {
        if (isset($server['REQUEST_METHOD']) === false) {
            return 'GET';
        }

        $method = strtoupper($server['REQUEST_METHOD']);

        if ($method === 'POST') {
            if (isset($server['X-HTTP-METHOD-OVERRIDE']) !== false) {
                $method = strtoupper($server['X-HTTP-METHOD-OVERRIDE']);
            }
        }
        return $method;
    }

    /**
     *
     * @param array $server
     * @return string
     */
    public static function extractHost(array $server)
    {
        $host = '';
        if (static::isFromTrustedProxy($server) && isset($server['HTTP_X_FORWARDED_HOST'])) {
            $host = $server['HTTP_X_FORWARDED_HOST'];
        } elseif (isset($server['HTTP_HOST'])) {
            $host = $server['HTTP_HOST'];
        } elseif (isset($server['SERVER_NAME'])) {
            $host = $server['SERVER_NAME'];
        } elseif (isset($server['SERVER_ADDR'])) {
            $host = $server['SERVER_ADDR'];
        } else {
            $host = 'localhost';
        }
        return $host;
    }

    /**
     *
     * @return string
     */
    public static function extractPort(array $server)
    {
        if (static::isFromTrustedProxy($server) && isset($server['HTTP_X_FORWARDED_PORT'])) {
            return $server['HTTP_X_FORWARDED_PORT'];
        } elseif(isset($server['SERVER_PORT'])) {
            return $server['SERVER_PORT'];
        }
        return '';
    }

    /**
     *
     * @return string
     */
    public static function extractScheme(array $server)
    {
        if (static::isFromTrustedProxy($server) && isset($server['HTTP_X_FORWARDED_PROTO'])) {
            return $server['HTTP_X_FORWARDED_PROTO'];
        }
        $https = isset($server['HTTPS']) !== false ? $server['HTTPS'] : null;
        return empty($https) === false && $https == 'on' ? 'https' : 'http';
    }

    /**
     *
     * @param array $server
     * @return string
     * @throws UnexpectedValueException
     */
    public static function extractProtocol(array $server)
    {
        if (isset($server['SERVER_PROTOCOL']) === false) {
            return '1.1';
        }
        $matches = array();
        if (! preg_match('#^(HTTP/)?(?P<version>[1-9]\d*(?:\.\d)?)$#', $server['SERVER_PROTOCOL'], $matches)) {
            throw new UnexpectedValueException(sprintf(
                'Unrecognized protocol version (%s)',
                $server['SERVER_PROTOCOL']
            ));
        }
        return $matches['version'];
    }

    /**
     *
     * @param array $server
     * @return boolean|string
     */
    public static function extractUser(array $server)
    {
        if (isset($server['PHP_AUTH_USER'])) {
            return $server['PHP_AUTH_USER'];
        }
        return false;
    }

    /**
     *
     * @param array $server
     * @return boolean|string
     */
    public static function extractPassword(array $server)
    {
        if (isset($server['PHP_AUTH_PW'])){
            return $server['PHP_AUTH_PW'];
        }
        return false;
    }

    /**
     *
     * @param array $server
     * @return string
     */
    public static function extractPath(array $server)
    {
        if (isset($server['PATH_INFO']) !== false) {
            $uri = $server['PATH_INFO'];
        } elseif (isset($server['REQUEST_URI']) !== false) {
            $requestUri = $server['REQUEST_URI'];
            if (strpos($requestUri, '://') === false) {
                $uri = $requestUri;
            } else {
                $qPosition = strpos($requestUri, '?');
                if ($qPosition !== false && strpos($requestUri, '://') > $qPosition) {
                    $uri = $requestUri;
                } else {
                    $scheme = static::extractScheme($server);
                    $host = static::extractHost($server);
                    $uri = substr(
                        $requestUri,
                        strlen($scheme.'://'.$host)
                    );
                }
            }
        } elseif (isset($server['PHP_SELF']) && isset($server['SCRIPT_NAME'])) {
            $phpSelf = $server['PHP_SELF'];
            $scriptName = $server['SCRIPT_NAME'];
            $uri = str_replace($scriptName, '', $phpSelf);
        } elseif (isset($server['HTTP_X_REWRITE_URL'])) {
            $uri = $server['HTTP_X_REWRITE_URL'];
        } else {
            $var = $server['argv'];
            $uri = $var[0];
        }

        if (strpos($uri, '?') !== false) {
            list($uri) = explode('?', $uri, 2);
        }

        if (empty($uri) || $uri === '/' || $uri === '//' || $uri === '/index.php') {
            $uri = '/';
        }

        return $uri;
    }

    /**
     *
     * @param array|object $query
     * @return string
     */
    public static function makeQueryString($query)
    {
        return http_build_query($query, null, '&', PHP_QUERY_RFC3986);
    }

    /**
     *
     * @param array $server
     * @param array|object $query
     * @return \EllevenFw\Library\Network\Uri
     */
    public static function extractUri(array $server, $query = array())
    {
        $uri = '';
        $uri.= static::extractScheme($server) . '://';

        if (static::extractUser($server) !== false && static::extractPassword($server) !== false) {
            $uri.= static::extractUser($server) . ':';
            $uri.= static::extractPassword($server) . '@';
        }
        $uri.= static::extractHost($server);

        if (static::extractPort($server) !== false && static::extractPort($server) !== '80' && static::extractPort($server) !== '443') {
            $uri.= ':' . static::extractPort($server);
        }

        $uri.= static::extractPath($server);

        $queryString = static::makeQueryString($query);
        $uri.= $queryString !== '' ? '?' . $queryString : '';

        return new Uri($uri);
    }
}
