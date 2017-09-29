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

namespace EllevenFw\Library\Basic;

use InvalidArgumentException;
use Psr\Http\Message\UriInterface as UriInterface;

/**
 * Description of Request
 *
 * @author Marcus Maia <contato@marcusmaia.com>
 */
class Uri implements UriInterface
{

    /**
     * Absolute http and https URIs require a host per RFC 7230 Section 2.7
     * but in generic URIs the host can be empty. So for http(s) URIs
     * we apply this default host when no host is given yet to form a
     * valid URI.
     */
    const HTTP_DEFAULT_HOST = 'localhost';

    /**
     *
     * @var array
     */
    private static $defaultPorts = [
        'http'  => 80,
        'https' => 443,
        'ftp' => 21,
        'gopher' => 70,
        'nntp' => 119,
        'news' => 119,
        'telnet' => 23,
        'tn3270' => 23,
        'imap' => 143,
        'pop' => 110,
        'ldap' => 389,
    ];

    /**
     *
     * @var string
     */
    private static $charUnreserved = 'a-zA-Z0-9_\-\.~';

    /**
     *
     * @var string
     */
    private static $charSubDelims = '!\$&\'\(\)\*\+,;=';

    /**
     *
     * @var string Uri scheme.
     */
    private $scheme = '';

    /**
     *
     * @var string Uri user.
     */
    private $user = '';

    /**
     *
     * @var string Uri password.
     */
    private $password = '';

    /**
     *
     * @var string Uri host.
     */
    private $host = '';

    /**
     *
     * @var int|null Uri port.
     */
    private $port;

    /**
     *
     * @var string Uri path.
     */
    private $path = '';

    /**
     *
     * @var string Uri query string.
     */
    private $query = '';

    /**
     *
     * @var string Uri fragment.
     */
    private $fragment = '';

    /**
     *
     * @param string $uri
     * @throws InvalidArgumentException
     */
    public function __construct($uri = '')
    {
        if ($uri != '') {
            $parts = parse_url($uri);
            if ($parts === false) {
                throw new InvalidArgumentException("Unable to parse URI: $uri");
            }
            $this->applyParts($parts);
        }
    }

    /**
     *
     * @return string
     */
    public function __toString()
    {
        return self::composeComponents(
            $this->scheme,
            $this->getAuthority(),
            $this->path,
            $this->query,
            $this->fragment
        );
    }

    /**
     *
     * @return string
     */
    public function getAuthority()
    {
        $authority = $this->host;
        $userInfo = $this->getUserInfo();
        if ($userInfo !== '') {
            $authority = $userInfo . '@' . $authority;
        }
        if ($this->port !== null) {
            $authority .= ':' . $this->port;
        }
        return $authority;
    }

    public function getFragment()
    {
        return $this->fragment;
    }

    public function getHost()
    {
        return $this->host;
    }

    public function getPath()
    {
        return $this->path;
    }

    public function getPort()
    {
        return $this->port;
    }

    public function getQuery()
    {
        return $this->query;
    }

    public function getScheme()
    {
        return $this->scheme;
    }

