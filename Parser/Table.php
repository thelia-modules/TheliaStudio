<?php
/*************************************************************************************/
/* This file is part of the Thelia package.                                          */
/*                                                                                   */
/* Copyright (c) OpenStudio                                                          */
/* email : dev@thelia.net                                                            */
/* web : http://www.thelia.net                                                       */
/*                                                                                   */
/* For the full copyright and license information, please view the LICENSE.txt       */
/* file that was distributed with this source code.                                  */
/*************************************************************************************/

namespace TheliaStudio\Parser;

use Symfony\Component\DependencyInjection\Container;

/**
 * Class Table
 * @package TheliaStudio\Parser
 * @author Benjamin Perche <bperche9@gmail.com>
 */
class Table
{
    /** @var  string */
    protected $tableName;

    /** @var Column[] */
    protected $columns = array();

    /** @var array  */
    protected $behaviors;

    protected $namespace;

    public function __construct($tableName, $namespace = '', array $behaviors = array())
    {
        $this->tableName = $tableName;
        $this->behaviors = $behaviors;
        $this->namespace = $namespace;
    }

    public function addColumn(Column $column)
    {
        $this->columns[$column->getName()] = $column;
    }

    public function getColumn($name, $default = null)
    {
        return $this->has($name) ? $this->columns[$name] : $default;
    }

    public function has($name)
    {
        return isset($this->columns[$name]);
    }

    public function hasPosition()
    {
        return $this->has("position");
    }

    public function hasVisible()
    {
        return $this->has("visible");
    }

    public function addBehavior($name)
    {
        if (!in_array($name, $this->behaviors)) {
            $this->behaviors[] = $name;
        }

        return $this;
    }

    public function hasBehavior($name)
    {
        return in_array($name, $this->behaviors);
    }

    public function hasI18nBehavior()
    {
        return $this->hasBehavior("i18n");
    }

    public function hasTimestampableBehavior()
    {
        return $this->hasBehavior("timestampable");
    }

    public function getQueryClass()
    {
        return $this->getModelClass() . "Query";
    }

    public function getFullQueryClass()
    {
        return $this->getFullModelClass() . "Query";
    }

    public function getModelClass()
    {
        return Container::camelize($this->tableName);
    }

    public function getFullModelClass()
    {
        return  $this->getNamespace() . "\\" . $this->getModelClass();
    }

    /**
     * @return string
     */
    public function getTableName()
    {
        return Container::camelize($this->tableName);
    }

    public function getRawTableName()
    {
        return $this->tableName;
    }

    /**
     * @return Column[]
     */
    public function getColumns()
    {
        return $this->columns;
    }

    /**
     * @return array
     */
    public function getBehaviors()
    {
        return $this->behaviors;
    }

    /**
     * @return string
     */
    public function getNamespace()
    {
        return $this->namespace;
    }

    public function getI18nColumns()
    {
        $collection = array();

        foreach ($this->columns as $column) {
            if ($column->isI18n()) {
                $collection[] = $column;
            }
        }

        return $collection;
    }

    public function getListTemplateName()
    {
        return str_replace("_", "-", $this->tableName) . "s";
    }

    public function getEditionTemplateName()
    {
        return str_replace("_", "-", $this->tableName) . "-edit";
    }

    public function getModuleCode()
    {
        return explode("\\", $this->namespace)[0];
    }

    public function getListPathInfo()
    {
        return "/admin/module/" . $this->getModuleCode() . "/" . $this->tableName;
    }

    public function getEditionPathInfo()
    {
        return $this->getListPathInfo() . "/edit";
    }

    public function getLoopType()
    {
        return str_replace("_", "-", strtolower($this->tableName));
    }
}
