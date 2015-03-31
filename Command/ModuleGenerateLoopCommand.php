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

use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Thelia\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;
use Thelia\Core\HttpFoundation\Request;
use Thelia\Core\HttpFoundation\Session\Session;
use TheliaStudio\Events\ModuleGenerateEvent;
use TheliaStudio\Events\TheliaStudioEvents;

/**
 * Class ModuleGenerateLoopCommand
 * @package TheliaStudio\Command
 * @author Benjamin Perche <benjamin@thelia.net>
 */
class ModuleGenerateLoopCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName("module:generate:loop")
            ->setDescription("Generate module loops")
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
        ;
    }

    /**
     * @param  InputInterface  $input
     * @param  OutputInterface $output
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainer();

        $container->set("request", new Request());
        $container->get("request")->setSession(new Session(new MockArraySessionStorage()));

        $container->enterScope("request");

        try {
            $container->get("event_dispatcher")
                ->dispatch(
                    TheliaStudioEvents::LAUNCH_MODULE_BUILD,
                    new ModuleGenerateEvent($input->getArgument("moduleCode"), $input->getOption("tables"), ['php'], ["Loop"])
                );

            $output->renderBlock(array(
                '',
                'Everything has been generated successfully',
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
