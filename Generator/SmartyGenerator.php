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
 * Class SmartyGenerator
 * @package TheliaStudio\Generator
 * @author Benjamin Perche <bperche9@gmail.com>
 */
class SmartyGenerator extends BaseGenerator
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
        $previousLeft = $this->parser->left_delimiter;
        $previousRight = $this->parser->right_delimiter;
        $this->parser->left_delimiter = '[{';
        $this->parser->right_delimiter = '}]';

        $templates = $this->findInPath($event->getResourcesPath(), "/__TABLE__.*\.html$/");

        $this->parser->assign("moduleCode", $event->getModuleCode());
        $this->parser->assign("tables", $event->getEntities());

        foreach ($event->getEntities() as $entity) {
            $this->processTemplate($entity, $templates, $event->getResourcesPath(), $event->getModuleCode());
        }

        $this->parser->left_delimiter = $previousLeft;
        $this->parser->right_delimiter = $previousRight;
    }

    /**
     * @param  \TheliaStudio\Parser\Entity\Table            $table
     * @param  \SplFileInfo[]   $templates
     * @param $resourcesPath
     * @param $moduleCode
     * @throws \Exception
     * @throws \SmartyException
     */
    protected function processTemplate(Table $table, $templates, $resourcesPath, $moduleCode)
    {
        $this->parser->assign("table", $table);

        foreach ($templates as $template) {
            $fetchedTemplate = $this->parser->fetch($template->getRealPath());
            $fileName = str_replace("__TABLE__", str_replace("_", "-", $table->getRawTableName()), $template->getFilename());

            $relativePath = str_replace($resourcesPath, "", $template->getPath() . DS);
            $completeFilePath = THELIA_MODULE_DIR . $moduleCode . DS . $relativePath . DS . $fileName;

            $this->writeFile($completeFilePath, $fetchedTemplate, false, true);
        }
    }

    public function getName()
    {
        return "smarty";
    }
}
