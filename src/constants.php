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
/**
 * Define o tempo de inicio da execução da aplicação
 */
define('EFW_TIME_START', microtime(true));

/**
 * Abreviação da constante da barra de separação de caminhos de diretórios.
 */
if (!defined('DS')) {
    define('DS', DIRECTORY_SEPARATOR);
}

/**
 * Pasta da instalação do Elleven Framework.
 */
if (!defined('EFW_ROOT')) {
    define('EFW_ROOT', dirname(__DIR__) . DS);
}

/**
 * Pasta para o Elleven Framework.
 */
if (!defined('EFW_PATH')) {
    define('EFW_PATH', EFW_ROOT . DS . 'src' . DS);
}
