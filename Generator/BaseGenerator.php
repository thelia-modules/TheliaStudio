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

namespace TheliaStudio\Generator;

use Symfony\Component\DependencyInjection\SimpleXMLElement;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Finder\Finder;
use TheliaStudio\Events\ModuleGenerateEvent;
use TheliaStudio\Events\TheliaStudioEvents;

/**
 * Class FileWriter
 * @package TheliaStudio\Generator
 * @author Benjamin Perche <bperche9@gmail.com>
 */
abstract class BaseGenerator implements EventSubscriberInterface
{
    protected static $eventPriority = 128;

    protected function findInPath($resourcesPath, $name)
    {
        return Finder::create()
            ->files()
            ->in($resourcesPath)
            ->name($name)
            ->exclude(["includes", "raw-copy"])
        ;
    }

    protected function saveXml(SimpleXMLElement $xml, $path)
    {
        $dom = new \DOMDocument("1.0");
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $dom->loadXML($xml->asXML());
        $this->writeFile($path, $dom->saveXML(), true, true);
    }

    protected function writeFile($path, $data, $force = false, $mkdir = false)
    {
        if ($mkdir) {
            @mkdir(dirname($path), 0777, true);
        }

        if ($force || !file_exists($path)) {
            @file_put_contents($path, $data);
        }
    }

    abstract public function generate(ModuleGenerateEvent $event);

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
        return [TheliaStudioEvents::RUN_GENERATORS => ["generate", static::$eventPriority]];
    }
}
