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

/**
 * Description of ConfigureEngineInterface
 *
 * @author Marcus Maia <contato@marcusmaia.com>
 */
interface ConfigureEngineInterface
{
    /**
     *
     * @param string $path
     */
    public function __construct($path);

    /**
     *
     * @param string $type
     */
    public function dump($path = null);

    /**
     *
     * @param array $data
     */
    public function write($data, $merge = true);

    /**
     *
     */
    public function read();
}
