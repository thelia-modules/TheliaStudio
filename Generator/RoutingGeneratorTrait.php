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
use TheliaStudio\Parser\RoutingParser;

/**
 * Trait RoutingGeneratorTrait
 * @package TheliaStudio\Generator
 * @author Benjamin Perche <bperche9@gmail.com>
 */
trait RoutingGeneratorTrait
{
    protected function parseRoutingXml($modulePath)
    {
        $routingData = @file_get_contents($routingPath = $modulePath . "Config" . DS . "routing.xml");

        if (false === $routingData) {
            $routingData = "<?xml version=\"1.0\" encoding=\"UTF-8\"?><routes xmlns=\"http://symfony.com/schema/routing\" xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xsi:schemaLocation=\"http://symfony.com/schema/routing http://symfony.com/schema/routing/routing-1.0.xsd\"></routes>";
        }

        $routingParser = new RoutingParser();
        $xml = new SimpleXMLElement($routingData);

        /** @var \TheliaStudio\Parser\Entity\Route[] $routes */
        $routes = $routingParser->parseRoutes($xml);

        return [$xml, $routingPath, $routes];
    }

    /**
     * @param \TheliaStudio\Parser\Entity\Route[] $routes
     * @param SimpleXMLElement $xml
     */
    protected function addRoutesToXml(array $routes, SimpleXMLElement $xml)
    {
        foreach ($routes as $route) {
            /** @var SimpleXmlElement $element */
            $element = $xml->addChild("route");

            $element->addAttribute("id", $route->getId());
            $element->addAttribute("path", $route->getPath());

            if ($route->getMethods()) {
                $element->addAttribute("methods", $route->getMethods());
            }

            foreach ($route->getDefaults() as $key => $value) {
                $default = $element->addChild("default", $value);
                $default->addAttribute("key", $key);
            }

            foreach ($route->getRequirements() as $key => $value) {
                $requirement = $element->addChild("requirement", $value);
                $requirement->addAttribute("key", $key);
            }
        }
    }
}
