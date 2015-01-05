<?php

namespace TheliaStudio\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\NullSessionHandler;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Thelia\Command\ContainerAwareCommand;
use Thelia\Core\HttpFoundation\Request;
use Thelia\Core\HttpFoundation\Session\Session;
use TheliaStudio\Events\ModuleGenerateEvent;
use TheliaStudio\Events\TheliaStudioEvents;

/**
 * Class ModuleGenerateEverythingCommand
 * @package TheliaStudio\Command
 * @author Benjamin Perche <bperche9@gmail.com>
 */
class ModuleGenerateEverythingCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName("module:generate:everything")
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
        ;
    }

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
                    new ModuleGenerateEvent($input->getArgument("moduleCode"), $input->getOption("tables"))
                );
        } catch (\Exception $e) {
            $output->writeln($e->getMessage());
        }
    }
}