    public function getUserInfo()
    {
        $userInfo = '';
        if ($this->user != '') {
            $userInfo.= $this->user;
        }
        if ($this->password != '') {
            $userInfo.= ':' . $this->password;
        }
        return $userInfo;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function withScheme($scheme)
    {
        $schemeFiltred = $this->filterScheme($scheme);
        if ($this->scheme === $schemeFiltred) {
            return $this;
        }
        $new = clone $this;
        $new->scheme = $schemeFiltred;
        $new->removeDefaultPort();
        $new->validateState();
        return $new;
    }

    public function withUserInfo($user, $password = null)
    {
        $info = $user;
        if ($password != '') {
            $info .= ':' . $password;
        }
        if ($this->getUserInfo() === $info) {
            return $this;
        }
        $new = clone $this;
        $new->user = $user;
        $new->password = $password;
        $new->validateState();
        return $new;
    }

    public function withHost($host)
    {
        $hostFiltred = $this->filterHost($host);
        if ($this->host === $hostFiltred) {
            return $this;
        }
        $new = clone $this;
        $new->host = $hostFiltred;
        $new->validateState();
        return $new;
    }

    public function withPort($port)
    {
        $portFiltred = $this->filterPort($port);
        if ($this->port === $portFiltred) {
            return $this;
        }
        $new = clone $this;
        $new->port = $portFiltred;
        $new->removeDefaultPort();
        $new->validateState();
        return $new;
    }

    public function withPath($path)
    {
        $pathFiltred = $this->filterPath($path);
        if ($this->path === $pathFiltred) {
            return $this;
        }
        $new = clone $this;
        $new->path = $pathFiltred;
        $new->validateState();
        return $new;
    }

    public function withQuery($query)
    {
        $queryFiltred = $this->filterQueryAndFragment($query);
        if ($this->query === $queryFiltred) {
            return $this;
        }
        $new = clone $this;
        $new->query = $queryFiltred;
        return $new;
    }

    public function withFragment($fragment)
    {
        $fragmentFiltred = $this->filterQueryAndFragment($fragment);
        if ($this->fragment === $fragmentFiltred) {
            return $this;
        }
        $new = clone $this;
        $new->fragment = $fragmentFiltred;
        return $new;
    }

    public static function isDefaultPort(UriInterface $uri)
    {
        return $uri->getPort() === null
            || (isset(self::$defaultPorts[$uri->getScheme()])
                && $uri->getPort() === self::$defaultPorts[$uri->getScheme()]);
    }

    /**
     * Composes a URI reference string from its various components.
     *
     * Usually this method does not need to be called manually but instead is
     * used indirectly via `Psr\Http\Message\UriInterface::__toString`.
     *
     * PSR-7 UriInterface treats an empty component the same as a missing
     * component as getQuery(), getFragment() etc. always return a string.
     * This explains the slight difference to RFC 3986 Section 5.3.
     *
     * Another adjustment is that the authority separator is added even when the
     * authority is missing/empty for the "file" scheme. This is because PHP
     * stream functions like `file_get_contents` only work with `file:///myfile`
     * but not with `file:/myfile` although they are equivalent according to
     * RFC 3986. But `file:///` is the more common syntax for the file scheme
     * anyway (Chrome for example redirects to that format).
     *
     * @param string $scheme
     * @param string $authority
     * @param string $path
     * @param string $query
     * @param string $fragment
     *
     * @return string
     *
     * @link https://tools.ietf.org/html/rfc3986#section-5.3
     */
    public static function composeComponents($scheme, $authority, $path, $query, $fragment)
    {
        $uri = '';
        // weak type checks to also accept null until we can add scalar type hints
        if ($scheme != '') {
            $uri .= $scheme . ':';
        }
        if ($authority != ''|| $scheme === 'file') {
            $uri .= '//' . $authority;
        }
        $uri .= $path;
        if ($query != '') {
            $uri .= '?' . $query;
        }
        if ($fragment != '') {
            $uri .= '#' . $fragment;
        }
        return $uri;
    }

    /**
     *
     * @param string $scheme
     * @return string
     * @throws InvalidArgumentException
     */
    private function filterScheme($scheme)
    {
        if (!is_string($scheme)) {
            throw new InvalidArgumentException('Scheme must be a string');
        }
        return strtolower($scheme);
    }

    /**
     *
     * @param string $host
     * @return string
     * @throws InvalidArgumentException
     */
    private function filterHost($host)
    {
        if (!is_string($host)) {
            throw new InvalidArgumentException('Host must be a string');
        }
        return strtolower($host);
    }

    /**
     *
     * @param string $port
     * @return string
     * @throws InvalidArgumentException
     */
    private function filterPort($port)
    {
        if ($port === null) {
            return null;
        }
        $portCast = (int) $port;
        if (1 > $portCast || 0xffff < $portCast) {
            throw new InvalidArgumentException(
                sprintf('Invalid port: %d. Must be between 1 and 65535', $portCast)
            );
        }
        return $portCast;
    }

    /**
     *
     * @param string $path
     * @return string
     * @throws InvalidArgumentException
     */
    private function filterPath($path)
    {
        if (!is_string($path)) {
            throw new InvalidArgumentException('Path must be a string');
        }
        $regex = '/(?:[^' . self::$charUnreserved . self::$charSubDelims;
        $regex.= '%:@\/]++|%(?![A-Fa-f0-9]{2}))/';
        return preg_replace_callback($regex, [$this, 'rawUrlEncodeMatchZero'], $path);
    }

    /**
     * Apply parse_url parts to a URI.
     *
     * @param array $parts
     * @return void
     */
    private function applyParts(array $parts)
    {
        $this->scheme = isset($parts['scheme']) ? $this->filterScheme($parts['scheme']) : '';
        $this->user = isset($parts['user']) ? $parts['user'] : '';
        $this->host = isset($parts['host']) ? $this->filterHost($parts['host']) : '';
        $this->port = isset($parts['port']) ? $this->filterPort($parts['port']) : null;
        $this->path = isset($parts['path']) ? $this->filterPath($parts['path']) : '';
        $this->query = isset($parts['query']) ? $this->filterQueryAndFragment($parts['query']) : '';
        $this->fragment = isset($parts['fragment']) ? $this->filterQueryAndFragment($parts['fragment']) : '';
        if (isset($parts['pass'])) {
            $this->password .= $parts['pass'];
        }
        $this->removeDefaultPort();
    }

    /**
     *
     * @return void
     */
    private function removeDefaultPort()
    {
        if ($this->port !== null && self::isDefaultPort($this)) {
            $this->port = null;
        }
    }

    /**
     * Filters the query string or fragment of a URI.
     *
     * @param string $str
     * @return string
     * @throws InvalidArgumentException If the query or fragment is invalid.
     */
    private function filterQueryAndFragment($string)
    {
        if (!is_string($string)) {
            throw new InvalidArgumentException('Query and fragment must be a string');
        }
        $regex = '/(?:[^' . self::$charUnreserved . self::$charSubDelims;
        $regex.= '%:@\/\?]++|%(?![A-Fa-f0-9]{2}))/';
        return preg_replace_callback($regex, [$this, 'rawUrlEncodeMatchZero'], $string);
    }

    /**
     *
     * @param array $match
     * @return string
     */
    private function rawUrlEncodeMatchZero(array $match)
    {
        return rawurlencode($match[0]);
    }

    /**
     *
     * @throws InvalidArgumentException
     */
    private function validateState()
    {
        if ($this->host === '' && ($this->scheme === 'http' || $this->scheme === 'https')) {
            $this->host = self::HTTP_DEFAULT_HOST;
        }
        if ($this->getAuthority() === '') {
            if (0 === strpos($this->path, '//')) {
                throw new InvalidArgumentException(
                    'The path of a URI without an authority must not start ' .
                    'with two slashes "//"');
            }
            if ($this->scheme === '' && false !== strpos(explode('/', $this->path, 2)[0], ':')) {
                throw new InvalidArgumentException(
                    'A relative URI must not have a path beginning with a ' .
                    'segment containing a colon'
                );
            }
        } elseif (isset($this->path[0]) && $this->path[0] !== '/') {
            // The path of a URI with an authority must start with a slash "/"
            // or be empty. Automagically fixing the URI by adding a leading
            // slash to the path is deprecated since version 1.4 and will throw
            // an exception instead.
            $this->path = '/'. $this->path;
        }
    }

}
