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
use TheliaStudio\Parser\Entity\Table;

/**
 * Class PhpGenerator
 * @package TheliaStudio\Generator
 * @author Benjamin Perche <bperche9@gmail.com>
 */
class PhpGenerator extends BaseGenerator
{
    /**
     * @var ParserInterface|\TheliaSmarty\Template\SmartyParser
     */
    protected $parser;

    public function __construct(ParserInterface $parser)
    {
        $this->parser = $parser;
    }

    /**
     * @param Table          $table
     * @param \SplFileInfo[] $templates
     * @param $resourcesPath
     * @param $moduleCode
     */
    protected function processPhp(Table $table, $templates, $resourcesPath, $moduleCode, $modulePath)
    {
        $this->parser->assign("table", $table);

        foreach ($templates as $template) {
            $fileName = str_replace("__TABLE__", $table->getTableName(), $template->getFilename());
            $fileName = str_replace("FIX", "", $fileName);

            $relativePath = str_replace($resourcesPath, "", $template->getPath().DS);
            $completeFilePath = $modulePath.DS.$relativePath.DS.$fileName;

            $isFix = false !== strpos($template->getFilename(), "FIX");
            $isI18n = false !== strpos($template->getFilename(), "I18n");

            if ((($isFix && !file_exists($completeFilePath)) || !$isFix) && ( ($isI18n && $table->hasI18nBehavior()) || !$isI18n)) {
                $fetchedTemplate = $this->parser->fetch($template->getRealPath());

                $this->writeFile($completeFilePath, $fetchedTemplate, true, true);
            }
        }
    }

    public function generate(ModuleGenerateEvent $event)
    {
        $templates = $this->findInPath($event->getResourcesPath(), "/__TABLE__.*\.php$/");

        $this->parser->assign("moduleCode", $event->getModuleCode());
        $this->parser->assign("tables", $event->getEntities());

        foreach ($event->getEntities() as $entity) {
            $this->processPhp(
                $entity,
                $templates,
                $event->getResourcesPath(),
                $event->getModuleCode(),
                $event->getModulePath()
            );
        }
    }

    public function getName()
    {
        return "php";
    }
}
