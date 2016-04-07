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

if (!defined('DS')) {
    define('DS', DIRECTORY_SEPARATOR);
}

define('TMP', sys_get_temp_dir() . DS);
define('LOGS', TMP . 'logs' . DS);
define('CACHE', TMP . 'cache' . DS);
define('SESSIONS', TMP . 'sessions' . DS);

define('EFW_ROOT', dirname(__DIR__) . DS);
define('EFW_PATH', EFW_ROOT . 'src' . DS);
define('EFW_TESTS', EFW_ROOT . 'tests' . DS);

define('APP_NAME', 'AppTest');
define('APP_PATH', EFW_TESTS . 'apptest' . DS);
define('APP_PUBLIC', APP_PATH . 'public' . DS);
define('APP_PUBLIC_CACHE', APP_PUBLIC . 'cache' . DS);
define('APP_CONFIG', APP_PATH . 'config' . DS);
define('APP_LOGS', APP_PATH . 'logs' . DS);

if (file_exists(dirname(__DIR__) . '/vendor/autoload.php') === false) {
    trigger_error('Autoload file not found.', E_USER_ERROR);
}
require dirname(__DIR__) . '/vendor/autoload.php';
