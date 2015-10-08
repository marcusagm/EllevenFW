<?php
/*
 * Elleven Framework
 * Copyright 2015 Marcus Maia <contato@marcusmaia.com>.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright	Copyright (c) Marcus Maia <contato@marcusmaia.com>
 * @link		http://elleven.marcusmaia.com Elleven Kit
 * @since		1.0.0
 * @license	http://www.opensource.org/licenses/mit-license.php MIT License
 */

/**
 * @author Marcus Maia <contato@marcusmaia.com>
 */
// TODO: check include path
//ini_set('include_path', ini_get('include_path'));

date_default_timezone_set('America/Sao_Paulo');

if (file_exists(dirname(__DIR__) . '/vendor/autoload.php') === false) {
    trigger_error('Autoload file not found.', E_USER_ERROR);
}
require dirname(__DIR__) . '/vendor/autoload.php';
