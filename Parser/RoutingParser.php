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

use Symfony\Component\DependencyInjection\SimpleXMLElement;
use TheliaStudio\Parser\Entity\Route;

/**
 * Class RoutingParser
 * @package TheliaStudio\Parser
 * @author Benjamin Perche <bperche9@gmail.com>
 */
class RoutingParser
{
    public function parseRoutes(SimpleXMLElement $xml)
    {
        static::registerNamespace($xml);

        $routes = array();

        /** @var SimpleXmlElement $routeXml */
        foreach ($xml->xpath("//routes:route") as $routeXml) {
            $route = new Route($routeXml->getAttributeAsPhp("id"), $routeXml->getAttributeAsPhp("path"));

            if ($methods = $routeXml->getAttributeAsPhp("methods")) {
                $route->setMethods($methods);
            }

            static::registerNamespace($routeXml);

            /** @var SimpleXmlElement $defaultXml */
            foreach ($routeXml->xpath(".//routes:default") as $defaultXml) {
                $route->addDefault($defaultXml->getAttributeAsPhp("key"), (string) $defaultXml);
            }

            foreach ($routeXml->xpath(".//routes:requirement") as $defaultXml) {
                $route->addRequirement($defaultXml->getAttributeAsPhp("key"), (string) $defaultXml);
            }

            $routes[$route->getId()] = $route;
        }

        return $routes;
    }

    public static function registerNamespace(SimpleXMLElement $xml)
    {
        $xml->registerXPathNamespace("routes", "http://symfony.com/schema/routing");
    }
}
