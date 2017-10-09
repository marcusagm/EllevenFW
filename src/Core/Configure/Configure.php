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

namespace EllevenFw\Core\Configure;

use EllevenFw\Core\Exception\Types\CoreException;
use EllevenFw\Core\Configure\Engine\ConfigureEngineInterface;

/**
 * Description of Configure
 *
 * @author Marcus Maia <contato@marcusmaia.com>
 */
class Configure
{
    /**
     *
     * @var array
     */
    private static $engines = array();

    /**
     *
     * @var array
     */
    private static $data = array();

    /**
     *
     * @var array
     */
    private static $extensions = array(
        'json' => 'EllevenFw\Core\Configure\Engine\JsonConfigureEngine',
        'php' => 'EllevenFw\Core\Configure\Engine\PhpConfigureEngine',
    );

    /**
     *
     * @param string $name
     * @param EllevenFw\Core\Configure\Engine\ConfigureEngineInterface $engine
     */
    public static function registry($name, ConfigureEngineInterface $engine)
    {
        static::$engines[$name] = $engine;
        static::$data[$name] = $engine->read();
    }

    /**
     *
     * @param string $name
     * @param string $path
     * @throws CoreException
     */
    public static function registryByFile($name, $path)
    {
        if (file_exists($path) === false) {
            throw new CoreException('Arquivo de configuração não existente.');
        }

        $basename = pathinfo($path, PATHINFO_BASENAME);
        $extension = pathinfo($path, PATHINFO_EXTENSION);

        if (isset(static::$extensions[$extension]) === false) {
            throw new CoreException(sprintf(
                'Não foi possível carregar o arquivo de configuração "%s".'
                . ' O tipo de engine que deve ser usado não foi reconhecido.',
                $basename
            ));
        }

        static::registry($name, new static::$extensions[$extension]($path));
    }

    public static function isValidFile($path)
    {
        $extension = pathinfo($path, PATHINFO_EXTENSION);
        return isset(static::$extensions[$extension]);
    }

    /**
     *
     * @param string $name
     * @param EllevenFw\Core\Configure\Engine\ConfigureEngineInterface $engine
     * @return bool
     */
    public static function checkEngine($name, ConfigureEngineInterface $engine)
    {
        if(isset(static::$engines[$name]) === false)  {
            return false;
        }

        return static::$engines[$name] === $engine;
    }

    /**
     *
     * @param string $name
     * @return EllevenFw\Core\Configure\Engine\ConfigureEngineInterface
     * @throws EllevenFw\Core\Exception\Types\CoreException
     */
    public static function getEngine($name)
    {
        if(isset(static::$engines[$name]) === false)  {
            throw new CoreException(sprintf(
                'O mecanismo de configurações com o nome "%s" não foi encontrado.',
                $name
            ));
        }

        return static::$engines[$name];
    }

    /**
     *
     * @param string $name
     * @param array $data
     * @throws EllevenFw\Core\Exception\Types\CoreException
     */
    public static function write($name, $data)
    {
        if (is_array($data) === false) {
            throw new CoreException(sprintf(
                'Parâmetro inválido: "%s". Não é um valor do tipo array.',
                $data
            ));
        }
        static::$engines[$name]->write($data);
        static::$data[$name] = static::$engines[$name]->read();
    }

    /**
     *
     * @param string $name
     * @param string|array $key
     * @param mixed $default
     *
     * @return mixed
     * @throws EllevenFw\Core\Exception\Types\CoreException
     */
    public static function read($name, $key, $default = null)
    {
        if (isset(static::$data[$name]) === false) {
            throw new CoreException(sprintf(
                'O conjunto de configurações com o nome "%s" não foi encontrado.',
                $name
            ));
        }
        $data = static::$data[$name];

        if ($key === null || $key === '') {
            return $default;
        }
        $parts = static::parseKey($key);

        foreach ($parts as $key) {
            if (isset($data[$key])) {
                $data = $data[$key];
            } else {
                return $default;
            }
        }
        return $data;
    }

    public static function readAll($name = null)
    {
        if ($name === null) {
            return static::$data;
        }

        if (isset(static::$data[$name]) === false) {
            throw new CoreException(sprintf(
                'O conjunto de configurações com o nome "%s" não foi encontrado.',
                $name
            ));
        }
        return static::$data[$name];
    }

    /**
     *
     * @param string $name
     * @param string|array $key
     * @param mixed $value
     *
     * @return bool
     */
    public static function check($name, $key, $value)
    {
        $data = static::$data[$name];
        $parts = static::parseKey($key);

        foreach ($parts as $key) {
            if (isset($data[$key])) {
                $data = $data[$key];
            } else {
                return false;
            }
        }
        return $data == $value;
    }

    /**
     *
     * @param string $name
     */
    public static function dump($name = null)
    {
        if ($name !== null) {
            static::$engines[$name]->dump();
        }

        foreach (static::$engines as $engine) {
            $engine->dump();
        }
    }

    /**
     *
     * @param string $key
     *
     * @return array
     * @throws EllevenFw\Core\Exception\Types\CoreException
     */
    private static function parseKey($key)
    {
        if (is_string($key) || is_numeric($key)) {
            $parts = explode('.', $key);
        } else {
            if (is_array($key) === false) {
                throw new CoreException(
                    'Parâmetro inválido e não pode ser convertido em array.'
                );
            }
            $parts = $key;
        }
        return $parts;
    }

    /**
     *
     */
    public static function clear()
    {
        static::$data = array();
        static::$engines = array();
    }
}
