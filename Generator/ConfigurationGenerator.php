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

use Symfony\Component\DependencyInjection\SimpleXMLElement;
use TheliaStudio\Events\ModuleGenerateEvent;
use TheliaStudio\Parser\Entity\Config;
use TheliaStudio\Parser\Entity\Form;
use TheliaStudio\Parser\Entity\Loop;
use TheliaStudio\Parser\Entity\Service;
use TheliaStudio\Parser\Entity\Tag;

/**
 * Class ConfigurationGenerator
 * @package TheliaStudio\Generator
 * @author Benjamin Perche <bperche9@gmail.com>
 */
class ConfigurationGenerator extends BaseGenerator
{
    use ConfigurationGeneratorTrait;

    /**
     * @param \TheliaStudio\Parser\Entity\Table[] $tables
     */
    protected function generateConfiguration(array $tables, $moduleCode)
    {
        $config = new Config();

        foreach ($tables as $table) {
            $config->addLoop(new Loop(
                $table->getLoopType(),
                $moduleCode."\\Loop\\".$table->getTableName()
            ));

            $config->addForm(new Form(
                $table->getRawTableName()."_create",
                $moduleCode."\\Form\\".$table->getTableName()."CreateForm"
            ));

            $config->addForm(new Form(
                $table->getRawTableName()."_update",
                $moduleCode."\\Form\\".$table->getTableName()."UpdateForm"
            ));

            $service = new Service(
                "action.".$table->getRawTableName()."_table",
                $moduleCode."\\Action\\".$table->getTableName()."Action"
            );

            $service->addTag(new Tag("kernel.event_subscriber"));
            $config->addService($service);
        }

        return $config;
    }

    /**
     * @param \TheliaStudio\Parser\Entity\Table[] $tables
     * @param $modulePath
     */
    protected function processConfiguration(array $tables, $modulePath)
    {
        /** @var Config $config */
        list($xml, $configPath, $config) = $this->parseConfigXml($modulePath);

        /**
         * Get generated configuration
         */
        $generatedConfig = $this->generateConfiguration($tables, basename($modulePath));

        /**
         * Merge it
         */
        $config->mergeConfig($generatedConfig);

        /**
         * Write new configuration
         */
        $this->initializeConfig($xml);
        $this->writeNewConfig($config, $configPath, $xml);
    }

    protected function writeNewConfig(Config $config, $configPath, SimpleXMLElement $xml)
    {
        $this->addForms($xml, $config);
        $this->addLoops($xml, $config);
        $this->addServices($xml, $config);

        // For now delete hooks node if it has no child
        if (!$xml->hooks->children()) {
            unset($xml->hooks);
        }

        $this->saveXml($xml, $configPath);
    }

    public function generate(ModuleGenerateEvent $event)
    {
        $this->processConfiguration($event->getEntities(), $event->getModulePath());
    }

    public function getName()
    {
        return "config";
    }
}
