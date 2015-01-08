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
use TheliaStudio\Parser\Entity\Route;
use TheliaStudio\Parser\RoutingParser;
use TheliaStudio\Parser\Table;

/**
 * Class RoutingGenerator
 * @package TheliaStudio\Generator
 * @author Benjamin Perche <bperche9@gmail.com>
 */
class RoutingGenerator extends BaseGenerator
{
    public function generate(ModuleGenerateEvent $event)
    {
        $this->processRouting($event->getEntities(), $event->getModulePath());
    }

    protected function processRouting(array $tables, $modulePath)
    {
        $routingData = @file_get_contents($routingPath = $modulePath."Config".DS."routing.xml");

        if (false === $routingData) {
            $routingData = "<?xml version=\"1.0\" encoding=\"UTF-8\"?><routes xmlns=\"http://symfony.com/schema/routing\" xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xsi:schemaLocation=\"http://symfony.com/schema/routing http://symfony.com/schema/routing/routing-1.0.xsd\"></routes>";
        }

        $routingParser = new RoutingParser();
        $xml = new SimpleXMLElement($routingData);

        /** @var \TheliaStudio\Parser\Entity\Route[] $routes */
        $routes = $routingParser->parseRoutes($xml);

        $newRoutes = $this->generateRouting($tables, basename($modulePath));

        /**
         * Merge
         */
        $routing = array_diff_key($newRoutes, $routes);

        /**
         * Then write
         */
        $this->writeRouting($routing, $routingPath, $xml);
    }

    /**
     * @param Route[]          $routes
     * @param $routingPath
     * @param SimpleXMLElement $xml
     */
    protected function writeRouting(array $routes, $routingPath, SimpleXMLElement $xml)
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

        $this->saveXml($xml, $routingPath);
    }
    /**
     * @param  Table[] $tables
     * @return array
     */
    public function generateRouting(array $tables, $moduleCode)
    {
        $routes = array();

        foreach ($tables as $table) {
            // List
            $routes[$table->getRawTableName().".list"] = new Route(
                $table->getRawTableName().".list",
                "/admin/module/".$moduleCode."/".$table->getRawTableName(),
                "get",
                [
                    "_controller" => $moduleCode.":".$table->getTableName().":default"
                ]
            );

            // Create
            $routes[$table->getRawTableName().".create"] = new Route(
                $table->getRawTableName().".create",
                "/admin/module/".$moduleCode."/".$table->getRawTableName(),
                "post",
                [
                    "_controller" => $moduleCode.":".$table->getTableName().":create"
                ]
            );

            // View
            $routes[$table->getRawTableName().".view"] = new Route(
                $table->getRawTableName().".view",
                "/admin/module/".$moduleCode."/".$table->getRawTableName()."/edit",
                "get",
                [
                    "_controller" => $moduleCode.":".$table->getTableName().":update"
                ]
            );

            // Edit
            $routes[$table->getRawTableName().".edit"] = new Route(
                $table->getRawTableName().".edit",
                "/admin/module/".$moduleCode."/".$table->getRawTableName()."/edit",
                "post",
                [
                    "_controller" => $moduleCode.":".$table->getTableName().":processUpdate"
                ]
            );

            // Delete
            $routes[$table->getRawTableName().".delete"] = new Route(
                $table->getRawTableName().".delete",
                "/admin/module/".$moduleCode."/".$table->getRawTableName(),
                "post",
                [
                    "_controller" => $moduleCode.":".$table->getTableName().":delete"
                ]
            );

            // Update position
            if ($table->hasPosition()) {
                $routes[$table->getRawTableName().".update_position"] = new Route(
                    $table->getRawTableName().".update_position",
                    "/admin/module/".$moduleCode."/".$table->getRawTableName()."/updatePosition",
                    "get",
                    [
                        "_controller" => $moduleCode.":".$table->getTableName().":updatePosition"
                    ]
                );
            }

            // Toggle visibility
            if ($table->hasVisible()) {
                $routes[$table->getRawTableName().".toggle_visibility"] = new Route(
                    $table->getRawTableName().".toggle_visibility",
                    "/admin/module/".$moduleCode."/".$table->getRawTableName()."/toggleVisibility",
                    "get",
                    [
                        "_controller" => $moduleCode.":".$table->getTableName().":setToggleVisibility"
                    ]
                );
            }
        }

        return $routes;
    }
}
