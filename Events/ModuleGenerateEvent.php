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

namespace TheliaStudio\Events;

use Symfony\Component\EventDispatcher\Event;
use Thelia\Core\Thelia;

/**
 * Class ModuleGenerateEvent
 * @package TheliaStudio\Events
 * @author Benjamin Perche <bperche9@gmail.com>
 */
class ModuleGenerateEvent extends Event
{
    protected $moduleCode;

    protected $modulePath;

    /**
     * @var \Thelia\Core\Thelia
     */
    protected $kernel;

    protected $tables;

    protected $resourcesPath;

    /**
     * @var \TheliaStudio\Parser\Table[]
     */
    protected $entities;

    public function __construct($moduleCode, $tables)
    {
        $this->moduleCode = $moduleCode;
        $this->tables = $tables;
    }

    /**
     * @return mixed
     */
    public function getModuleCode()
    {
        return $this->moduleCode;
    }

    /**
     * @param  mixed $moduleCode
     * @return $this
     */
    public function setModuleCode($moduleCode)
    {
        $this->moduleCode = $moduleCode;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTables()
    {
        return $this->tables;
    }

    /**
     * @param  mixed $tables
     * @return $this
     */
    public function setTables($tables)
    {
        $this->tables = $tables;

        return $this;
    }

    /**
     * @return \TheliaStudio\Parser\Table[]
     */
    public function getEntities()
    {
        return $this->entities;
    }

    /**
     * @param  \TheliaStudio\Parser\Table[] $entites
     * @return $this
     */
    public function setEntities(array $entities)
    {
        $this->entities = $entities;

        return $this;
    }

    /**
     * @return \Thelia\Core\Thelia
     */
    public function getKernel()
    {
        return $this->kernel;
    }

    /**
     * @param  \Thelia\Core\Thelia $kernel
     * @return $this
     */
    public function setKernel(Thelia $kernel)
    {
        $this->kernel = $kernel;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getModulePath()
    {
        return $this->modulePath;
    }

    /**
     * @param  mixed $modulePath
     * @return $this
     */
    public function setModulePath($modulePath)
    {
        $this->modulePath = $modulePath;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getResourcesPath()
    {
        return $this->resourcesPath;
    }

    /**
     * @param  mixed $resourcesPath
     * @return $this
     */
    public function setResourcesPath($resourcesPath)
    {
        $this->resourcesPath = $resourcesPath;

        return $this;
    }
}
