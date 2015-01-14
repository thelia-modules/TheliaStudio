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
use TheliaStudio\Generator\ConfigurationGenerator;
use TheliaStudio\Parser\ConfigParser;
use TheliaStudio\Tests\GeneratorTestCase;

/**
 * Class ConfigurationGeneratorTest
 * @package TheliaStudio\Tests\Generator
 * @author Benjamin Perche <bperche9@gmail.com>
 */
class ConfigurationGeneratorTest extends GeneratorTestCase
{
    protected function setUp()
    {
        parent::setUp();

        $generator = new ConfigurationGenerator();
        $generator->doGenerate($this->event);
    }

    public function testInsertForms()
    {
        $xml = new SimpleXMLElement(file_get_contents($this->getStreamPath("Config/config.xml")));
        ConfigParser::registerNamespace($xml);

        $forms = $xml->xpath("//config:forms/config:form");
        $this->assertCount(2, $forms);

        $firstForm = array_shift($forms);
        $values[$firstForm->getAttributeAsPhp("name")] = $firstForm->getAttributeAsPhp("class");

        $secondForm = array_shift($forms);
        $values[$secondForm->getAttributeAsPhp("name")] = $secondForm->getAttributeAsPhp("class");

        $this->assertCount(2, $values);
        ksort($values);

        $compareValues = [
            "example_table.create" => "TheliaStudioTestModule\\Form\\ExampleTableCreateForm",
            "example_table.update" => "TheliaStudioTestModule\\Form\\ExampleTableUpdateForm",
        ];

        $this->assertEquals($compareValues, $values);
    }

    public function testInsertLoop()
    {
        $xml = new SimpleXMLElement(file_get_contents($this->getStreamPath("Config/config.xml")));
        ConfigParser::registerNamespace($xml);

        $loops = $xml->xpath("//config:loops/config:loop");
        $this->assertCount(1, $loops);

        $this->assertEquals(
            "example-table",
            $loops[0]->getAttributeAsPhp("name")
        );

        $this->assertEquals(
            "TheliaStudioTestModule\\Loop\\ExampleTable",
            $loops[0]->getAttributeAsPhp("class")
        );
    }

    public function testInsertService()
    {
        $xml = new SimpleXMLElement(file_get_contents($this->getStreamPath("Config/config.xml")));
        ConfigParser::registerNamespace($xml);

        $services = $xml->xpath("//config:services/config:service");
        $this->assertCount(2, $services);

        // test action
        $action = $xml->xpath(
            "//config:services/config:service[@id=\"action.theliastudiotestmodule.example_table_table\"]"
        );
        $this->assertCount(1, $action);

        $action = $action[0];
        $this->assertEquals(
            "TheliaStudioTestModule\\Action\\ExampleTableAction",
            $action->getAttributeAsPhp("class")
        );

        ConfigParser::registerNamespace($action);
        $tag = $action->xpath(".//config:tag");
        $this->assertCount(1, $tag);
        $this->assertEquals("kernel.event_subscriber", $tag[0]->getAttributeAsPhp("name"));

        // test form type
        $type = $xml->xpath(
            "//config:services/config:service[@id=\"theliastudiotestmodule.form.type.example_table_id\"]"
        );
        $this->assertCount(1, $type);

        $type = $type[0];
        $this->assertEquals(
            "TheliaStudioTestModule\\Form\\Type\\ExampleTableIdType",
            $type->getAttributeAsPhp("class")
        );

        ConfigParser::registerNamespace($type);
        $tag = $type->xpath(".//config:tag");
        $this->assertCount(1, $tag);
        $this->assertEquals("thelia.form.type", $tag[0]->getAttributeAsPhp("name"));
    }
}
