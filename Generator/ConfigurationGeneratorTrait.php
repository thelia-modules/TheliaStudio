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
use TheliaStudio\Parser\ConfigParser;
use TheliaStudio\Parser\Entity\Argument;
use TheliaStudio\Parser\Entity\Config;

/**
 * Trait ConfigurationGeneratorTrait
 * @package TheliaStudio\Generator
 * @author Benjamin Perche <bperche9@gmail.com>
 */
trait ConfigurationGeneratorTrait
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

                foreach ($service->getArguments() as $argument) {
                    $serviceXml = $element->addChild("argument");

                    if (null !== $argument->getId()) {
                        $serviceXml->addAttribute("id", $argument->getId());
                    }

                    if (null !== $argument->getType() && Argument::TYPE_STRING !== $argument->getType()) {
                        $serviceXml->addAttribute("type", $argument->getType());
                    }

                    if (null !== $argument->getValue()) {
                        $serviceXml[0] = $argument->getValue();
                    }
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

    protected function parseConfigXml($modulePath)
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
        return [$xml, $configPath, $configParser->parseXml($xml)];
    }
}
