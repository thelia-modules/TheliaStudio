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

use TheliaStudio\Parser\RuleReader;

/**
 * Class RuleReaderTest
 * @package TheliaStudio\Tests
 * @author Benjamin Perche <bperche9@gmail.com>
 */
class RuleReaderTest extends \PHPUnit_Framework_TestCase
{
    public function testReadValidRule()
    {
        $reader = new RuleReader();
        $rule = $reader->readRules(__DIR__ . DS . "fixtures" . DS . "valid.rule");

        $this->assertEquals("thelia.sql", $rule->getSource());

        $collection = $rule->getRuleCollection();

        $this->assertCount(2, $collection);
        $this->assertEquals(
            [["/DROP TABLE[^;]+;.*\n*/",""], ["/CREATE TABLE/", "CREATE TABLE IF NOT EXISTS"]],
            $collection
        );
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testThrowExceptionIfNoFileKey()
    {
        $reader = new RuleReader();
        $reader->readRules(__DIR__ . DS . "fixtures" . DS . "nofile.rule");
    }

    public function testIsEmptyIfTheJsonIsNotValid()
    {
        $reader = new RuleReader();
        $rule = $reader->readRules(__DIR__ . DS . "fixtures" . DS . "broken.rule");

        $this->assertEmpty($rule->getSource());
        $this->assertEmpty($rule->getRuleCollection());
    }
}
