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
use TheliaStudio\Parser\ConfigParser;
use TheliaStudio\Parser\Entity\Config;
use TheliaStudio\Parser\Entity\Form;
use TheliaStudio\Parser\Entity\Loop;
use TheliaStudio\Parser\Entity\Service;
use TheliaStudio\Parser\Entity\Tag;
use TheliaStudio\Parser\Table;


/**
 * Class ConfigurationGenerator
 * @package TheliaStudio\Generator
 * @author Benjamin Perche <bperche9@gmail.com>
 */
class ConfigurationGenerator extends BaseGenerator
{
    protected function initializeConfig(SimpleXMLElement $xml)
    {
        if (!$xml->forms) {
            $xml->addChild("forms");
        }

        if (!$xml->loops) {
            $xml->addChild("loops");
        }

        if (!$xml->services) {
            $xml->addChild("services");
        }

        if (!$xml->hooks) {
            $xml->addChild("hooks");
        }
    }


    protected function addForms(SimpleXMLElement $xml, Config $config)
    {
        foreach ($config->getForms() as $form) {
            if (!$xml->xpath("//config:forms/config:form[@name='{$form->getName()}']")) {
                $element = $xml->forms->addChild("form");
                $element->addAttribute("name", $form->getName());
                $element->addAttribute("class", $form->getClass());
            }
        }

    }

    protected function addLoops(SimpleXMLElement $xml, Config $config)
    {
        foreach ($config->getLoops() as $loop) {
            if (!$xml->xpath("//config:loops/config:loop[@name='{$loop->getName()}']")) {
                $element = $xml->loops->addChild("loop");
                $element->addAttribute("name", $loop->getName());
                $element->addAttribute("class", $loop->getClass());
            }
        }
    }

    protected function addServices(SimpleXMLElement $xml, Config $config)
    {
        foreach ($config->getServices() as $service) {
            if (!$xml->xpath("//config:services/config:service[@id='{$service->getId()}']")) {
                $element = $xml->services->addChild("service");
                $element->addAttribute("id", $service->getId());
                $element->addAttribute("class", $service->getClass());

                if ($service->getScope()) {
                    $element->addAttribute("scope", $service->getScope());
                }

                foreach ($service->getTags() as $tag) {
                    $tagXml = $element->addChild("tag");

                    foreach ($tag->getParameters() as $name => $parameter) {
                        $tagXml->addAttribute($name, $parameter);
                    }
                }
            }
        }
    }


    /**
     * @param Table[] $tables
     */
    protected function generateConfiguration(array $tables, $moduleCode)
    {
        $config = new Config();

        foreach ($tables as $table) {
            $config->addLoop(new Loop(
                $table->getLoopType(),
                $moduleCode . "\\Loop\\" . $table->getTableName()
            ));

            $config->addForm(new Form(
                $table->getRawTableName() . "_create",
                $moduleCode . "\\Form\\" . $table->getTableName() . "CreateForm"
            ));

            $config->addForm(new Form(
                $table->getRawTableName() . "_update",
                $moduleCode . "\\Form\\" . $table->getTableName() . "UpdateForm"
            ));

            $service = new Service(
                "action." . $table->getRawTableName() . "_table",
                $moduleCode . "\\Action\\" . $table->getTableName() . "Action"
            );

            $service->addTag(new Tag("kernel.event_subscriber"));
            $config->addService($service);
        }

        return $config;
    }


    /**
     * @param Table[] $tables
     * @param $modulePath
     */
    protected function processConfiguration(array $tables, $modulePath)
    {
        /**
         * Get current configuration
         */
        $configData = @file_get_contents($configPath = $modulePath . "Config" . DS . "config.xml");

        if (false === $configData) {
            throw new \Exception("missing file 'config.xml'");
        }

        $configParser = new ConfigParser();
        $xml = new SimpleXMLElement($configData);

        /** @var Config $config */
        $config = $configParser->parseXml($xml);

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
            unset ($xml->hooks);
        }

        $this->saveXml($xml, $configPath);
    }

    public function generate(ModuleGenerateEvent $event)
    {
        $this->processConfiguration($event->getEntities(), $event->getModulePath());
    }
}
