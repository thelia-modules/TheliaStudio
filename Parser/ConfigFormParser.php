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

use Symfony\Component\Yaml\Yaml;
use TheliaStudio\Parser\Entity\ConfigFormEntry;

/**
 * Class ConfigFormParser
 * @package TheliaStudio\Parser
 * @author Benjamin Perche <bperche9@gmail.com>
 */
class ConfigFormParser
{
    /**
     * @param $filePath
     * @return ConfigFormEntry[]
     */
    public function loadFromYaml($filePath)
    {
        $entries = $this->getConfigFromYamlFile($filePath);

        return $this->fromArrayToObjectCollection($entries);
    }

    /**
     * @param  array             $entries
     * @return ConfigFormEntry[]
     */
    protected function fromArrayToObjectCollection(array $entries)
    {
        $configFormEntries = array();

        foreach ($entries as $name => $entry) {
            if (!is_array($entry)) {
                $entry = ["type" => $entry];
            }

            if (!isset($entry["type"])) {
                continue;
            }

            $configFormEntry = new ConfigFormEntry($name, $entry["type"]);
            $configFormEntry
                ->setRequired(isset($entry["required"]) ? (bool) $entry["required"] : true)
            ;

            if (isset($entry["regex"])) {
                $configFormEntry->setRegex($entry["regex"]);
            }

            if (isset($entry["size"]) && is_array($entry["size"])) {
                $size = $entry["size"];

                if (isset($size["min"]) && is_int($size["min"])) {
                    $configFormEntry->setMinSize($size["min"]);
                }

                if (isset($size["max"]) && is_int($size["max"])) {
                    $configFormEntry->setMaxSize($size["max"]);
                }
            }

            $configFormEntries[] = $configFormEntry;
        }

        return $configFormEntries;
    }

    /**
     * @param $filePath
     * @return array
     */
    protected function getConfigFromYamlFile($filePath)
    {
        /**
         * the file don't exist or is not readable,
         * return the empty array
         */
        if (!is_file($filePath) || !is_readable($filePath)) {
            return [];
        }

        $rawContents = file_get_contents($filePath);

        try {
            $contents = Yaml::parse($rawContents);
        } catch (\Exception $e) {
            /**
             * An error while parsing the yaml ?
             * Return the empty array
             */
            return [];
        }

        /**
         * the config entry doesn't exist ?
         * Still return the empty array (:
         */
        if (!isset($contents["config"]) || !is_array($contents["config"])) {
            return [];
        }

        return $contents["config"];
    }
}
