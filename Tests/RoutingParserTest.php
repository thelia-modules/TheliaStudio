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

namespace TheliaStudio\Tests;

use Symfony\Component\DependencyInjection\SimpleXMLElement;
use TheliaStudio\Parser\RoutingParser;

/**
 * Class RoutingParserTest
 * @package TheliaStudio\Tests
 * @author Benjamin Perche <bperche9@gmail.com>
 */
class RoutingParserTest extends \PHPUnit_Framework_TestCase
{
    public function testConvertsSimpleXmlElementToObject()
    {
        $xml = new SimpleXMLElement(file_get_contents(__DIR__.DS."fixtures".DS."routing.xml"));

        $parser = new RoutingParser();
        $routes = $parser->parseRoutes($xml);

        $this->assertTrue(is_array($routes));
        $this->assertCount(2, $routes);

        $this->assertArrayHasKey("foo", $routes);
        $this->assertArrayHasKey("bar", $routes);

        /** @var \TheliaStudio\Parser\Entity\Route $fooRoute */
        $fooRoute = $routes["foo"];

        /** @var \TheliaStudio\Parser\Entity\Route $barRoute */
        $barRoute = $routes["bar"];

        $this->assertInstanceOf("TheliaStudio\\Parser\\Entity\\Route", $fooRoute);
        $this->assertInstanceOf("TheliaStudio\\Parser\\Entity\\Route", $barRoute);

        $this->assertEquals("foo", $fooRoute->getId());
        $this->assertEquals("/a/path", $fooRoute->getPath());
        $this->assertNull($fooRoute->getMethods());
        $this->assertEmpty($fooRoute->getDefaults());
        $this->assertEmpty($fooRoute->getRequirements());

        $this->assertEquals("bar", $barRoute->getId());
        $this->assertEquals("/another/path/{baz}", $barRoute->getPath());
        $this->assertEquals("get", $barRoute->getMethods());
        $this->assertEquals(["_controller" => "A:B:C"], $barRoute->getDefaults());
        $this->assertEquals(["baz" => "\\d+"], $barRoute->getRequirements());
    }
}
