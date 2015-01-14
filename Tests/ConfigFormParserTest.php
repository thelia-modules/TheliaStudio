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

use TheliaStudio\Parser\ConfigFormParser;

/**
 * Class ConfigFormParserTest
 * @package TheliaStudio\Tests
 * @author Benjamin Perche <bperche9@gmail.com>
 */
class ConfigFormParserTest extends \PHPUnit_Framework_TestCase
{
    public function testParserFixture()
    {
        $parser = new ConfigFormParser();
        $config = $parser->loadFromYaml(__DIR__."/fixtures/config-form.yml");

        $this->assertCount(3, $config);

        $first = $config[0];
        $second = $config[1];
        $third = $config[2];

        $this->assertEquals("var_name", $first->getName());
        $this->assertEquals("VarName", $first->getCamelizedName());
        $this->assertEquals("VAR_NAME", $first->getConstantName());
        $this->assertEquals("text", $first->getType());
        $this->assertTrue($first->isRequired());
        $this->assertFalse($first->hasSize());

        $this->assertEquals("var_name2", $second->getName());
        $this->assertEquals("integer", $second->getType());

        $this->assertEquals("var_name3", $third->getName());
        $this->assertEquals("text", $third->getType());
        $this->assertFalse($third->isRequired());
        $this->assertEquals(5, $third->getMinSize());
        $this->assertEquals(20, $third->getMaxSize());
        $this->assertTrue($third->hasMinSize());
        $this->assertTrue($third->hasMaxSize());
    }
}
