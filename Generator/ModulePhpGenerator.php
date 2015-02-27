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

        $this->processModule($templates, $event->getResourcesPath(), $event->getModulePath(), $event->getModuleCode());
    }

    /**
     * @param  \SplFileInfo[]   $templates
     * @param $resourcesPath
     * @param $moduleCode
     * @throws \Exception
     * @throws \SmartyException
     */
    protected function processModule($templates, $resourcesPath, $modulePath, $moduleCode)
    {
        foreach ($templates as $template) {
            $fileName = str_replace("__MODULE__", $moduleCode, $template->getFilename());
            $fileName = str_replace("FIX", "", $fileName);

            $relativePath = str_replace($resourcesPath, "", $template->getPath().DS);
            $completeFilePath = $modulePath.$relativePath.DS.$fileName;

            $isFix = false !== strpos($template->getFilename(), "FIX");

            if ((!$isFix && !file_exists($completeFilePath)) || $isFix) {
                $fetchedTemplate = $this->parser->fetch($template->getRealPath());

                $this->writeFile($completeFilePath, $fetchedTemplate, true);
            }
        }
    }

    public function getName()
    {
        return "module_php";
    }
}
