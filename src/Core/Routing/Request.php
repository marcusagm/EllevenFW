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

use InvalidArgumentException;
use UnexpectedValueException;
use Psr\Http\Message\UploadedFileInterface;
use Psr\Http\Message\ServerRequestInterface;
use EllevenFw\Library\Network\ServerRequest;
use EllevenFw\Library\Network\ServerRequestUtils;
use EllevenFw\Library\Network\UploadedFile;
use EllevenFw\Library\Network\UploadedFileFactory;

/**
 * Description of Request
 *
 * @author Marcus Maia <contato@marcusmaia.com>
 */
class Request
{

    /**
     *
     * @var array
     */
    private $environment = array();

    /**
     *
     * @var ServerRequest
     */
    private $serverRequest;

    /**
     *
     * @var array
     */
    private $accepts = array();

    /**
     *
     * @var string
     */
    private $languages = null;

    /**
     *
     * @var array
     */
    private $encoding = array();

    /**
     *
     * @var string
     */
    private $url = null;

    /**
     *
     */
    public function __construct(ServerRequestInterface $ServerRequest = null)
    {
        if ($ServerRequest === null) {
            $ServerRequest = ServerRequestUtils::createFromGlobals();
        }
        $this->serverRequest = $ServerRequest;

        $env = ServerRequestUtils::filterInputGlobals(INPUT_ENV);
        $this->setEnvironment($env);

        $serverParams = $ServerRequest->getServerParams();
        if (isset($serverParams['HTTP_ACCEPT'])) {
            $this->setAccept($serverParams['HTTP_ACCEPT']);
        }

        if (isset($serverParams['HTTP_ACCEPT_LANGUAGE'])) {
            $this->setAcceptLanguages($serverParams['HTTP_ACCEPT_LANGUAGE']);
        }
    }

    /**
     *
     * @return array
     */
    public function getQuery()
    {
        return $this->serverRequest->getQueryParams();
    }

    /**
     *
     * @param string $name
     * @return mixed|false
     */
    public function getQueryVar($name)
    {
        $params = $this->getQuery();
        if (isset($params[$name])) {
            return $params[$name];
        }
        return false;
    }

    /**
     *
     * @return array
     */
    public function getPost()
    {
        return $this->serverRequest->getParsedBody();
    }

    /**
     *
     * @param string $name
     * @return mixed|false
     */
    public function getPostVar($name)
    {
        $params = $this->getPost();
        if (isset($params[$name])) {
            return $params[$name];
        }
        return false;
    }

    /**
     *
     * @return array
     */
    public function getFiles()
    {
        return $this->serverRequest->getUploadedFiles();
    }

    /**
     *
     * @param string $name
     * @return mixed|false
     */
    public function getFilesVar($name)
    {
        $files = $this->getFiles();
        if (isset($files[$name])) {
            return $files[$name];
        }
        return false;
    }

    /**
     *
     * @return ServerRequest
     */
    public function getServerRequest()
    {
        return $this->serverRequest;
    }

    /**
     *
     * @return array
     */
    public function getServer()
    {
        return $this->serverRequest->getServerParams();
    }

    /**
     *
     * @param string $name
     * @return mixed|false
     */
    public function getServerVar($name)
    {
        $server = $this->getServer();
        if (array_key_exists($name, $server)) {
            return $server[$name];
        }
        return false;
    }

    /**
     *
     * @param array $environment
     * @return void
     */
    public function setEnvironment($environment)
    {
        $this->environment = $environment;
    }

    /**
     *
     * @return array
     */
    public function getEnvironment()
    {
        return $this->environment;
    }

    /**
     *
     * @param string $name
     * @return mixed|false
     */
    public function getEnvironmentVar($name)
    {
        if (isset($this->environment[$name])) {
            return $this->environment[$name];
        }
        return false;
    }

    /**
     *
     * @return array
     */
    public function getCookies()
    {
        return $this->serverRequest->getCookieParams();
    }

    /**
     *
     * @param string $name
     * @return boolean|mixed
     */
    public function getCookiesVar($name)
    {
        $cookies = $this->getCookies();
        if (array_key_exists($name, $cookies)) {
            return $cookies[$name];
        }
        return false;
    }

