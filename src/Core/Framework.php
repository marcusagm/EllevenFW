<?php

/**
 * 06 - Elleven Framework
 * Copyright 2017 Marcus Maia <contato@marcusmaia.com>
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.md
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright   Copyright (c) Marcus Maia <contato@marcusmaia.com>
 * @link        http://elleven.marcusmaia.com Elleven Kit
 * @since       1.0.0
 * @license     http://www.opensource.org/licenses/mit-license.php MIT License
 */

namespace EllevenFw\Core;

use DirectoryIterator;
use EllevenFw\Library\Network\ServerRequestUtils;
use EllevenFw\Core\Routing\Dispatcher;
use EllevenFw\Core\Routing\Mapper;
use EllevenFw\Core\Configure\Configure;

/**
 * Description of Framework
 *
 * @author Marcus Maia <contato@marcusmaia.com>
 */
class Framework
{
    public function init()
    {
        $this->loadConfigs(APP_CONFIG);
        $this->loadEnvironment();

        $Dispatcher = new Dispatcher();
        $Dispatcher->process(
            ServerRequestUtils::createFromGlobals(),
            new Mapper()
        );
    }

    public function loadConfigs($path)
    {
        $Directory = new DirectoryIterator($path);
        foreach ($Directory as $File) {
            $filePath = $File->getPathname();
            $fileName = $File->getBasename();
            if (Configure::isValidFile($filePath)) {
                Configure::registryByFile($fileName, $filePath);
            }
        }
    }

    public function loadEnvironment()
    {
        // Configurar Logs
        // Configurar Errors
        // Configurar Datasoucers
        // Configurar Rotes
        // Configurar Assests
        // Configurar Mailer
        // Configurar Sessions
    }
}
