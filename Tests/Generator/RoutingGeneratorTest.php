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
use TheliaStudio\Generator\RoutingGenerator;
use TheliaStudio\Parser\RoutingParser;
use TheliaStudio\Tests\GeneratorTestCase;

/**
 * Class RoutingGeneratorTest
 * @package TheliaStudio\Tests\Generator
 * @author Benjamin Perche <bperche9@gmail.com>
 */
class RoutingGeneratorTest extends GeneratorTestCase
{
    protected function setUp()
    {
        parent::setUp();

        $generator = new RoutingGenerator();
        $generator->doGenerate($this->event);
    }

    public function testInsertForms()
    {
        $xml = new SimpleXMLElement(file_get_contents($this->getStreamPath("Config/routing.xml")));
        $parser = new RoutingParser();
        $generatedRoutes = array_keys($parser->parseRoutes($xml));

        $routes = [
            "theliastudiotestmodule.example_table.list",
            "theliastudiotestmodule.example_table.create",
            "theliastudiotestmodule.example_table.view",
            "theliastudiotestmodule.example_table.edit",
            "theliastudiotestmodule.example_table.delete",
            "theliastudiotestmodule.example_table.update_position",
            "theliastudiotestmodule.example_table.toggle_visibility",
        ];

        sort($routes);
        sort($generatedRoutes);

        $this->assertEquals($routes, $generatedRoutes);
    }
}
