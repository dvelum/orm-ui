<?php
/*
 * DVelum ORM UI library https://github.com/dvelum/orm-ui
 *
 * Copyright (C) 2011-2021 Kirill Yegorov
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

declare(strict_types=1);

namespace Dvelum\Orm\Ui\Service\Orm;

use Dvelum\Orm\Ui\EnvParams;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Proxy to ORM actions
 */
class Controller
{
    private ServerRequestInterface $request;
    private ResponseInterface $response;
    private EnvParams $params;

    public function __construct(ServerRequestInterface $req, ResponseInterface $resp, EnvParams $params)
    {
        $this->request = $req;
        $this->response = $resp;
        $this->params = $params;
    }

    public function indexAction(): ResponseInterface
    {
        /**
         * @var ContainerInterface $diContainer
         */
        $diContainer = $this->params->getApplication()->getDiContainer();
        $ormController = new \Dvelum\App\Orm\Api\Router($diContainer, 1, true);
        return $ormController->route($this->request, $this->response);
    }

}