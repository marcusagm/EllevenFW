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
 * Esta framework suporta apenas a versão 5.3 do PHP.
 * Um erro é gerado caso a versão seja anterior a 5.3.
 */
if (version_compare(PHP_VERSION, '5.3') < 0) {
    trigger_error('This framework only works with PHP 5.3 or newer', E_USER_ERROR);
}

/**
 * A extensão é necessária para lidar com a internacionalização da aplicação.
 * Mesmo que a aplicação possua apenas uma linguagem, é necessário para tratamento
 * de tipos como datas, valores monetários, etc.
 */
//if (!extension_loaded('intl')) {
//    trigger_error('You must enable the intl extension to use EllEven Framework.', E_USER_ERROR);
//}

/**
 * Configuração de ambiente
 */
error_reporting(E_ALL & ~E_STRICT);

/**
 * Inclui o arquivo de constantes e do autoload.
 * @todo Verificar a necessidade de incluir o autoload e quais os riscos.
 */
require __DIR__ . '/constants.php';

if (file_exists(dirname(__DIR__) . '/vendor/autoload.php') === false) {
    trigger_error('Autoload file not found.', E_USER_ERROR);
}
require EFW_ROOT . 'vendor' . DS . 'autoload.php';

/**
 * Carrega as classes que serão usadas para iniciar a aplicação.
 */
use EllevenFw\Core\Routing\Dispatcher;
use EllevenFw\Core\Routing\Mapper;
use EllevenFw\Library\Network\ServerRequestUtils;

/**
 * Inicia a aplicação realizando a analise da requisição identificando como proceder.
 */
$Dispatcher = new Dispatcher();
$Dispatcher->process(
    ServerRequestUtils::createFromGlobals(),
    new Mapper()
);
