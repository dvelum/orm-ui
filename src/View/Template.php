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

namespace Dvelum\Orm\Ui\View;

class Template
{
    /**
     * Template data (local variables)
     * @var array
     */
    private $data = [];

    /**
     * Template Render
     * @param string $templatePath — the path to the template file
     * @return string
     */
    public function render(string $templatePath): string
    {
        $realPath = $templatePath;

        if (!$realPath) {
            return '';
        }

        \ob_start();
        include $realPath;
        $result = \ob_get_clean();

        return (string)$result;
    }

    /**
     * Set property
     * @param string $name
     * @param mixed $value
     * @return void
     */
    public function set(string $name, $value): void
    {
        $this->data[$name] = $value;
    }

    /**
     * Set multiple properties
     * @param array $data
     * @return void
     */
    public function setProperties(array $data): void
    {
        foreach ($data as $name => $value) {
            $this->data[$name] = $value;
        }
    }

    /**
     * Get property
     * @param string $name
     * @return mixed
     */
    public function get(string $name)
    {
        if (!isset($this->data[$name])) {
            return null;
        }

        return $this->data[$name];
    }

    public function __set($name, $value)
    {
        $this->set($name, $value);
    }

    public function __isset($name)
    {
        return isset($this->data[$name]);
    }

    public function __get($name)
    {
        return $this->get($name);
    }

    public function __unset($name)
    {
        unset($this->data[$name]);
    }

    /**
     * Empty template data
     * @return void
     */
    public function clear(): void
    {
        $this->data = [];
    }

    /**
     * Get template data
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * Redefine template data using an associative key-value array,
     * old and new data merge
     * @param array $data
     * @return void
     */
    public function setData(array $data): void
    {
        $this->data = $data;
    }

    /**
     * Render sub template
     * @param string $templatePath
     * @param array $data
     * @return string
     * @throws \Exception
     */
    public function renderTemplate(string $templatePath, array $data = []): string
    {
        $tpl = new self();
        $tpl->setData($data);
        return $tpl->render($templatePath);
    }
}