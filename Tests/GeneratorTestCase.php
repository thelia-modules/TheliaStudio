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

use org\bovigo\vfs\vfsStream;
use Symfony\Component\DependencyInjection\SimpleXMLElement;
use Thelia\Core\Template\ParserContext;
use Thelia\Core\Thelia;
use TheliaSmarty\Template\SmartyParser;
use TheliaStudio\Events\ModuleGenerateEvent;
use TheliaStudio\Parser\SchemaParser;

/**
 * Class GeneratorTestCase
 * @package TheliaStudio\Tests
 * @author Benjamin Perche <bperche9@gmail.com>
 */
class GeneratorTestCase extends  \PHPUnit_Framework_TestCase
{
    const TEST_MODULE_PATH = "fixtures/TheliaStudioTestModule/";

    /**
     * @var \org\bovigo\vfs\vfsStreamDirectory
     */
    protected $stream;

    /**
     * @var ModuleGenerateEvent
     */
    protected $event;

    /**
     * @var Thelia
     */
    protected $kernel;

    protected function setUp()
    {
        // Setup a virtual file system under thelia:// stream
        $this->stream = vfsStream::setup("thelia", 0777);

        // Copy the module fixtures
        vfsStream::copyFromFileSystem(__DIR__ . DS . str_replace("/", DS, static::TEST_MODULE_PATH), $this->stream);

        // Initialize the kernel
        $this->kernel = new Thelia("test", true);
        $this->kernel->boot();

        // Then create event
        $this->event = new ModuleGenerateEvent("TheliaStudioTestModule");
        $this->buildEvent();
    }

    protected function buildEvent()
    {
        $parser = new SchemaParser();
        $entities = $parser->parseXml(
            new SimpleXMLElement(
                file_get_contents(
                    __DIR__ . DS .  str_replace("/", DS, static::TEST_MODULE_PATH) . "Config" . DS . "schema.xml"
                )
            )
        );

        $this->event
            ->setKernel($this->kernel)
            ->setModulePath($this->getStreamPath(''))
            ->setEntities($entities)
            ->setResourcesPath(realpath(__DIR__ . DS . ".." . DS . "Resources")  . DS)
        ;
    }

    protected function getStreamPath($relativePath)
    {
        $path = vfsStream::url("thelia") . DS . $relativePath;

        return $path;
    }

    protected function getSmarty()
    {
        return new SmartyParser(
            $this->getMock("Thelia\Core\HttpFoundation\Request"),
            $this->getMock("Symfony\Component\EventDispatcher\EventDispatcher"),
            new ParserContext($this->getMock("Thelia\Core\HttpFoundation\Request"))
        );
    }

    protected function loadClassFromVfs($relativePath, $loadBase = true)
    {
        if (class_exists("TheliaStudioTestModule\\" . str_replace("/", "\\", $relativePath))) {
            return;
        }

        if ($loadBase) {
            $baseRelativePath = preg_replace("#^(([a-z]+/)*)([a-z]*)#i", "$1Base/$3", $relativePath);
            $this->loadClassFromVfs($baseRelativePath, false);
        }

        $classPath = $this->getStreamPath($relativePath . ".php");
        $this->assertFileExists($classPath);

        include $classPath;
    }
}
