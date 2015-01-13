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

use Propel\Runtime\Propel;
use Thelia\Install\Database;
use TheliaStudio\Generator\RuleGenerator;
use TheliaStudio\Tests\GeneratorTestCase;

/**
 * Class RuleGeneratorTest
 * @package TheliaStudio\Tests\Generator
 * @author Benjamin Perche <bperche9@gmail.com>
 */
class RuleGeneratorTest extends GeneratorTestCase
{
    /**
     * @var \Propel\Runtime\Connection\ConnectionInterface
     */
    protected $con;

    protected function setUp()
    {
        parent::setUp();

        // Start a transaction to revert all the modification after the test
        $this->con = Propel::getConnection();
        $this->con->beginTransaction();

        // Drop the table to be sure we have a clean environment
        $this->con->exec("SET FOREIGN_KEY_CHECKS = 0;");
        $this->con->exec("DROP TABLE IF EXISTS `example_table`");
    }

    public function testCreateSqlRuleGeneration()
    {
        $generator = new RuleGenerator();
        $generator->doGenerate($this->event);

        // Test file generation
        $this->assertFileExists($createFile = $this->getStreamPath("Config/create.sql"));

        // Test that the table doesn't exist
        /** @var \Propel\Runtime\DataFetcher\PDODataFetcher $stmt */
        $stmt = $this->con->query("SHOW TABLES LIKE 'example_table'");
        $this->assertEquals(0, $stmt->count());

        // Test that the file has the correct behavior
        $db = new Database($this->con);
        $db->insertSql(null, [$createFile]);

        // Now it exists
        /** @var \Propel\Runtime\DataFetcher\PDODataFetcher $stmt */
        $stmt = $this->con->query("SHOW TABLES LIKE 'example_table'");
        $this->assertEquals(1, $stmt->count());

        // Insert a new value in the table
        $this->con->exec("INSERT INTO `example_table`(`visible`, `position`) VALUES(1, 42)");

        // Check that the value exists
        $stmt = $this->con->query("SELECT * FROM `example_table`");
        $this->assertEquals(1, $stmt->count());

        // Then test that it doesn't crash if we apply the script again
        $db = new Database($this->con);
        $db->insertSql(null, [$createFile]);

        // Check that the previous entry still exists ( proves that there is no DROP TABLE )
        $stmt = $this->con->query("SELECT * FROM `example_table`");
        $fetched = $stmt->fetch(\PDO::FETCH_ASSOC);

        $this->assertTrue(is_array($fetched));
        $this->assertArrayHasKey("position", $fetched);
        $this->assertArrayHasKey("visible", $fetched);

        $this->assertEquals("1", $fetched["visible"]);
        $this->assertEquals("42", $fetched["position"]);
    }

    protected function tearDown()
    {
        parent::tearDown();

        $this->con->exec("SET FOREIGN_KEY_CHECKS = 1;");
        $this->con->rollback();
    }
}
