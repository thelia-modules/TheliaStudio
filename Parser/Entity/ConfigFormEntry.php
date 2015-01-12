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
 * Class ConfigFormEntry
 * @package TheliaStudio\Parser\Entity
 * @author Benjamin Perche <bperche9@gmail.com>
 */
class ConfigFormEntry
{
    protected $name;
    protected $type;

    protected $required;
    protected $regex;
    protected $minSize;
    protected $maxSize;

    public function __construct($name, $type)
    {
        $this->name = $name;
        $this->type = $type;
    }

    /**
     * @return mixed
     */
    public function getMaxSize()
    {
        return $this->maxSize;
    }

    /**
     * @param mixed $maxSize
     * @return $this
     */
    public function setMaxSize($maxSize)
    {
        $this->maxSize = $maxSize;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param mixed $type
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return bool
     */
    public function isRequired()
    {
        return $this->required;
    }

    /**
     * @param mixed $required
     * @return $this
     */
    public function setRequired($required)
    {
        $this->required = $required;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getRegex()
    {
        return $this->regex;
    }

    public function getFormattedRegex()
    {
        return '/' . $this->regex . '/';
    }

    /**
     * @param mixed $regex
     * @return $this
     */
    public function setRegex($regex)
    {
        $this->regex = $regex;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getMinSize()
    {
        return $this->minSize;
    }

    /**
     * @param mixed $minSize
     * @return $this
     */
    public function setMinSize($minSize)
    {
        $this->minSize = $minSize;
        return $this;
    }

    public function getCamelizedName()
    {
        return $this->camelize($this->name);
    }

    protected function camelize($str)
    {
        return preg_replace_callback(
            "/_([a-z])/",
            function($m) {
                return strtoupper($m[1]);
            },
            ucfirst($str)
        );
    }

    public function getRealType()
    {
        return $this->type;
    }

    public function getConstantName()
    {
        return strtoupper($this->name);
    }

    public function hasSize()
    {
        return $this->hasMaxSize() || $this->hasMinSize();
    }

    public function hasMinSize()
    {
        return null !== $this->getMinSize();
    }

    public function hasMaxSize()
    {
        return null !== $this->getMaxSize();
    }

    public function hasRegex()
    {
        return is_string($this->regex) && '' !== trim($this->regex);
    }
}
