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

use EllevenFw\Library\Network\ServerRequest;

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
     * @var EllevenFw\Library\Basic\Session
     */
    private $session;

    /**
     *
     * @var Psr\Http\Message\ServerRequestInterface
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
    private $trustedProxies = array();

    /**
     *
     */
    public function __construct()
    {
        $server = $this->filterInputGlobals(INPUT_SERVER);
        $uploadedFiles = static::normalizeFiles($_FILES);
        $env = $this->filterInputGlobals(INPUT_ENV);

        $this->serverRequest = new ServerRequest(
            $server,
            $uploadedFiles,
            $this->getFullUrl($server),
            $this->getMethod($server),
            'php://input',
            $server,
            $this->filterInputGlobals(INPUT_COOKIE),
            $this->filterInputGlobals(INPUT_GET),
            $this->filterInputGlobals(INPUT_POST),
            $this->getProtocol($server)
        );

        $this->setEnvironment($env);

        if (isset($server['HTTP_ACCEPT'])) {
            $this->setAccept($server['HTTP_ACCEPT']);
        }

        if (isset($server['HTTP_ACCEPT_LANGUAGE'])) {
            $this->setAcceptLanguages($server['HTTP_ACCEPT_LANGUAGE']);
        }
    }

    private function filterInputGlobals($type)
    {
        $values = filter_input_array($type);
        if ( is_array($values) === false ) {
            return array();
        }
        return $values;
    }

    private function getProtocolFromGlobals($server = null)
    {
        if ($server == null) {
            $server = filter_input_array(INPUT_SERVER);
        }
        if (! isset($server['SERVER_PROTOCOL'])) {
            return '1.1';
        }
        if (! preg_match('#^(HTTP/)?(?P<version>[1-9]\d*(?:\.\d)?)$#', $server['SERVER_PROTOCOL'], $matches)) {
            throw new UnexpectedValueException(sprintf(
                'Unrecognized protocol version (%s)',
                $server['SERVER_PROTOCOL']
            ));
        }
        return $matches['version'];
    }

    /**
     * Normalize uploaded files
     *
     * Transforms each value into an UploadedFileInterface instance, and ensures
     * that nested arrays are normalized.
     *
     * @param array $files
     * @return array
     * @throws InvalidArgumentException for unrecognized values
     */
    public static function normalizeFiles(array $files)
    {
        $normalized = [];
        foreach ($files as $key => $value) {
            if ($value instanceof UploadedFileInterface) {
                $normalized[$key] = $value;
                continue;
            }
            if (is_array($value) && isset($value['tmp_name'])) {
                $normalized[$key] = self::createUploadedFileFromSpec($value);
                continue;
            }
            if (is_array($value)) {
                $normalized[$key] = self::normalizeFiles($value);
                continue;
            }
            throw new InvalidArgumentException('Invalid value in files specification');
        }
        return $normalized;
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
     * @param array $proxies
     * @return void
     */
    public function setTrustedProxies(array $proxies)
    {
        $this->trustedProxies = $proxies;
    }

    /**
     *
     * @return array
     */
    public function getTrustedProxies()
    {
        return $this->trustedProxies;
    }

    /**
     *
     * @param string $address
     * @return boolean
     */
    public function isFromTrustedProxy($server = null)
    {
        if ($server == null) {
            $server = $this->getServer();
        }
        if (empty($this->getTrustedProxies())) {
            return false;
        }
        $address = $server['REMOTE_ADDR'];
        return in_array($address, $this->trustedProxies);
    }

    /**
     *
     * @return string
     */
    public function getClientIp($server = null)
    {
        if ($server == null) {
            $server = $this->getServer();
        }
        $ipAddress = null;
        if ($this->isFromTrustedProxy() && $server['HTTP_X_FORWARDED_FOR']) {
            $ipAddress = preg_replace(
                '/(?:,.*)/',
                '',
                $server('HTTP_X_FORWARDED_FOR')
            );
        } else {
            if ($server('HTTP_CLIENT_IP')) {
                $ipAddress = $server('HTTP_CLIENT_IP');
            } else {
                $ipAddress = $server('REMOTE_ADDR');
            }
        }
        if ($server('HTTP_CLIENTADDRESS')) {
            $tmpIpAddr = $server('HTTP_CLIENTADDRESS');
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
    public function getMethod($server = null)
    {
        if ($server == null) {
            $server = $this->getServer();
        }
        if (isset($server('REQUEST_METHOD')) === false) {
            return 'GET';
        }

        $method = strtoupper($server('REQUEST_METHOD'));

        if ($method === 'POST') {
            if ($server('X-HTTP-METHOD-OVERRIDE') !== false) {
                $method = strtoupper($server('X-HTTP-METHOD-OVERRIDE'));
            }
        }
        return $method;
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
     * @todo Corrigir Exceção
     * @todo Adicionar verificação de hosts confiáveis para evitar ataques de
     * injeção de cabeçalhos (Host Header Injection Attacks)
     * @return string
     */
    public function getHost($server = null)
    {
        if ($server == null) {
            $server = $this->getServer();
        }
        $host = '';
        if ($this->isFromTrustedProxy() && $server('HTTP_X_FORWARDED_HOST')) {
            $host = $server('HTTP_X_FORWARDED_HOST');
        } elseif ($server('HTTP_HOST')) {
            $host = $server('HTTP_HOST');
        } elseif ($server('SERVER_NAME')) {
            $host = $server('SERVER_NAME');
        } else {
            $host = $server('SERVER_ADDR');
        }
        return $host;
    }

    /**
     *
     * @return string
     */
    public function getPort($server = null)
    {
        if ($server == null) {
            $server = $this->getServer();
        }
        if ($this->isFromTrustedProxy() && $server('HTTP_X_FORWARDED_PORT')) {
            return $server('HTTP_X_FORWARDED_PORT');
        }
        return $server('SERVER_PORT');
    }

    /**
     *
     * @return string
     */
    public function getScheme($server = null)
    {
        if ($server == null) {
            $server = $this->getServer();
        }
        if ($this->isFromTrustedProxy() && $server('HTTP_X_FORWARDED_PROTO') !== false) {
            return $server('HTTP_X_FORWARDED_PROTO');
        }
        $https = $server('HTTPS');
        return empty($https) === false && $https === 'on' ? 'https' : 'http';
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
    public function getProtocol($server = null)
    {
        if ($server == null) {
            $server = $this->getServer();
        }
        if (! isset($server['SERVER_PROTOCOL'])) {
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
     * @return string
     */
    public function getEncoding($server = null)
    {
        if ($server == null) {
            $server = $this->getServer();
        }
        if (empty($this->encoding)) {
            $encoding = $server('HTTP_ACCEPT_ENCODING');
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
    public function getContentLength($server = null)
    {
        if ($server == null) {
            $server = $this->getServer();
        }
        if ($server('HTTP_CONTENT_LENGTH') !== false) {
            return (int) $server('HTTP_CONTENT_LENGTH');
        }
        if ($server('CONTENT_LENGTH') !== false) {
            return (int) $server('CONTENT_LENGTH');
        }
        return false;
    }

    /**
     *
     * @return boolean|string
     */
    public function getContentType($server = null)
    {
        if ($server == null) {
            $server = $this->getServer();
        }
        if ($server('HTTP_CONTENT_TYPE') !== false) {
            return $server('HTTP_CONTENT_TYPE');
        }
        if ($server('CONTENT_TYPE') !== false) {
            return $server('CONTENT_TYPE');
        }
        return false;
    }

    /**
     *
     * @return boolean|string
     */
    public function getUser($server = null)
    {
        if ($server == null) {
            $server = $this->getServer();
        }
        if ($server('PHP_AUTH_USER')) {
            return $server('PHP_AUTH_USER');
        }
        return false;
    }

    /**
     *
     * @return boolean|string
     */
    public function getPassword($server = null)
    {
        if ($server == null) {
            $server = $this->getServer();
        }
        if ($server('PHP_AUTH_PW')){
            return $server('PHP_AUTH_PW');
        }
        return false;
    }

    public function getRequestTime($server = null)
    {
        if ($server == null) {
            $server = $this->getServer();
        }
        return $server('REQUEST_TIME');
    }

    public function isXmlHttpRequest($server = null)
    {
        if ($server == null) {
            $server = $this->getServer();
        }
        $requestedWith = $server('HTTP_X_REQUESTED_WITH');
        if ($requestedWith !== false) {
            $requestedWith = strtolower($requestedWith);
        }
        return $requestedWith == 'xmlhttprequest';
    }

    /**
     *
     * @return void
     */
    public function resetUrlPath()
    {
        $this->pathUrl = null;
    }

    /**
     *
     * @return string
     */
    public function getUrlPath($server = null)
    {
        if ($server == null) {
            $server = $this->getServer();
        }
        if ($this->pathUrl !== null) {
            return $this->pathUrl;
        }

        if ($server('PATH_INFO') !== false) {
            $uri = $server('PATH_INFO');
        } elseif ($server('REQUEST_URI') !== false) {
            $requestUri = $server('REQUEST_URI');
            if (strpos($requestUri, '://') === false) {
                $uri = $requestUri;
            } else {
                $qPosition = strpos($requestUri, '?');
                if ($qPosition !== false && strpos($requestUri, '://') > $qPosition) {
                    $uri = $requestUri;
                } else {
                    $scheme = $this->getScheme();
                    $host = $this->getHost();
                    $uri = substr(
                        $requestUri,
                        strlen($scheme.'://'.$host)
                    );
                }
            }
        } elseif ($server('PHP_SELF') !== false && $server('SCRIPT_NAME') !== false) {
            $phpSelf = $server('PHP_SELF');
            $scriptName = $server('SCRIPT_NAME');
            $uri = str_replace($scriptName, '', $phpSelf);
        } elseif ($server('HTTP_X_REWRITE_URL') !== false) {
            $uri = $server('HTTP_X_REWRITE_URL');
        } else {
            $var = $server('argv');
            $uri = $var[0];
        }

        if (strpos($uri, '?') !== false) {
            list($uri) = explode('?', $uri, 2);
        }

        if (empty($uri) || $uri === '/' || $uri === '//' || $uri === '/index.php') {
            $uri = '/';
        }

        $this->pathUrl = $uri;

        return $uri;
    }

    public function getQueryString($query = null)
    {
        if ($this->queryString !== null) {
            return $this->queryString;
        }

        if ($query == null) {
            $query = $this->getQuery();
        }
        if (empty($query)) {
            return '';
        }
        $this->queryString = http_build_query($query, null, '&', PHP_QUERY_RFC3986);
        return $this->queryString;
    }

    public function getFullUrl($server = null, $query = null)
    {
        if ($server == null) {
            $server = $this->getServer();
        }
        $url = '';
        $url.= $this->getScheme($server) . '://';

        if ($this->getUser($server) !== false && $this->getPassword($server) !== false) {
            $url.= $this->getUser($server) . ':';
            $url.= $this->getPassword($server) . '@';
        }
        $url.= $this->getHost($server);

        if ($this->getPort($server) !== false && $this->getPort($server) !== '80' && $this->getPort($server) !== '443') {
            $url.= ':' . $this->getPort($server);
        }

        $url.= $this->getUrlPath($server);

        $queryString = $this->getQueryString($server);
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
