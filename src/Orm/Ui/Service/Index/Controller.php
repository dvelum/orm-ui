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

namespace Dvelum\Orm\Ui\Service\Index;

use Dvelum\Config;
use Dvelum\Lang;
use Dvelum\Orm\Ui\EnvParams;
use Dvelum\Orm\Ui\View\Template;
use Dvelum\Utils;
use MatthiasMullie\Minify\JS;
use Psr\Container\ContainerInterface;
use Dvelum\Request;
use Dvelum\Response\ResponseInterface;

class Controller
{
    private Request $request;
    private ResponseInterface $response;
    private EnvParams $params;

    public function __construct(Request $req, ResponseInterface $resp, EnvParams $params)
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
        /**
         * @var Lang $langService
         */
        $langService = $diContainer->get(Lang::class);
        $lang = $langService->getDictionary();
        $diContainer->get(Lang::class)
            ->getStorage()
            ->addPath(
                DVELUM_ORM_IU_DIR . '/locales/' . $langService->getDefaultDictionary() . '/'
            );
        $diContainer->get(Lang::class)->addLoader('orm_tooltips', 'orm.php');
        $dbConfigs = [];

        foreach ($diContainer->get('config.main')->get('db_configs') as $k => $v) {
            $dbConfigs[] = [
                'id' => $k,
                'title' => $lang->get($v['title'])
            ];
        }

        $template = new Template();
        $template->setData(
            [
                'lang' => $lang->getJson(),
                'orm_lang' => $diContainer->get(Lang::class)->getDictionary('orm_tooltips')->getJson(),
                'db_configs' => $dbConfigs,
                'actions' => $this->getActions(),
                'fields' => $this->getAdditionalObjectFields()
            ]
        );
        $html = $template->render(DVELUM_ORM_IU_DIR . '/templates/index.php');
        $this->response->put($html);
        $this->response->send();
        return $this->response;
    }

    private function getActions(): array
    {
        /**
         * @var ContainerInterface $diContainer
         */
        $diContainer = $this->params->getApplication()->getDiContainer();
        $configStorage = $diContainer->get(\Dvelum\Config\Storage\StorageInterface::class);

        $appRoot = '/orm/';

        $config = $configStorage->get('orm/actions.php');
        $list = $config->__toArray();
        foreach ($list as &$v) {
            $v = $appRoot . $v;
        }
        return $list;
    }

    protected function getAdditionalObjectFields(): string
    {
        /**
         * @var ContainerInterface $diContainer
         */
        $diContainer = $this->params->getApplication()->getDiContainer();
        $configStorage = $diContainer->get(\Dvelum\Config\Storage\StorageInterface::class);

        $config = $configStorage->get('orm/properties.php')->__toArray();

        if (empty($config)) {
            return '';
        }
        $fieldsJs = array_column($config, 'js_field');
        return implode(',', array_values($fieldsJs));
    }

    private function buildJs()
    {
        $files = [
            'front/common.js',

            'front/components/SearchPanel.js',
            'front/records/field/Grid.js',
            'front/records/field/Layout.js',
            'front/records/ExportsGrid.js',
            'front/records/TypesGrid.js',
            'front/records/FactoriesGrid.js',
            'front/records/Grid.js',
            'front/records/Layout.js',
            'front/Application.js'
        ];

        $min = new JS();
        foreach ($files as $file) {
            $min->add(DVELUM_DR_IU_DIR . '/' . $file);
        }
        $min->minify(DVELUM_DR_IU_DIR . '/public/js/build.js');
    }
}