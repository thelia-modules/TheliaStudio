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

use TheliaStudio\Parser\Entity\Rule;

/**
 * Class RuleReader
 * @package TheliaStudio\Parser
 * @author Benjamin Perche <bperche9@gmail.com>
 */
class RuleParser
{
    public function readRules($filePath)
    {
        $rule = new Rule();

        if (is_file($filePath) && is_readable($filePath)) {
            $contents = file_get_contents($filePath);

            $data = json_decode($contents, true);

            if (null !== $data) {
                $rule->setSource($this->getSource($data, $filePath));
            }

            if (isset($data["rules"])) {
                if (is_array($data["rules"])) {
                    $this->doReadRules($data["rules"], $rule);
                }
            }
        }

        return $rule;
    }

    protected function getSource(array $data, $filePath)
    {
        if (!isset($data["file"]) || !is_string($data["file"]) || '' === $data["file"]) {
            throw new \InvalidArgumentException(sprintf(
                "The given 'file' entry for '%s' is not valid",
                isset($data["file"]) ? $data["file"] : ""
            ));
        }

        return $data["file"];
    }

    public function doReadRules($rules, Rule $rule)
    {
        if (!is_array($rules)) {
            return;
        }

        if (!isset($rules["replace"]) || !is_array($rules["replace"])) {
            return;
        }

        foreach ($rules["replace"] as $replacement) {
            if (count($replacement) == 2) {
                $rule->addRule($replacement[0], $replacement[1]);
            }
        }
    }
}
