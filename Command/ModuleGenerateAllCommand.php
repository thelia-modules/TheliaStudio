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

namespace TheliaStudio\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Thelia\Command\ContainerAwareCommand;
use Thelia\Core\HttpFoundation\Request;
use Thelia\Core\HttpFoundation\Session\Session;
use TheliaStudio\Events\ModuleGenerateEvent;
use TheliaStudio\Events\TheliaStudioEvents;

/**
 * Class ModuleGenerateAllCommand
 * @package TheliaStudio\Command
 * @author Benjamin Perche <bperche9@gmail.com>
 */
class ModuleGenerateAllCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName("module:generate:all")
            ->setDescription("Generate actions, model, forms, controllers, events, templates from schema.xml")
            ->addArgument(
                "moduleCode",
                InputArgument::REQUIRED,
                "The module code"
            )
            ->addOption(
                "tables",
                "t",
                InputArgument::OPTIONAL,
                "Only generate for those tables"
            )
            ->addOption(
                "generators",
                "g",
                InputArgument::OPTIONAL,
                "Only use those generators"
            )
            ->addOption(
                "directories",
                "d",
                InputArgument::OPTIONAL,
                "Only generate file for the directories"
            )
        ;
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainer();

        $container->set("request", new Request());
        $container->get("request")->setSession(new Session(new MockArraySessionStorage()));

        try {
            $container->get("event_dispatcher")
                ->dispatch(
                    TheliaStudioEvents::LAUNCH_MODULE_BUILD,
                    new ModuleGenerateEvent(
                        $input->getArgument("moduleCode"),
                        $input->getOption("tables"),
                        $input->getOption("generators"),
                        $input->getOption("directories")
                    )
                );

            $output->renderBlock(array(
                '',
                'The config form has been correcly generated',
                'Files available in your module directory',
                '',
            ), 'bg=green;fg=black');
        } catch (\Exception $e) {
            $outputArray = explode("\n", $e->getMessage());
            array_push($outputArray, '');
            array_unshift($outputArray, '');

            $output->renderBlock($outputArray, 'bg=red;fg=white');
        }
    }
}
