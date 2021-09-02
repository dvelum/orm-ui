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

namespace Dvelum\Orm\Ui;


use Dvelum\Orm\Ui\Service\Index\Controller as IndexController;
use Dvelum\Orm\Ui\Service\Orm\Controller as OrmController;
use Dvelum\Request;
use Dvelum\Response\ResponseInterface;

class Server
{
    public function run(Request $request, ResponseInterface $response, EnvParams $params): ResponseInterface
    {
        return $this->routeRequest($request, $response, $params);
    }

    public function routeRequest(Request $request, ResponseInterface $response, EnvParams $params): ResponseInterface
    {
        $routes = [
            'index' => IndexController::class,
            'orm' => OrmController::class
        ];

        $uriParts = $request->getPathParts();

        if (isset($routes[$uriParts[0] ?? 'index'])) {
            $controllerClass = $routes[$uriParts[0]];
        } else {
            $controllerClass = $routes['index'];
        }

        $methodName = $uriParts[1] ?? 'index';
        $controller = new $controllerClass($request, $response, $params);
        if (method_exists($controller, $methodName . 'Action')) {
            $response = $controller->{$methodName . 'Action'}();
        } else {
            $response = $controller->indexAction();
        }

        return $response;
    }
}

