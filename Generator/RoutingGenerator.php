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
use TheliaStudio\Parser\Entity\Route;
use TheliaStudio\Parser\Entity\Table;

/**
 * Class RoutingGenerator
 * @package TheliaStudio\Generator
 * @author Benjamin Perche <bperche9@gmail.com>
 */
class RoutingGenerator extends BaseGenerator
{
    use RoutingGeneratorTrait;

    public function generate(ModuleGenerateEvent $event)
    {
        $this->processRouting($event->getEntities(), $event->getModulePath(), $event->getModuleCode());
    }

    protected function processRouting(array $tables, $modulePath, $moduleCode)
    {
        list($xml, $routingPath, $routes) = $this->parseRoutingXml($modulePath);

        $newRoutes = $this->generateRouting($tables, $moduleCode);

        /**
         * Merge
         */
        $routing = array_diff_key($newRoutes, $routes);

        /**
         * Then write
         */
        $this->addRoutesToXml($routing, $xml);
        $this->saveXml($xml, $routingPath);
    }

    /**
     * @param  \TheliaStudio\Parser\Entity\Table[] $tables
     * @return array
     */
    public function generateRouting(array $tables, $moduleCode)
    {
        $routes = array();
        $lowerCode = strtolower($moduleCode);

        foreach ($tables as $table) {
            // List
            $routes[$lowerCode.".".$table->getRawTableName().".list"] = new Route(
                $lowerCode.".".$table->getRawTableName().".list",
                "/admin/module/".$moduleCode."/".$table->getRawTableName(),
                "get",
                [
                    "_controller" => $moduleCode.":".$table->getTableName().":default"
                ]
            );

            // Create
            $routes[$lowerCode.".".$table->getRawTableName().".create"] = new Route(
                $lowerCode.".".$table->getRawTableName().".create",
                "/admin/module/".$moduleCode."/".$table->getRawTableName(),
                "post",
                [
                    "_controller" => $moduleCode.":".$table->getTableName().":create"
                ]
            );

            // View
            $routes[$lowerCode.".".$table->getRawTableName().".view"] = new Route(
                $lowerCode.".".$table->getRawTableName().".view",
                "/admin/module/".$moduleCode."/".$table->getRawTableName()."/edit",
                "get",
                [
                    "_controller" => $moduleCode.":".$table->getTableName().":update"
                ]
            );

            // Edit
            $routes[$lowerCode.".".$table->getRawTableName().".edit"] = new Route(
                $lowerCode.".".$table->getRawTableName().".edit",
                "/admin/module/".$moduleCode."/".$table->getRawTableName()."/edit",
                "post",
                [
                    "_controller" => $moduleCode.":".$table->getTableName().":processUpdate"
                ]
            );

            // Delete
            $routes[$lowerCode.".".$table->getRawTableName().".delete"] = new Route(
                $lowerCode.".".$table->getRawTableName().".delete",
                "/admin/module/".$moduleCode."/".$table->getRawTableName()."/delete",
                "post",
                [
                    "_controller" => $moduleCode.":".$table->getTableName().":delete"
                ]
            );

            // Update position
            if ($table->hasPosition()) {
                $routes[$lowerCode.".".$table->getRawTableName().".update_position"] = new Route(
                    $lowerCode.".".$table->getRawTableName().".update_position",
                    "/admin/module/".$moduleCode."/".$table->getRawTableName()."/updatePosition",
                    "get",
                    [
                        "_controller" => $moduleCode.":".$table->getTableName().":updatePosition"
                    ]
                );
            }

            // Toggle visibility
            if ($table->hasVisible()) {
                $routes[$lowerCode.".".$table->getRawTableName().".toggle_visibility"] = new Route(
                    $lowerCode.".".$table->getRawTableName().".toggle_visibility",
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

    public function getName()
    {
        return "routing";
    }
}