    /**
     *
     * @return string
     */
    public function getClientIp()
    {
        $ipAddress = null;
        $server = $this->getServer();
        if (ServerRequestUtils::isFromTrustedProxy($server) && $this->getServerVar('HTTP_X_FORWARDED_FOR')) {
            $ipAddress = preg_replace(
                '/(?:,.*)/',
                '',
                $this->getServerVar('HTTP_X_FORWARDED_FOR')
            );
        } else {
            if ($this->getServerVar('HTTP_CLIENT_IP')) {
                $ipAddress = $this->getServerVar('HTTP_CLIENT_IP');
            } else {
                $ipAddress = $this->getServerVar('REMOTE_ADDR');
            }
        }
        if ($this->getServerVar('HTTP_CLIENTADDRESS')) {
            $tmpIpAddr = $this->getServerVar('HTTP_CLIENTADDRESS');
            if (!empty($tmpIpAddr)) {
                $ipAddress = preg_replace('/(?:,.*)/', '', $tmpIpAddr);
            }
        }
        if (filter_var($ipAddress, FILTER_VALIDATE_IP) === false) {
            throw new \DomainException(sprintf('O IP "%s" é inválido.', $ipAddress));
        }
        return trim($ipAddress);
    }

    /**
     *
     * @return string
     */
    public function getMethod()
    {
        return $this->serverRequest->getMethod();
    }

    /**
     *
     * @param string $method
     * @return boolean
     */
    public function isMethod($method)
    {
        return $this->getMethod() === strtoupper($method);
    }

    /**
     *
     * @return boolean
     */
    public function isMethodSafe()
    {
        return in_array($this->getMethod(), array('GET', 'HEAD', 'OPTIONS', 'TRACE'));
    }

    /**
     *
     * @return string
     */
    public function getHost()
    {
        return $this->serverRequest->getUri()->getHost();
    }

    /**
     *
     * @return string
     */
    public function getPort()
    {
        $port = $this->serverRequest->getUri()->getPort();
        if ($port === null) {
            if ($this->getScheme() === 'https') {
                return '443';
            } else {
                return '80';
            }
        }
        return $port;
    }

    /**
     *
     * @return string
     */
    public function getScheme()
    {
        return $this->serverRequest->getUri()->getScheme();
    }

    /**
     *
     * @param int $tldLength Número de segmentos que o "Top Level Domain"
     * possui. Por exemplo: `exemplo.com` possui um segmento, `exemplo.com.br`
     * possui dois segmentos.
     * @return string
     */
    public function getDomain($tldLength = 1)
    {
        $segments = explode('.', $this->getHost());
        $domain = array_slice($segments, -1 * ($tldLength + 1));
        return implode('.', $domain);
    }

    /**
     *
     * @param int $tldLength Número de segmentos que o "Top Level Domain"
     * possui. Por exemplo: `exemplo.com` possui um segmento, `exemplo.com.br`
     * possui dois segmentos.
     * @return array
     */
    public function getSubdomain($tldLength = 1)
    {
        $segments = explode('.', $this->getHost());
        return array_slice($segments, 0, -1 * ($tldLength + 1));
    }

    /**
     *
     * @param string $acceptString
     * @return void
     */
    public function setAccept($acceptString)
    {
        $accept = $this->parseAcceptWithQualifier($acceptString);
        $cleanList = array();
        foreach ($accept as $types) {
            $cleanList = array_merge($cleanList, $types);
        }

        $this->accepts = $cleanList;
    }

    /**
     *
     * @return array
     */
    public function getAccepts()
    {
        return $this->accepts;
    }

    /**
     *
     * @param string $type
     * @return boolean
     */
    public function checkAcceptType($type)
    {
        return in_array($type, $this->accepts);
    }

    /**
     *
     * @param string $acceptLanguagesString
     */
    public function setAcceptLanguages($acceptLanguagesString)
    {
        $raw = $this->parseAcceptWithQualifier($acceptLanguagesString);
        $accept = [];
        foreach ($raw as $languages) {
            foreach ($languages as &$lang) {
                if (strpos($lang, '_')) {
                    $lang = str_replace('_', '-', $lang);
                }
                $lang = strtolower($lang);
            }
            $accept = array_merge($accept, $languages);
        }
        $this->languages = $accept;
    }

    /**
     *
     * @return array
     */
    public function getAcceptLanguages()
    {
        return $this->languages;
    }

    /**
     *
     * @param string $language
     * @return boolean
     */
    public function checkAcceptLanguage($language)
    {
        return in_array(strtolower($language), $this->languages);
    }

    /**
     *
     * @return string
     */
    public function getProtocol()
    {
        return $this->serverRequest->getProtocolVersion();
    }

