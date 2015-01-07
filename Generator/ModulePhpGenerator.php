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

use Thelia\Core\Template\ParserInterface;
use TheliaStudio\Events\ModuleGenerateEvent;

/**
 * Class ModulePhpGenerator
 * @package TheliaStudio\Generator
 * @author Benjamin Perche <bperche9@gmail.com>
 */
class ModulePhpGenerator extends BaseGenerator
{
    /**
     * @var ParserInterface|\TheliaSmarty\Template\SmartyParser
     */
    protected $parser;

    public function __construct(ParserInterface $parser)
    {
        $this->parser = $parser;
    }

    public function generate(ModuleGenerateEvent $event)
    {
        $templates = $this->findInPath($event->getResourcesPath(), "/__MODULE__.*\.php$/");

        $this->parser->assign("moduleCode", $event->getModuleCode());
        $this->parser->assign("tables", $event->getEntities());

        $this->processModule($templates, $event->getResourcesPath(), $event->getModuleCode());
    }

    /**
     * @param \SplFileInfo[] $templates
     * @param $resourcesPath
     * @param $moduleCode
     * @throws \Exception
     * @throws \SmartyException
     */
    protected function processModule($templates, $resourcesPath, $moduleCode)
    {
        foreach ($templates as $template) {
            $fileName = str_replace("__MODULE__", $moduleCode, $template->getFilename());

            $relativePath = str_replace($resourcesPath, "", $template->getPath() . DS);
            $completeFilePath = THELIA_MODULE_DIR . $moduleCode . DS . $relativePath . DS  . $fileName;

            $fetchedTemplate = $this->parser->fetch($template->getRealPath());

            $this->writeFile($completeFilePath, $fetchedTemplate, true);
        }
    }
}
