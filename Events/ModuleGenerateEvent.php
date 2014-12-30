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

    public function __construct($moduleCode)
    {
        $this->moduleCode = $moduleCode;
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
}
