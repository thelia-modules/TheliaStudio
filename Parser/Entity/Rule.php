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
 * Class Rule
 * @package TheliaStudio\Parser
 * @author Benjamin Perche <bperche9@gmail.com>
 */
class Rule
{
    protected $source;

    protected $ruleCollection;

    /**
     * @return mixed
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * @param  mixed $source
     * @return $this
     */
    public function setSource($source)
    {
        $this->source = $source;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getRuleCollection()
    {
        return $this->ruleCollection;
    }

    /**
     * @param  mixed $ruleCollection
     * @return $this
     */
    public function setRuleCollection($ruleCollection)
    {
        $this->ruleCollection = $ruleCollection;

        return $this;
    }

    public function addRule($regex, $replacement)
    {
        $this->ruleCollection[] = [$regex, $replacement];
    }
}
