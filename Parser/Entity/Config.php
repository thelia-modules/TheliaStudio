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
}
