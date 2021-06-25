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

use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Finder\Finder;
use Thelia\Command\ModuleGenerateModelCommand;
use Thelia\Command\ModuleGenerateSqlCommand;
use Thelia\Core\Application;
use Thelia\Core\Thelia;
use TheliaStudio\Events\ModuleGenerateEvent;
use TheliaStudio\Output\NullOutput;

/**
 * Class PropelLauncherGenerator
 * @package TheliaStudio\Generator
 * @author Benjamin Perche <bperche9@gmail.com>
 */
class PropelLauncherGenerator extends BaseGenerator
{
    protected static $eventPriority = 1; // Be sure to come last

    public function generate(ModuleGenerateEvent $event)
    {
        $this->generateSql($event->getModuleCode(), $event->getKernel());
        $this->generateModel($event->getModuleCode(), $event->getKernel());
        $this->removeMapFile($event->getModulePath());
    }

    protected function removeMapFile($modulePath)
    {
        $files = (new Finder())->files()->in($modulePath."Config")->name("*.map");

        /** @var \SplFileInfo $file */
        foreach ($files as $file) {
            unlink($file->getRealPath()); // You're useless ! (:
        }
    }

    protected function generateSql($moduleCode, Thelia $kernel)
    {
        /**
         * Generate sql
         */
        $moduleGenerateModel = new ModuleGenerateSqlCommand();
        $moduleGenerateModel->setApplication(new Application($kernel));

        $output = new NullOutput();

        $moduleGenerateModel->run(
            new ArrayInput(array(
                "command" => $moduleGenerateModel->getName(),
                "name" => $moduleCode,
            )),
            $output
        );
    }

    protected function generateModel($moduleCode, Thelia $kernel)
    {
        /**
         * Generate model
         */
        $moduleGenerateModel = new ModuleGenerateModelCommand();
        $moduleGenerateModel->setApplication(new Application($kernel));

        $output = new NullOutput();

        $code = $moduleGenerateModel->run(
            new ArrayInput(array(
                "command" => $moduleGenerateModel->getName(),
                "name" => $moduleCode,
            )),
            $output
        );

        if ($code) {
            throw new \InvalidArgumentException(
                "There is a problem with the schema.xml file, please try to run propel to see what happened"
            );
        }
    }

    public function getName()
    {
        return "propel";
    }
}
