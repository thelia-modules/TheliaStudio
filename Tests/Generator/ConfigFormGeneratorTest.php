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

namespace TheliaStudio\Tests\Generator;

use Symfony\Component\DependencyInjection\SimpleXMLElement;
use TheliaStudio\Generator\ConfigFormGenerator;
use TheliaStudio\Parser\ConfigParser;
use TheliaStudio\Parser\RoutingParser;
use TheliaStudio\Tests\GeneratorTestCase;

/**
 * Class ConfigFormGeneratorTest
 * @package TheliaStudio\Tests\Generator
 * @author Benjamin Perche <bperche9@gmail.com>
 */
class ConfigFormGeneratorTest extends GeneratorTestCase
{
    protected function setUp()
    {
        parent::setUp();

        $generator = new ConfigFormGenerator($this->getSmarty());
        $generator->doGenerate($this->event);
    }

    public function testRouting()
    {
        $xml = new SimpleXMLElement(file_get_contents($this->getStreamPath("Config/routing.xml")));
        $parser = new RoutingParser();
        $generatedRoutes = array_keys($parser->parseRoutes($xml));

        $routes = [
            "theliastudiotestmodule.configuration.default",
            "theliastudiotestmodule.configuration.save",
        ];

        sort($routes);
        sort($generatedRoutes);

        $this->assertEquals($routes, $generatedRoutes);
    }

    public function testInsertForms()
    {
        $xml = new SimpleXMLElement(file_get_contents($this->getStreamPath("Config/config.xml")));
        $parser = new ConfigParser();
        $config = $parser->parseXml($xml);

        $forms = $config->getForms();
        $this->assertCount(1, $forms);

        $form = array_pop($forms);
        $this->assertEquals(
            "theliastudiotestmodule.configuration",
            $form->getName()
        );

        $this->assertEquals(
            "TheliaStudioTestModule\\Form\\TheliaStudioTestModuleConfigForm",
            $form->getClass()
        );
    }

    public function testController()
    {
        $this->loadClassFromVfs("Controller/TheliaStudioTestModuleConfigController");

        $reflection = new \ReflectionClass("TheliaStudioTestModule\\Controller\\TheliaStudioTestModuleConfigController");
        // Test mother classes
        $this->assertTrue(
            $reflection->isSubclassOf("TheliaStudioTestModule\\Controller\\Base\\TheliaStudioTestModuleConfigController")
        );
        $this->assertTrue(
            $reflection->isSubclassOf("Thelia\\Controller\\Admin\\BaseAdminController")
        );

        $this->assertTrue($reflection->hasMethod("defaultAction"));
        $this->assertTrue($reflection->hasMethod("saveAction"));
    }

    public function testForm()
    {
        $this->loadClassFromVfs("Form/TheliaStudioTestModuleConfigForm");

        $reflection = new \ReflectionClass("TheliaStudioTestModule\\Form\\TheliaStudioTestModuleConfigForm");
        // Test mother classes
        $this->assertTrue(
            $reflection->isSubclassOf("TheliaStudioTestModule\\Form\\Base\\TheliaStudioTestModuleConfigForm")
        );
        $this->assertTrue(
            $reflection->isSubclassOf("Thelia\\Form\\BaseForm")
        );
    }
}