    /**
     *
     * @return string
     */
    public function getEncoding()
    {
        if (empty($this->encoding)) {
            $encoding = $this->getServerVar('HTTP_ACCEPT_ENCODING');
            $raw = $this->parseAcceptWithQualifier($encoding);
            $cleanList = array();
            foreach ($raw as $encoding) {
                $cleanList = array_merge($cleanList, $encoding);
            }
            $this->encoding = $cleanList;
        }
        return $this->encoding;
    }

    /**
     *
     * @return boolean|integer
     */
    public function getContentLength()
    {
        if ($this->getServerVar('HTTP_CONTENT_LENGTH') !== false) {
            return (int) $this->getServerVar('HTTP_CONTENT_LENGTH');
        }
        if ($this->getServerVar('CONTENT_LENGTH') !== false) {
            return (int) $this->getServerVar('CONTENT_LENGTH');
        }
        return false;
    }

    /**
     *
     * @return boolean|string
     */
    public function getContentType()
    {
        if ($this->getServerVar('HTTP_CONTENT_TYPE') !== false) {
            return $this->getServerVar('HTTP_CONTENT_TYPE');
        }
        if ($this->getServerVar('CONTENT_TYPE') !== false) {
            return $this->getServerVar('CONTENT_TYPE');
        }
        return false;
    }

    /**
     *
     * @return boolean|string
     */
    public function getUser()
    {
        return $this->serverRequest->getUri()->getUser();
    }

    /**
     *
     * @return boolean|string
     */
    public function getPassword()
    {
        return $this->serverRequest->getUri()->getPassword();
    }

    /**
     *
     * @return string
     */
    public function getRequestTime()
    {
        return $this->getServerVar('REQUEST_TIME');
    }

    /**
     *
     * @return boolean
     */
    public function isXmlHttpRequest()
    {
        $requestedWith = $this->getServerVar('HTTP_X_REQUESTED_WITH');
        if ($requestedWith !== false) {
            $requestedWith = strtolower($requestedWith);
        }
        return $requestedWith == 'xmlhttprequest';
    }

    /**
     *
     * @return string
     */
    public function getPath()
    {
        return $this->serverRequest->getUri()->getPath();
    }

    /**
     *
     * @return string
     */
    public function getQueryString()
    {
        return $this->serverRequest->getUri()->getQuery();
    }

    /**
     *
     * @return string
     */
    public function getFullUrl()
    {
        if ($this->url) {
            return $this->url;
        }

        $url = '';
        $url.= $this->getScheme() . '://';

        if ($this->getUser() !== '' && $this->getPassword() !== '') {
            $url.= $this->getUser() . ':';
            $url.= $this->getPassword() . '@';
        }
        $url.= $this->getHost();

        if ($this->getPort() !== null && $this->getPort() !== '80' && $this->getPort() !== '443') {
            $url.= ':' . $this->getPort();
        }

        $url.= $this->getPath();

        $queryString = $this->getQueryString();
        $url.= $queryString !== '' ? '?' . $queryString : '';

        $this->url = $url;

        return $this->url;
    }

    /**
     *
     * @param string $callback
     * @return mixed
     */
    public function getInput($callback = null)
    {
        $input = $this->readInput();
        $args = func_get_args();
        if (!empty($args)) {
            $callback = array_shift($args);
            array_unshift($args, $input);
            return call_user_func_array($callback, $args);
        }
        return $input;
    }

    /**
     *
     * @return Psr\Http\Message\StreamInterface
     */
    public function readInput()
    {
        return $this->serverRequest->getBody();
    }

    /**
     *
     * @param string $acceptString
     * @return array
     */
    public function parseAcceptWithQualifier($acceptString)
    {
        $accept = array();

        $parts = explode(',', $acceptString);
        $parser = array_filter($parts);

        foreach ($parser as $value) {
            $prefValue = '1.0';
            $value = trim($value);
            $semiPos = strpos($value, ';');
            if ($semiPos !== false) {
                $params = explode(';', $value);
                $value = trim($params[0]);
                foreach ($params as $param) {
                    $qPos = strpos($param, 'q=');
                    if ($qPos !== false) {
                        $prefValue = substr($param, $qPos + 2);
                    }
                }
            }
            if (!isset($accept[$prefValue])) {
                $accept[$prefValue] = [];
            }
            if ($prefValue) {
                $accept[$prefValue][] = $value;
            }
        }
        krsort($accept);
        return $accept;
    }

}
