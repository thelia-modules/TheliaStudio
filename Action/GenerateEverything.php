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

namespace TheliaStudio\Action;

use Symfony\Component\DependencyInjection\SimpleXMLElement;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Thelia\Core\Thelia;
use Thelia\Model\ConfigQuery;
use TheliaStudio\Events\ModuleGenerateEvent;
use TheliaStudio\Events\TheliaStudioEvents;
use TheliaStudio\Parser\Entity\Config;
use TheliaStudio\Parser\SchemaParser;
use TheliaStudio\Parser\Entity\Table;
use TheliaStudio\TheliaStudio;

/**
 * Class GenerateEverything
 * @package TheliaStudio\Action
 * @author Benjamin Perche <bperche9@gmail.com>
 *
 * This class does some coffee for you.
 */
class GenerateEverything implements EventSubscriberInterface
{
    protected $kernel;

    public function __construct(Thelia $kernel)
    {
        $this->kernel = $kernel;
    }

    /**
     * @param ModuleGenerateEvent $event
     *
     * This method self prepares the event before running the generators.
     *
     * Here is the build process:
     * 1. Launch propel to validate the schema and generate model && sql
     * 2. Find / prepare all the resources
     * 3. Compile the module related classes
     * 4. Compile the table related classes
     * 5. Compile the templates
     * 6. Copy raw files
     * 7. Apply rules
     * 8. Add everything in config.xml and routing.xml
     */
    public function launchBuild(ModuleGenerateEvent $event)
    {
        // Backup trim level and set it to 0
        $previousTrimLevel = ConfigQuery::read("html_output_trim_level");
        ConfigQuery::write("html_output_trim_level", 0);

        $moduleCode = $event->getModuleCode();
        $modulePath = THELIA_MODULE_DIR.$moduleCode.DS;

        $resourcesPath = ConfigQuery::read(TheliaStudio::RESOURCE_PATH_CONFIG_NAME).DS;

        if (!is_dir($resourcesPath) || !is_readable($resourcesPath)) {
            throw new FileNotFoundException(sprintf(
                "The resources directory %s doesn't exist",
                $resourcesPath
            ));
        }

        $e = null;

        try {
            $entities = $this->buildEntities($modulePath, $event->getTables());

            $event
                ->setModulePath($modulePath)
                ->setResourcesPath($resourcesPath)
                ->setEntities($entities)
                ->setKernel($this->kernel)
            ;

            $event->getDispatcher()->dispatch(TheliaStudioEvents::RUN_GENERATORS, $event);
        } catch (\Exception $e) {
        }

        // Restore trim level
        ConfigQuery::write("html_output_trim_level", $previousTrimLevel);

        if (null !== $e) {
            throw $e;
        }
    }
    /**
     * @param $modulePath
     * @return \TheliaStudio\Parser\Entity\Table[]
     */
    protected function buildEntities($modulePath, $whiteList)
    {
        $entities = array();
        $schemaFile = $modulePath."Config".DS."schema.xml";

        if (is_file($schemaFile) && is_readable($schemaFile)) {
            $xml = new SimpleXMLElement(file_get_contents($schemaFile));
            $parser = new SchemaParser();

            $entities = $parser->parseXml($xml, $whiteList);
        }

        return $entities;
    }

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * The array keys are event names and the value can be:
     *
     *  * The method name to call (priority defaults to 0)
     *  * An array composed of the method name to call and the priority
     *  * An array of arrays composed of the method names to call and respective
     *    priorities, or 0 if unset
     *
     * For instance:
     *
     *  * array('eventName' => 'methodName')
     *  * array('eventName' => array('methodName', $priority))
     *  * array('eventName' => array(array('methodName1', $priority), array('methodName2'))
     *
     * @return array The event names to listen to
     *
     * @api
     */
    public static function getSubscribedEvents()
    {
        return array(
            TheliaStudioEvents::LAUNCH_MODULE_BUILD => array("launchBuild", 128),
        );
    }
}
