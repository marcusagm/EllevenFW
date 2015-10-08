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

use EllevenFw\Library\Basic\Cookie;
use EllevenFw\Library\Basic\Session;

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
    private $get = array();

    /**
     *
     * @var array
     */
    private $post = array();

    /**
     *
     * @var array
     */
    private $files = array();

    /**
     *
     * @var string
     */
    private $input = null;

    /**
     *
     * @var array
     */
    private $environment = array();

    /**
     *
     * @var array
     */
    private $server = array();

    /**
     *
     * @var EllevenFw\Library\Basic\Cookie
     */
    private $cookies;

    /**
     *
     * @var EllevenFw\Library\Basic\Session
     */
    private $session;

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
    private $pathUrl = null;

    /**
     *
     * @var string
     */
    private $queryString;

    /**
     *
     * @var string
     */
    private $baseUrl = null;

    /**
     *
     * @var string
     */
    private $basePath = null;

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
        $this->setQuery($_GET);
        $this->setPost($_POST);
        $this->setFiles($_FILES);
        $this->setServer($_SERVER);
        $this->setEnvironment($_ENV);
        $this->setCookies(new Cookie());
        $this->setSession(new Session());

        if (isset($_SERVER['HTTP_ACCEPT'])) {
            $this->setAccept($_SERVER['HTTP_ACCEPT']);
        }

        if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            $this->setAcceptLanguages($_SERVER['HTTP_ACCEPT_LANGUAGE']);
        }
    }

    /**
     *
     * @param array $get
     * @return void
     */
    public function setQuery($get)
    {
        $this->get = $get;
    }

    /**
     *
     * @return array
     */
    public function getQuery()
    {
        return $this->get;
    }

    /**
     *
     * @param string $name
     * @return mixed|false
     */
    public function getQueryVar($name)
    {
        if (isset($this->get[$name])) {
            return $this->get[$name];
        }
        return false;
    }

    /**
     *
     * @param array $post
     * @return void
     */
    public function setPost($post)
    {
        $this->post = $post;
    }

    /**
     *
     * @return array
     */
    public function getPost()
    {
        return $this->post;
    }

    /**
     *
     * @param string $name
     * @return mixed|false
     */
    public function getPostVar($name)
    {
        if (isset($this->post[$name])) {
            return $this->post[$name];
        }
        return false;
    }

    /**
     *
     * @param array $files
     * @return void
     */
    public function setFiles($files)
    {
        $this->files = $files;
    }

    /**
     *
     * @return array
     */
    public function getFiles()
    {
        return $this->files;
    }

    /**
     *
     * @param string $name
     * @return mixed|false
     */
    public function getFilesVar($name)
    {
        if (isset($this->files[$name])) {
            return $this->files[$name];
        }
        return false;
    }

    /**
     *
     * @param array $server
     * @return void
     */
    public function setServer($server)
    {
        $this->server = $server;
    }

    /**
     *
     * @return array
     */
    public function getServer()
    {
        return $this->server;
    }

    /**
     *
     * @param string $name
     * @return mixed|false
     */
    public function getServerVar($name)
    {
        if (array_key_exists($name, $this->server)) {
            return $this->server[$name];
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
     * @return EllevenFw\Library\Basic\Cookie
     */
    public function getCookies()
    {
        return $this->cookies;
    }

    /**
     *
     * @param EllevenFw\Library\Basic\Cookie $Cookie
     * @return void
     */
    public function setCookies(Cookie $Cookie)
    {
        $this->cookies = $Cookie;
    }

    /**
     *
     * @return EllevenFw\Library\Basic\Session
     */
    public function getSession()
    {
        return $this->session;
    }

    /**
     *
     * @param EllevenFw\Library\Basic\Session $Session
     * @return void
     */
    public function setSession(Session $Session)
    {
        $this->session = $Session;
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
    public function isFromTrustedProxy()
    {
        if (empty($this->getTrustedProxies())) {
            return false;
        }
        $address = $this->getServerVar('REMOTE_ADDR');
        return in_array($address, $this->trustedProxies);
    }

    /**
     *
     * @return string
     */
    public function getClientIp()
    {
        $ipAddress = null;
        if ($this->isFromTrustedProxy() && $this->getServerVar('HTTP_X_FORWARDED_FOR')) {
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
        $method = strtoupper($this->getServerVar('REQUEST_METHOD'));

        if ($method === 'POST') {
            if ($this->getServerVar('X-HTTP-METHOD-OVERRIDE') !== false) {
                $method = strtoupper($this->getServerVar('X-HTTP-METHOD-OVERRIDE'));
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
        return in_array($this->getMethod(), array('GET', 'HEAD'));
    }

    /**
     *
     * @todo Corrigir Exceção
     * @todo Adicionar verificação de hosts confiáveis para evitar ataques de
     * injeção de cabeçalhos (Host Header Injection Attacks)
     * @return string
     */
    public function getHost()
    {
        $host = '';
        if ($this->isFromTrustedProxy() && $this->getServerVar('HTTP_X_FORWARDED_HOST')) {
            $host = $this->getServerVar('HTTP_X_FORWARDED_HOST');
        } elseif ($this->getServerVar('HTTP_HOST')) {
            $host = $this->getServerVar('HTTP_HOST');
        } elseif ($this->getServerVar('SERVER_NAME')) {
            $host = $this->getServerVar('SERVER_NAME');
        } else {
            $host = $this->getServerVar('SERVER_ADDR');
        }
        return $host;
    }

    /**
     *
     * @return string
     */
    public function getPort()
    {
        if ($this->isFromTrustedProxy() && $this->getServerVar('HTTP_X_FORWARDED_PORT')) {
            return $this->getServerVar('HTTP_X_FORWARDED_PORT');
        }
        return $this->getServerVar('SERVER_PORT');
    }

    /**
     *
     * @return string
     */
    public function getScheme()
    {
        if ($this->isFromTrustedProxy() && $this->getServerVar('HTTP_X_FORWARDED_PROTO') !== false) {
            return $this->getServerVar('HTTP_X_FORWARDED_PROTO');
        }
        $https = $this->getServerVar('HTTPS');
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
     * @return voi
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
    public function getProtocol()
    {
        return $this->getServerVar('SERVER_PROTOCOL');
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
     * @return bollean|integer
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
        if ($this->getServerVar('PHP_AUTH_USER')) {
            return $this->getServerVar('PHP_AUTH_USER');
        }
        return false;
    }

    /**
     *
     * @return boolean|string
     */
    public function getPassword()
    {
        if ($this->getServerVar('PHP_AUTH_PW')){
            return $this->getServerVar('PHP_AUTH_PW');
        }
        return false;
    }

    public function getRequestTime()
    {
        return $this->getServerVar('REQUEST_TIME');
    }

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
    public function getUrlPath()
    {
        if ($this->pathUrl !== null) {
            return $this->pathUrl;
        }

        if ($this->getServerVar('PATH_INFO') !== false) {
            $uri = $this->getServerVar('PATH_INFO');
        } elseif ($this->getServerVar('REQUEST_URI') !== false) {
            $requestUri = $this->getServerVar('REQUEST_URI');
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
        } elseif ($this->getServerVar('PHP_SELF') !== false && $this->getServerVar('SCRIPT_NAME') !== false) {
            $phpSelf = $this->getServerVar('PHP_SELF');
            $scriptName = $this->getServerVar('SCRIPT_NAME');
            $uri = str_replace($scriptName, '', $phpSelf);
        } elseif ($this->getServerVar('HTTP_X_REWRITE_URL') !== false) {
            $uri = $this->getServerVar('HTTP_X_REWRITE_URL');
        } else {
            $var = $this->getServerVar('argv');
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

    public function getQueryString()
    {
        if ($this->queryString !== null) {
            return $this->queryString;
        }

        $getParams = $this->getQuery();
        if (empty($getParams)) {
            return '';
        }
        $this->queryString = http_build_query($getParams, null, '&', PHP_QUERY_RFC3986);
        return $this->queryString;
    }

    public function getFullUrl()
    {
        $url = '';
        $url.= $this->getScheme() . '://';

        if ($this->getUser() !== false && $this->getPassword() !== false) {
            $url.= $this->getUser() . ':';
            $url.= $this->getPassword() . '@';
        }
        $url.= $this->getHost();

        if ($this->getPort() !== false && $this->getPort() !== '80' && $this->getPort() !== '443') {
            $url.= ':' . $this->getPort();
        }

        $url.= $this->getUrlPath();

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
     * @return string
     */
    public function readInput()
    {
        if (empty($this->input)) {
            $fh = fopen('php://input', 'r');
            $content = stream_get_contents($fh);
            fclose($fh);
            $this->input = $content;
        }

        return $this->input;
    }

    /**
     *
     * @param string $input
     */
    public function setInput($input)
    {
        $this->_input = $input;
    }

    /**
     *
     * @param string $acceptString
     * @return array
     */
    public function parseAcceptWithQualifier($acceptString)
    {
        $accept = array();

        $parser = explode(',', $acceptString);
        $parser = array_filter($parser);

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
