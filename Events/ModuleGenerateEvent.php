<?php


namespace TheliaStudio\Events;
use Symfony\Component\EventDispatcher\Event;


/**
 * Class ModuleGenerateEvent
 * @package TheliaStudio\Events
 * @author Benjamin Perche <bperche9@gmail.com>
 */
class ModuleGenerateEvent extends Event
{
    protected $moduleCode;

    protected $tables;

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
     * @param mixed $moduleCode
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
     * @param mixed $tables
     * @return $this
     */
    public function setTables($tables)
    {
        $this->tables = $tables;
        return $this;
    }
}
