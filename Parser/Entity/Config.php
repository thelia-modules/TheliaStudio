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

namespace TheliaStudio\Parser\Entity;

/**
 * Class Config
 * @package TheliaStudio\Parser\Entity
 * @author Benjamin Perche <bperche9@gmail.com>
 */
class Config
{
    protected $loops = array();

    protected $forms = array();

    protected $services = array();

    protected $hooks = array();

    public function addLoop(Loop $loop)
    {
        $this->loops[$loop->getName()] = $loop;
    }

    public function addForm(Form $form)
    {
        $this->forms[$form->getName()] = $form;
    }

    public function addService(Service $service)
    {
        $this->services[$service->getId()] = $service;
    }

    public function addHook(Hook $hook)
    {
        $this->hooks[$hook->getId()] = $hook;
    }

    public function hasLoop($name)
    {
        return isset($this->loops[$name]);
    }

    public function hasForm($name)
    {
        return isset($this->forms[$name]);
    }

    public function hasService($name)
    {
        return isset($this->services[$name]);
    }

    public function hasHook($name)
    {
        return isset($this->hooks[$name]);
    }

    /**
     * @return Loop[]
     */
    public function getLoops()
    {
        return $this->loops;
    }

    /**
     * @param  array $loops
     * @return $this
     */
    public function setLoops(array $loops)
    {
        $this->loops = $loops;

        return $this;
    }

    /**
     * @return Form[]
     */
    public function getForms()
    {
        return $this->forms;
    }

    /**
     * @param  array $forms
     * @return $this
     */
    public function setForms(array $forms)
    {
        $this->forms = $forms;

        return $this;
    }

    /**
     * @return Service[]
     */
    public function getServices()
    {
        return $this->services;
    }

    /**
     * @param  array $services
     * @return $this
     */
    public function setServices(array $services)
    {
        $this->services = $services;

        return $this;
    }

    /**
     * @return Hook[]
     */
    public function getHooks()
    {
        return $this->hooks;
    }

    /**
     * @param  array $hooks
     * @return $this
     */
    public function setHooks(array $hooks)
    {
        $this->hooks = $hooks;

        return $this;
    }

    /**
     * @param $name
     * @param  mixed $default
     * @return Form
     */
    public function getForm($name, $default = null)
    {
        if ($this->hasForm($name)) {
            return $this->forms[$name];
        }

        return $default;
    }

    /**
     * @param $name
     * @param  mixed $default
     * @return Hook
     */
    public function getHook($name, $default = null)
    {
        if ($this->hasHook($name)) {
            return $this->hooks[$name];
        }

        return $default;
    }

    /**
     * @param $name
     * @param  mixed   $default
     * @return Service
     */
    public function getService($name, $default = null)
    {
        if ($this->hasService($name)) {
            return $this->services[$name];
        }

        return $default;
    }

    /**
     * @param $name
     * @param  mixed $default
     * @return Loop
     */
    public function getLoop($name, $default = null)
    {
        if ($this->hasLoop($name)) {
            return $this->loops[$name];
        }

        return $default;
    }

    public function mergeConfig(Config $config)
    {
        foreach ($config->getForms() as $form) {
            if (!$this->hasForm($form->getName())) {
                $this->addForm($form);
            }
        }

        foreach ($config->getLoops() as $loop) {
            if (!$this->hasLoop($loop->getName())) {
                $this->addLoop($loop);
            }
        }

        foreach ($config->getServices() as $service) {
            if (!$this->hasService($service->getId())) {
                $this->addService($service);
            }
        }

        foreach ($config->getHooks() as $hook) {
            if (!$this->hasHook($hook->getId())) {
                $this->addHook($hook);
            }
        }
    }
}
