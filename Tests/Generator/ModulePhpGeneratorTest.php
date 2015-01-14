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

use org\bovigo\vfs\vfsStream;
use Propel\Runtime\Propel;
use TheliaStudio\Generator\ModulePhpGenerator;
use TheliaStudio\Tests\GeneratorTestCase;

/**
 * Class ModulePhpGeneratorTest
 * @package TheliaStudio\Tests\Generator
 * @author Benjamin Perche <bperche9@gmail.com>
 */
class ModulePhpGeneratorTest extends GeneratorTestCase
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

    public function testGeneratePostActivation()
    {
        // Touch a new "insert.sql" file and copy a "create.sql" file
        /** @var \org\bovigo\vfs\vfsStreamFile $file */
        $configDir = $this->stream->getChild("Config");
        $file = vfsStream::newFile("create.sql")->at($configDir);
        $file->setContent(file_get_contents(__DIR__."/../".static::TEST_MODULE_PATH."Config".DS."thelia.sql"));

        vfsStream::newFile("insert.sql")->at($configDir);

        // Then run the generator
        $generator = new ModulePhpGenerator($this->getSmarty());

        $generator->doGenerate($this->event);

        // Read the class
        include $this->getStreamPath("TheliaStudioTestModule.php");
        $reflection = new \ReflectionClass("TheliaStudioTestModule\\TheliaStudioTestModule");

        $this->assertTrue($reflection->hasConstant("MESSAGE_DOMAIN"));
        $this->assertEquals("theliastudiotestmodule", $reflection->getConstant("MESSAGE_DOMAIN"));
        $this->assertTrue($reflection->hasMethod("postActivation"));
        // get a method closure
        $method = $reflection->getMethod("postActivation");
        $closure = $method->getClosure($reflection->newInstance());

        // Test that the table doesn't exist
        /** @var \Propel\Runtime\DataFetcher\PDODataFetcher $stmt */
        $stmt = $this->con->query("SHOW TABLES LIKE 'example_table'");
        $this->assertEquals(0, $stmt->count());

        // Execute the method
        $closure($this->con);

        // Now it exists
        /** @var \Propel\Runtime\DataFetcher\PDODataFetcher $stmt */
        $stmt = $this->con->query("SHOW TABLES LIKE 'example_table'");
        $this->assertEquals(1, $stmt->count());
    }

    protected function tearDown()
    {
        parent::tearDown();

        $this->con->exec("SET FOREIGN_KEY_CHECKS = 1;");
        $this->con->rollback();
    }
}
