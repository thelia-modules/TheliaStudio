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
use TheliaStudio\Parser\ConfigFormParser;
use TheliaStudio\Parser\Entity\Config;
use TheliaStudio\Parser\Entity\Form;
use TheliaStudio\Parser\Entity\Route;

/**
 * Class ConfigFormGenerator
 * @package TheliaStudio\Generator
 * @author Benjamin Perche <bperche9@gmail.com>
 */
class ConfigFormGenerator extends BaseGenerator
{
    use ConfigurationGeneratorTrait;
    use RoutingGeneratorTrait;

    const CONFIG_FORM_FILE = "config-form.yml";

    /**
     * @var ParserInterface|\TheliaSmarty\Template\SmartyParser
     */
    protected $parser;

    public function __construct(ParserInterface $parser)
    {
        $this->parser = $parser;
    }
    /**
     * @param  ModuleGenerateEvent $event
     * @return mixed
     */
    protected function generate(ModuleGenerateEvent $event)
    {
        $formConfig = $this->readConfigFormFile($event->getModulePath());

        if (empty($formConfig)) {
            return;
        }

        $this->parser->assign("form", $formConfig);
        $this->parser->assign("moduleCode", $event->getModuleCode());

        $this->generateClasses($event->getResourcesPath(), $event->getModulePath(), $event->getModuleCode());
        $this->generateTemplates($event->getResourcesPath(), $event->getModulePath(), $event->getModuleCode());
        $this->generateConfiguration($event->getModulePath(), $event->getModuleCode());
        $this->generateRouting($event->getModulePath(), $event->getModuleCode());
    }

    protected function generateClasses($resourcesPath, $modulePath, $moduleCode)
    {
        $templates = $this->findInPath($resourcesPath, "/__CONFIG_FORM__.*\.php$/");

        /** @var \SplFileInfo $template */
        foreach ($templates as $template) {
            $fetchedTemplate = $this->parser->fetch($template->getRealPath());

            $relativePath = str_replace($resourcesPath, '', $template->getRealPath());
            $relativePath = str_replace("__CONFIG_FORM__", $moduleCode, $relativePath);
            $relativePath = str_replace("FIX", '', $relativePath);

            $fullPath = $modulePath.$relativePath;

            $this->writeFile($fullPath, $fetchedTemplate, false === strpos($template->getFilename(), "FIX"), true);
        }
    }

    protected function generateTemplates($resourcesPath, $modulePath, $moduleCode)
    {
        $templates = $this->findInPath($resourcesPath, "/__CONFIG_FORM__.*\.html/");

        $previousLeft = $this->parser->left_delimiter;
        $previousRight = $this->parser->right_delimiter;

        $this->parser->left_delimiter = '[{';
        $this->parser->right_delimiter = '}]';

        /** @var \SplFileInfo $template */
        foreach ($templates as $template) {
            $fetchedTemplate = $this->parser->fetch($template->getRealPath());

            $relativePath = str_replace($resourcesPath, '', $template->getRealPath());
            $relativePath = str_replace("__CONFIG_FORM__", strtolower($moduleCode), $relativePath);

            $fullPath = $modulePath.$relativePath;

            $this->writeFile($fullPath, $fetchedTemplate, false, true);
        }

        $this->parser->left_delimiter = $previousLeft;
        $this->parser->right_delimiter = $previousRight;
    }

    /**
     * @param $modulePath
     * @throws \Exception
     */
    protected function generateConfiguration($modulePath, $moduleCode)
    {
        /** @var Config $config */
        list($xml, $configPath, $config) = $this->parseConfigXml($modulePath);

        $newConfig = $this->generateConfig($moduleCode);
        $config->mergeConfig($newConfig);

        $this->initializeConfig($xml);
        $this->addForms($xml, $config);

        // For now delete hooks node if it has no child
        if (!$xml->hooks->children()) {
            unset($xml->hooks);
        }

        if (!$xml->services->children()) {
            unset($xml->services);
        }

        $this->saveXml($xml, $configPath);
    }

    protected function generateConfig($moduleCode)
    {
        $config = new Config();

        $config->addForm(new Form(
            strtolower($moduleCode).".configuration",
            $moduleCode."\\Form\\".$moduleCode."ConfigForm")
        );

        return $config;
    }

    protected function generateRouting($modulePath, $moduleCode)
    {
        list($xml, $routingPath, $routes) = $this->parseRoutingXml($modulePath);

        $newRoutes = $this->generateRoutes($moduleCode);

        /**
         * Merge
         */
        $routing = array_diff_key($newRoutes, $routes);

        /**
         * Then write
         */
        $this->addRoutesToXml($routing, $xml);
        $this->saveXml($xml, $routingPath);
    }

    protected function generateRoutes($moduleCode)
    {
        $routes = array();
        $lowModuleCode = strtolower($moduleCode);

        $routes[$lowModuleCode.".configuration.default"] = new Route(
            $lowModuleCode.".configuration.default",
            '/admin/module/'.$moduleCode,
            'get',
            [
                "_controller" => $moduleCode.":".$moduleCode."Config:default",
            ]
        );

        $routes[$lowModuleCode.".configuration.save"] = new Route(
            $lowModuleCode.".configuration.save",
            '/admin/module/'.$moduleCode,
            'post',
            [
                "_controller" => $moduleCode.":".$moduleCode."Config:save",
            ]
        );

        return $routes;
    }

    protected function readConfigFormFile($modulePath)
    {
        $fullPath = $modulePath."Config".DS.static::CONFIG_FORM_FILE;

        $parser = new ConfigFormParser();

        return $parser->loadFromYaml($fullPath);
    }

    /**
     * @return string
     *
     * Get the generator name
     */
    protected function getName()
    {
        return "config-form";
    }
}
