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
 * Class Tag
 * @package TheliaStudio\Parser\Entity
 * @author Benjamin Perche <bperche9@gmail.com>
 */
class Tag
{
    protected $parameters = array();

    public function __construct($name, array $parameters = array())
    {
        $this->parameters = $parameters;
        $this->parameters["name"] = $name;
    }

    /**
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * @param  array $parameters
     * @return $this
     */
    public function setParameters(array $parameters)
    {
        $this->parameters = $parameters;

        return $this;
    }

    public function has($name)
    {
        return isset($this->parameters[$name]);
    }

    public function get($name, $default = null)
    {
        if ($this->has($name)) {
            return $this->parameters[$name];
        }

        return $default;
    }
}
