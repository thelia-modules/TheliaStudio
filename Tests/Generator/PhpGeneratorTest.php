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

use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Thelia\Core\HttpFoundation\Request;
use Thelia\Core\HttpFoundation\Session\Session;
use Thelia\Core\Translation\Translator;
use TheliaStudio\Generator\ModulePhpGenerator;
use TheliaStudio\Generator\PhpGenerator;
use TheliaStudio\Tests\GeneratorTestCase;

/**
 * Class PhpGeneratorTest
 * @package TheliaStudio\Tests\Generator
 * @author Benjamin Perche <bperche9@gmail.com>
 */
class PhpGeneratorTest extends GeneratorTestCase
{
    protected function setUp()
    {
        parent::setUp();

        $generator = new PhpGenerator($this->getSmarty());
        $generator->doGenerate($this->event);
    }

    public function testAction()
    {
        $this->loadClassFromVfs("Action/ExampleTableAction");

        $reflection = new \ReflectionClass("TheliaStudioTestModule\\Action\\ExampleTableAction");
        // Test mother classes
        $this->assertTrue(
            $reflection->isSubclassOf("TheliaStudioTestModule\\Action\\Base\\ExampleTableAction")
        );
        $this->assertTrue(
            $reflection->implementsInterface("Symfony\\Component\\EventDispatcher\\EventSubscriberInterface")
        );

        $this->assertTrue($reflection->hasMethod("create"));
        $this->assertTrue($reflection->hasMethod("update"));
        $this->assertTrue($reflection->hasMethod("delete"));
        $this->assertTrue($reflection->hasMethod("updatePosition"));
        $this->assertTrue($reflection->hasMethod("toggleVisibility"));
        $this->assertTrue($reflection->hasMethod("createOrUpdate"));
        $this->assertTrue($reflection->hasMethod("beforeCreateFormBuild"));
        $this->assertTrue($reflection->hasMethod("beforeUpdateFormBuild"));
        $this->assertTrue($reflection->hasMethod("afterCreateFormBuild"));
        $this->assertTrue($reflection->hasMethod("afterUpdateFormBuild"));
    }

    public function testController()
    {
        $this->loadClassFromVfs("Controller/ExampleTableController");

        $reflection = new \ReflectionClass("TheliaStudioTestModule\\Controller\\ExampleTableController");
        // Test mother classes
        $this->assertTrue(
            $reflection->isSubclassOf("TheliaStudioTestModule\\Controller\\Base\\ExampleTableController")
        );
        $this->assertTrue(
            $reflection->isSubclassOf("Thelia\\Controller\\Admin\\AbstractCrudController")
        );
    }

    public function testEvent()
    {
        $this->loadClassFromVfs("Event/ExampleTableEvent");

        $reflection = new \ReflectionClass("TheliaStudioTestModule\\Event\\ExampleTableEvent");
        // Test mother classes
        $this->assertTrue(
            $reflection->isSubclassOf("TheliaStudioTestModule\\Event\\Base\\ExampleTableEvent")
        );
        $this->assertTrue(
            $reflection->isSubclassOf("Thelia\\Core\\Event\\ActionEvent")
        );

        $proprieties = array(
            "id",
            "visible",
            "position",
            "exampleTable",
            "title",
            "locale",
            "description",
            "chapo",
            "postscriptum",
        );

        foreach ($proprieties as $propriety) {
            $this->assertTrue($reflection->hasProperty($propriety), "The class doesn't have the propriety $propriety");
            $this->assertTrue(
                $reflection->hasMethod(
                    "get".Container::camelize($propriety)
                ),
                "The class doesn't have the method 'get".Container::camelize($propriety)."'"
            );
            $this->assertTrue(
                $reflection->hasMethod(
                    "set".Container::camelize($propriety)
                ),
                "The class doesn't have the method 'get".Container::camelize($propriety)."'"
            );
        }
    }

    public function testEvents()
    {
        $this->loadClassFromVfs("Event/ExampleTableEvents");

        $reflection = new \ReflectionClass("TheliaStudioTestModule\\Event\\ExampleTableEvents");
        // Test mother class
        $this->assertTrue(
            $reflection->isSubclassOf("TheliaStudioTestModule\\Event\\Base\\ExampleTableEvents")
        );

        $this->assertTrue($reflection->hasConstant("CREATE"));
        $this->assertTrue($reflection->hasConstant("UPDATE"));
        $this->assertTrue($reflection->hasConstant("DELETE"));
        $this->assertTrue($reflection->hasConstant("UPDATE_POSITION"));
        $this->assertTrue($reflection->hasConstant("TOGGLE_VISIBILITY"));
    }

