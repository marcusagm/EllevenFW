<?php
/**
 * Elleven Framework
 * Copyright 2016 Marcus Maia <contato@marcusmaia.com>.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright  Copyright (c) Marcus Maia <contato@marcusmaia.com>
 * @link       http://elleven.marcusmaia.com Elleven Kit
 * @since      1.0.0
 * @license    http://www.opensource.org/licenses/mit-license.php MIT License
 */

namespace EllevenFw\Core\Configure\Engine;

use EllevenFw\Core\Configure\Engine\ConfigureEngineInterface;
use EllevenFw\Core\Exception\Types\CoreException;

/**
 * Description of JsonConfigureEngine
 *
 * @author Marcus Maia <contato@marcusmaia.com>
 */
class JsonConfigureEngine implements ConfigureEngineInterface
{
    /**
     *
     * @var string
     */
    private $path = null;

    /**
     *
     * @var array
     */
    private $data = array();

    /**
     *
     * @var bool
     */
    private $read = false;

    /**
     *
     * @param string $path
     * @throws CoreException
     */
    public function __construct($path) {
        $this->path = $path;

        if (file_exists($path) === false) {
            throw new CoreException('Arquivo de configuração não existente.');
        }

        $this->read();
    }

    /**
     *
     * @param string $path
     * @return bool
     */
    public function dump($path = null)
    {
        $path = $path ? $path : $this->path;
        $encoded = json_encode($this->data, JSON_PRETTY_PRINT);
        return file_put_contents($path, $encoded) > 0;
    }

    /**
     *
     * @param array $data
     * @param bool $merge
     * @throws CoreException
     */
    public function write($data, $merge = true)
    {
        if (is_array($data) === false) {
            throw new CoreException(
                'O valor informado para adicionar no arquivo de configuração '
                . 'não é um array.'
            );
        }

        if ($merge === true) {
            $newData = array_merge_recursive($this->data, $data);
            $this->data = $newData;
        } else {
            $this->data = $data;
        }
    }

    /**
     *
     * @return array
     * @throws CoreException
     */
    public function read()
    {
        if ($this->read === false) {
            $data = file_get_contents($this->path);
            $data = json_decode($data, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new CoreException(
                    'Erro ao ler o arquivo de configuração. '
                    . 'O formato do conteúdo está incorreto.'
                );
            }
            if (is_array($data) === false) {
                throw new CoreException(
                    'Erro ao ler o arquivo de configuração. '
                    . 'Não foi possível converte-lo em array.'
                );
            }
            $this->data = $data;
            $this->read = true;
        }
        return $this->data;
    }
}
