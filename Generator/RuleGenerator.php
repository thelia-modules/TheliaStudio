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

namespace TheliaStudio\Generator;

use TheliaStudio\Events\ModuleGenerateEvent;
use TheliaStudio\Parser\Rule;
use TheliaStudio\Parser\RuleReader;

/**
 * Class RuleGenerator
 * @package TheliaStudio\Generator
 * @author Benjamin Perche <bperche9@gmail.com>
 */
class RuleGenerator extends BaseGenerator
{
    public function generate(ModuleGenerateEvent $event)
    {
        $ruleReader = new RuleReader();

        /** @var \SplFileInfo $rule */
        foreach ($this->findInPath($event->getResourcesPath(), "/\.rule$/") as $rule) {
            $relativePath = str_replace($event->getResourcesPath(), "", $rule->getRealPath());

            $completePath = $event->getModulePath() . $relativePath;
            $completePath = substr($completePath, 0, -5); // remove .rule extension

            $rule = $ruleReader->readRules($rule->getRealPath());
            $this->processRule($rule, $completePath);
        }
    }

    protected function processRule(Rule $rule, $destination)
    {
        $sourceFile = dirname($destination) . DS . $rule->getSource();

        $source = "";
        if (is_file($sourceFile) && is_readable($sourceFile)) {
            $source = file_get_contents($sourceFile);
        }

        foreach ($rule->getRuleCollection() as $regex) {
            $source = preg_replace($regex[0], $regex[1], $source);
        }

        @$this->writeFile($destination, $source);
    }

}