    public function testLoop()
    {
        $this->loadClassFromVfs("Loop/ExampleTable");

        $reflection = new \ReflectionClass("TheliaStudioTestModule\\Loop\\ExampleTable");
        $this->assertTrue(
            $reflection->isSubclassOf("TheliaStudioTestModule\\Loop\\Base\\ExampleTable")
        );
        $this->assertTrue(
            $reflection->isSubclassOf("Thelia\\Core\\Template\\Element\\BaseI18nLoop")
        );
        $this->assertTrue(
            $reflection->implementsInterface("Thelia\\Core\\Template\\Element\\PropelSearchLoopInterface")
        );

        $this->assertTrue($reflection->hasMethod("getArgDefinitions"));
        $this->assertTrue($reflection->hasMethod("parseResults"));
        $this->assertTrue($reflection->hasMethod("buildModelCriteria"));
        $this->assertTrue($reflection->hasMethod("addMoreResults"));
    }

    public function testCreateForm()
    {
        /**
         * Run the module class generator for the form
         */
        $generator = new ModulePhpGenerator($this->getSmarty());
        $generator->doGenerate($this->event);
        $this->loadClassFromVfs("TheliaStudioTestModule", false);

        $this->loadClassFromVfs("Form/ExampleTableCreateForm");

        $reflection = new \ReflectionClass("TheliaStudioTestModule\\Form\\ExampleTableCreateForm");
        $this->assertTrue(
            $reflection->isSubclassOf("TheliaStudioTestModule\\Form\\Base\\ExampleTableCreateForm")
        );
        $this->assertTrue(
            $reflection->isSubclassOf("Thelia\\Form\\BaseForm")
        );

        $this->assertTrue($reflection->hasConstant("FORM_NAME"));
        $this->assertEquals(
            "example_table_create",
            $reflection->getConstant("FORM_NAME")
        );

        /**
         * Mock the request to build the form
         */
        new Translator(new Container());
        $request = new Request();
        $session = new Session(new MockArraySessionStorage());
        $request->setSession($session);

        $baseForm = $reflection->newInstance($request);
        /** @var \Symfony\Component\Form\Form $form */
        $form = $baseForm->getForm();

        /**
         * Then test it
         */
        $this->assertTrue($form->has("locale"));
        $this->assertTrue($form->has("title"));
        $this->assertTrue($form->has("description"));
        $this->assertTrue($form->has("chapo"));
        $this->assertTrue($form->has("postscriptum"));
        $this->assertTrue($form->has("visible"));
        $this->assertFalse($form->has("position"));
        $this->assertFalse($form->has("id"));
    }

    public function testUpdateForm()
    {
        $this->loadClassFromVfs("Form/ExampleTableCreateForm");
        $this->loadClassFromVfs("Form/ExampleTableUpdateForm");

        $reflection = new \ReflectionClass("TheliaStudioTestModule\\Form\\ExampleTableUpdateForm");
        $this->assertTrue(
            $reflection->isSubclassOf("TheliaStudioTestModule\\Form\\Base\\ExampleTableUpdateForm")
        );
        $this->assertTrue(
            $reflection->isSubclassOf("TheliaStudioTestModule\\Form\\ExampleTableCreateForm")
        );

        $this->assertTrue($reflection->hasConstant("FORM_NAME"));
        $this->assertEquals(
            "example_table_update",
            $reflection->getConstant("FORM_NAME")
        );
    }

    public function testFormType()
    {
        $this->loadClassFromVfs("Form/Type/ExampleTableIdType");

        $reflection = new \ReflectionClass("TheliaStudioTestModule\\Form\\Type\\ExampleTableIdType");
        $this->assertTrue(
            $reflection->isSubclassOf("Thelia\\Core\\Form\\Type\\Field\\AbstractIdType")
        );

        $this->assertTrue($reflection->hasMethod("getQuery"));
        $this->assertTrue($reflection->hasMethod("getName"));
        $this->assertTrue($reflection->hasConstant("TYPE_NAME"));
        $this->assertEquals(
            "example_table_id",
            $reflection->getConstant("TYPE_NAME")
        );
    }
}
