<?php

namespace TheliaStudio\Action;

use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\CS\Console\Application as PhpCsFixer;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\DependencyInjection\SimpleXMLElement;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Thelia\Command\ModuleGenerateModelCommand;
use Thelia\Command\ModuleGenerateSqlCommand;
use Thelia\Core\Application;
use Thelia\Core\Template\ParserInterface;
use Thelia\Core\Thelia;
use Thelia\Model\ConfigQuery;
use TheliaStudio\Events\ModuleGenerateEvent;
use TheliaStudio\Events\TheliaStudioEvents;
use TheliaStudio\Parser\ConfigParser;
use TheliaStudio\Parser\Entity\Config;
use TheliaStudio\Parser\Entity\Form;
use TheliaStudio\Parser\Entity\Loop;
use TheliaStudio\Parser\Entity\Route;
use TheliaStudio\Parser\Entity\Service;
use TheliaStudio\Parser\Entity\Tag;
use TheliaStudio\Parser\RoutingParser;
use TheliaStudio\Parser\Rule;
use TheliaStudio\Parser\RuleReader;
use TheliaStudio\Parser\SchemaParser;
use TheliaStudio\Parser\Table;
use TheliaStudio\TheliaStudio;
use TheliaStudio\Output\NullOutput;

/**
 * Class GenerateEverything
 * @package TheliaStudio\Action
 * @author Benjamin Perche <bperche9@gmail.com>
 *
 * This class does some coffee for you.
 */
class GenerateEverything implements EventSubscriberInterface
{
    /**
     * @var ParserInterface|\TheliaSmarty\Template\SmartyParser
     */
    protected $parser;

    protected $kernel;

    protected $resourcesPath;

    /**
     * @var \SplFileInfo[]
     */
    protected $tablePhpTemplates;

    /**
     * @var \SplFileInfo[]
     */
    protected $smartyTemplates;

    /**
     * @var \SplFileInfo[]
     */
    protected $rulesTemplates;

    /**
     * @var \SplFileInfo[]
     */
    protected $rawCopyTemplates;

    /**
     * @var \SplFileInfo[]
     */
    protected $modulePhpTemplates;

    protected $moduleCode;

    protected $previousTrimLevel;

    public function __construct(ParserInterface $parser, Thelia $kernel)
    {
        $this->parser = $parser;
        $this->kernel = $kernel;
    }

    /**
     * @param ModuleGenerateEvent $event
     *
     * Here is the build process:
     * 1. Launch propel to validate the schema and generate model && sql
     * 2. Find / prepare all the resources
     * 3. Compile the module related classes
     * 4. Compile the table related classes
     * 5. Compile the templates
     * 6. Copy raw files
     * 7. Apply rules
     * 8. Add everything in config.xml and routing.xml
     */
    public function launchBuild(ModuleGenerateEvent $event)
    {
        // Backup trim level and set it to 0
        $this->previousTrimLevel = ConfigQuery::read("html_output_trim_level");
        ConfigQuery::write("html_output_trim_level", 0);

        $this->moduleCode = $moduleCode = $event->getModuleCode();
        $modulePath = $modulePath = THELIA_MODULE_DIR . $moduleCode . DS;

        $e = null;

        try {
            // 1. Launch propel
            $this->generateModel($modulePath, $moduleCode);
            $this->generateSql($modulePath, $moduleCode);

            // 2. Prepare resources
            $entities = $this->buildEntities($modulePath, $moduleCode, $event->getTables());
            $this->prepareResources();
            $this->parser->assign("moduleCode", $moduleCode);
            $this->parser->assign("tables", $entities);


            // 3. Build module classes
            $this->processModule($entities);

            foreach ($entities as $entity) {
                $this->parser->assign("table", $entity);
                // 4. Build table classes
                $this->processTable($entity);
                // 5. Build templates
                $this->processTemplate($entity);
            }

            // 6. Process copy
            foreach ($this->rawCopyTemplates as $file) {
                $relativePath = $relativePath = str_replace($this->resourcesPath . "raw-copy", "", $file->getRealPath());
                $completePath = $modulePath . $relativePath;

                if (!file_exists($completePath)) {
                    @copy($file->getRealPath(), $completePath);
                }
            }

            // 7. Process rules
            $ruleReader = new RuleReader();
            foreach ($this->rulesTemplates as $rule) {
                $relativePath = str_replace($this->resourcesPath, "", $rule->getRealPath());

                $completePath = $modulePath . $relativePath;
                $completePath = substr($completePath, 0, -5); // remove .rule extension

                $rule = $ruleReader->readRules($rule->getRealPath());
                $this->processRule($rule, $completePath);
            }

            // 8. Add everything in config
            $this->processConfiguration($entities, $modulePath);
            $this->processRouting($entities, $modulePath);
        } catch (\Exception $e) {}

        // Restore trim level
        ConfigQuery::write("html_output_trim_level", $this->previousTrimLevel);

        if (null !== $e) {
            throw $e;
        }
    }

    protected function processRouting(array $tables, $modulePath)
    {
        $routingData = @file_get_contents($routingPath = $modulePath . "Config" . DS . "routing.xml");

        if (false === $routingData) {
            $routingData = "<?xml version=\"1.0\" encoding=\"UTF-8\"?><routes xmlns=\"http://symfony.com/schema/routing\" xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xsi:schemaLocation=\"http://symfony.com/schema/routing http://symfony.com/schema/routing/routing-1.0.xsd\"></routes>";
        }

        $routingParser = new RoutingParser();
        $xml = new SimpleXMLElement($routingData);

        /** @var \TheliaStudio\Parser\Entity\Route[] $routes */
        $routes = $routingParser->parseRoutes($xml);

        $newRoutes = $this->generateRouting($tables);

        /**
         * Merge
         */
        $routing = array_diff_key($newRoutes, $routes);

        /**
         * Then write
         */
        $this->writeRouting($routing, $routingPath, $xml);
    }

    /**
     * @param Route[] $routes
     * @param $routingPath
     * @param SimpleXMLElement $xml
     */
    protected function writeRouting(array $routes, $routingPath, SimpleXMLElement $xml)
    {
        foreach ($routes as $route) {
            /** @var SimpleXmlElement $element */
            $element = $xml->addChild("route");

            $element->addAttribute("id", $route->getId());
            $element->addAttribute("path", $route->getPath());

            if ($route->getMethods()) {
                $element->addAttribute("methods", $route->getMethods());
            }

            foreach ($route->getDefaults() as $key => $value) {
                $default = $element->addChild("default", $value);
                $default->addAttribute("key", $key);
            }

            foreach ($route->getRequirements() as $key => $value) {
                $requirement = $element->addChild("requirement", $value);
                $requirement->addAttribute("key", $key);
            }
        }

        $this->saveXml($xml, $routingPath);
    }

    /**
     * @param Table[] $tables
     */
    public function generateRouting(array $tables)
    {
        $routes = array();

        foreach ($tables as $table) {
            // List
            $routes[$table->getRawTableName() . ".list"] = new Route(
                $table->getRawTableName() . ".list",
                "/admin/module/" . $this->moduleCode . "/" . $table->getRawTableName(),
                "get",
                [
                    "_controller" => $this->moduleCode . ":" . $table->getTableName() . ":default"
                ]
            );

            // Create
            $routes[$table->getRawTableName() . ".create"] = new Route(
                $table->getRawTableName() . ".create",
                "/admin/module/" . $this->moduleCode . "/" . $table->getRawTableName(),
                "post",
                [
                    "_controller" => $this->moduleCode . ":" . $table->getTableName() . ":create"
                ]
            );

            // View
            $routes[$table->getRawTableName() . ".view"] = new Route(
                $table->getRawTableName() . ".view",
                "/admin/module/" . $this->moduleCode . "/" . $table->getRawTableName() . "/edit",
                "get",
                [
                    "_controller" => $this->moduleCode . ":" . $table->getTableName() . ":update"
                ]
            );

            // Edit
            $routes[$table->getRawTableName() . ".edit"] = new Route(
                $table->getRawTableName() . ".edit",
                "/admin/module/" . $this->moduleCode . "/" . $table->getRawTableName() . "/edit",
                "post",
                [
                    "_controller" => $this->moduleCode . ":" . $table->getTableName() . ":processUpdate"
                ]
            );

            // Delete
            $routes[$table->getRawTableName() . ".delete"] = new Route(
                $table->getRawTableName() . ".delete",
                "/admin/module/" . $this->moduleCode . "/" . $table->getRawTableName(),
                "post",
                [
                    "_controller" => $this->moduleCode . ":" . $table->getTableName() . ":delete"
                ]
            );

            // Update position
            if ($table->hasPosition()) {
                $routes[$table->getRawTableName() . ".update_position"] = new Route(
                    $table->getRawTableName() . ".update_position",
                    "/admin/module/" . $this->moduleCode . "/" . $table->getRawTableName() . "/updatePosition",
                    "get",
                    [
                        "_controller" => $this->moduleCode . ":" . $table->getTableName() . ":updatePosition"
                    ]
                );
            }

            // Toggle visibility
            if ($table->hasVisible()) {
                $routes[$table->getRawTableName() . ".toggle_visibility"] = new Route(
                    $table->getRawTableName() . ".toggle_visibility",
                    "/admin/module/" . $this->moduleCode . "/" . $table->getRawTableName() . "/toggleVisibility",
                    "get",
                    [
                        "_controller" => $this->moduleCode . ":" . $table->getTableName() . ":setToggleVisibility"
                    ]
                );
            }
        }

        return $routes;
    }

    /**
     * @param Table[] $tables
     * @param $modulePath
     */
    protected function processConfiguration(array $tables, $modulePath)
    {
        /**
         * Get current configuration
         */
        $configData = @file_get_contents($configPath = $modulePath . "Config" . DS . "config.xml");

        if (false === $configData) {
            throw new \Exception("missing file 'config.xml'");
        }

        $configParser = new ConfigParser();
        $xml = new SimpleXMLElement($configData);

        /** @var Config $config */
        $config = $configParser->parseXml($xml);

        /**
         * Get generated configuration
         */
        $generatedConfig = $this->generateConfiguration($tables);

        /**
         * Merge it
         */
        $config->mergeConfig($generatedConfig);

        /**
         * Write new configuration
         */
        $this->initializeConfig($xml);
        $this->writeNewConfig($config, $configPath, $xml);
    }

    protected function initializeConfig(SimpleXMLElement $xml)
    {
        if (!$xml->forms) {
            $xml->addChild("forms");
        }

        if (!$xml->loops) {
            $xml->addChild("loops");
        }

        if (!$xml->services) {
            $xml->addChild("services");
        }

        if (!$xml->hooks) {
            $xml->addChild("hooks");
        }
    }

    protected function writeNewConfig(Config $config, $configPath, SimpleXMLElement $xml)
    {
        $this->addForms($xml, $config);
        $this->addLoops($xml, $config);
        $this->addServices($xml, $config);

        // For now delete hooks node if it has no child
        if (!$xml->hooks->children()) {
            unset ($xml->hooks);
        }

        $this->saveXml($xml, $configPath);
    }

    protected function saveXml(SimpleXMLElement $xml, $path)
    {
        $dom = new \DOMDocument("1.0");
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $dom->loadXML($xml->asXML());
        @file_put_contents($path, $dom->saveXML());
    }

    protected function addForms(SimpleXMLElement $xml, Config $config)
    {
        foreach ($config->getForms() as $form) {
            if (!$xml->xpath("//config:forms/config:form[@name='{$form->getName()}']")) {
                $element = $xml->forms->addChild("form");
                $element->addAttribute("name", $form->getName());
                $element->addAttribute("class", $form->getClass());
            }
        }

    }

    protected function addLoops(SimpleXMLElement $xml, Config $config)
    {
        foreach ($config->getLoops() as $loop) {
            if (!$xml->xpath("//config:loops/config:loop[@name='{$loop->getName()}']")) {
                $element = $xml->loops->addChild("loop");
                $element->addAttribute("name", $loop->getName());
                $element->addAttribute("class", $loop->getClass());
            }
        }
    }

    protected function addServices(SimpleXMLElement $xml, Config $config)
    {
        foreach ($config->getServices() as $service) {
            if (!$xml->xpath("//config:services/config:service[@id='{$service->getId()}']")) {
                $element = $xml->services->addChild("service");
                $element->addAttribute("id", $service->getId());
                $element->addAttribute("class", $service->getClass());

                if ($service->getScope()) {
                    $element->addAttribute("scope", $service->getScope());
                }

                foreach ($service->getTags() as $tag) {
                    $tagXml = $element->addChild("tag");

                    foreach ($tag->getParameters() as $name => $parameter) {
                        $tagXml->addAttribute($name, $parameter);
                    }
                }
            }
        }
    }

    /**
     * @param Table[] $tables
     */
    protected function generateConfiguration(array $tables)
    {
        $config = new Config();

        foreach ($tables as $table) {
            $config->addLoop(new Loop(
                $table->getLoopType(),
                $this->moduleCode . "\\Loop\\" . $table->getTableName()
            ));

            $config->addForm(new Form(
                $table->getRawTableName() . "_create",
                $this->moduleCode . "\\Form\\" . $table->getTableName() . "CreateForm"
            ));

            $config->addForm(new Form(
                $table->getRawTableName() . "_update",
                $this->moduleCode . "\\Form\\" . $table->getTableName() . "UpdateForm"
            ));

            $service = new Service(
                "action." . $table->getRawTableName() . "_table",
                $this->moduleCode . "\\Action\\" . $table->getTableName() . "Action"
            );

            $service->addTag(new Tag("kernel.event_subscriber"));
            $config->addService($service);
        }

        return $config;
    }

    protected function processTemplate(Table $table)
    {
        $previousLeft = $this->parser->left_delimiter;
        $previousRight = $this->parser->right_delimiter;
        $this->parser->left_delimiter = '[{';
        $this->parser->right_delimiter = '}]';

        foreach ($this->smartyTemplates as $template) {
            $fetchedTemplate = $this->parser->fetch($template->getRealPath());
            $fileName = str_replace("__TABLE__", str_replace("_", "-", $table->getRawTableName()), $template->getFilename());

            $relativePath = str_replace($this->resourcesPath, "", $template->getPath() . DS);
            $completeFilePath = THELIA_MODULE_DIR . $this->moduleCode . DS . $relativePath . DS  . $fileName;

            if (!file_exists($completeFilePath)) {
                @mkdir(dirname($completeFilePath), 0777, true);
                @file_put_contents($completeFilePath, $fetchedTemplate);
            }

        }

        $this->parser->left_delimiter = $previousLeft;
        $this->parser->right_delimiter = $previousRight;
    }

    protected function processModule(array $entities)
    {
        foreach ($this->modulePhpTemplates as $template) {
            $fileName = str_replace("__MODULE__", $this->moduleCode, $template->getFilename());

            $relativePath = str_replace($this->resourcesPath, "", $template->getPath() . DS);
            $completeFilePath = THELIA_MODULE_DIR . $this->moduleCode . DS . $relativePath . DS  . $fileName;

            $fetchedTemplate = $this->parser->fetch($template->getRealPath());

            //@mkdir(dirname($completeFilePath), 0777, true);
            @file_put_contents($completeFilePath, $fetchedTemplate);
        }
    }

    protected function processRule(Rule $rule, $destination)
    {
        $sourceFile = dirname($destination) . DS . $rule->getSource();

        $source = "";
        if (is_file($sourceFile) && is_readable($sourceFile)) {
            $source = file_get_contents($sourceFile);
        }

        foreach ($rule->getRuleCollection() as $regex) {
            $source = preg_replace($regex[0], $regex[1], $source);
        }

        @file_put_contents($destination, $source);
    }

    protected function fixCs($modulePath)
    {
        $phpCsFixer = new PhpCsFixer();
        $output = new ConsoleOutput();

        $input = new ArrayInput([
            "command" => "fix",
            "path" => $modulePath,
        ]);

        $phpCsFixer->setAutoExit(false);
        $phpCsFixer->run($input, $output);
    }

    protected function processTable(Table $table)
    {
        foreach ($this->tablePhpTemplates as $template) {
            $fileName = str_replace("__TABLE__", $table->getTableName(), $template->getFilename());
            $fileName = str_replace("FIX", "", $fileName);

            $relativePath = str_replace($this->resourcesPath, "", $template->getPath() . DS);
            $completeFilePath = THELIA_MODULE_DIR . $this->moduleCode . DS . $relativePath . DS  . $fileName;

            $isFix = false !== strpos($template->getFilename(), "FIX");
            $isI18n = false !== strpos($template->getFilename(), "I18n");

            if ((($isFix && !file_exists($completeFilePath)) || !$isFix) && ($isI18n && $table->hasI18nBehavior() || !$isI18n)) {
                $fetchedTemplate = $this->parser->fetch($template->getRealPath());

                @mkdir(dirname($completeFilePath), 0777, true);
                @file_put_contents($completeFilePath, $fetchedTemplate);
            }
        }
    }

    protected function prepareResources()
    {
        $this->resourcesPath = $resourcesPath = ConfigQuery::read(TheliaStudio::RESOURCE_PATH_CONFIG_NAME) . DS ;

        if (!is_dir($resourcesPath) || !is_readable($resourcesPath)) {
            throw new FileNotFoundException(sprintf(
                "The resources directory %s doesn't exist",
                $resourcesPath
            ));
        }

        $this->tablePhpTemplates = $this->findInPath($resourcesPath, "/__TABLE__.*\.php$/");
        $this->modulePhpTemplates = $this->findInPath($resourcesPath, "/__MODULE__.*\.php$/");
        $this->smartyTemplates = $this->findInPath($resourcesPath, "/__TABLE__.*\.html$/");
        $this->rulesTemplates = $this->findInPath($resourcesPath, "/\.rule$/");

        $this->rawCopyTemplates = Finder::create()->files()->in($resourcesPath . "raw-copy");
    }

    protected function findInPath($resourcesPath, $name)
    {
        return Finder::create()
            ->files()
            ->in($resourcesPath)
            ->name($name)
            ->exclude(["includes", "raw-copy"])
        ;
    }

    /**
     * @param $modulePath
     * @param $moduleCode
     * @return \TheliaStudio\Parser\Table[]
     */
    protected function buildEntities($modulePath, $moduleCode, $whiteList)
    {
        $entities = array();
        $schemaFile = $modulePath . "Config" . DS . "schema.xml";

        $whiteList = trim($whiteList);

        if (null === $whiteList || '' === $whiteList) {
            $whiteList = array();
        } else {
            $whiteList = array_map("trim", explode(",", $whiteList));
        }

        if (is_file($schemaFile) && is_readable($schemaFile)) {
            $xml = new SimpleXMLElement(file_get_contents($schemaFile));
            $parser = new SchemaParser();

            $entities = $parser->parseXml($xml, $whiteList);
        }

        return $entities;
    }

    protected function generateSql($modulePath, $moduleCode)
    {
        /**
         * Generate sql
         */
        $moduleGenerateModel = new ModuleGenerateSqlCommand();
        $moduleGenerateModel->setApplication(new Application($this->kernel));

        $output = new NullOutput();

        $moduleGenerateModel->run(
            new ArrayInput(array(
                "command" => $moduleGenerateModel->getName(),
                "name" => $moduleCode,
            )),
            $output
        );
        // Remove *.map
        $files = (new Finder())->files()->in($modulePath . "Config")->name("*.map");

        /** @var \SplFileInfo $file */
        foreach ($files as $file) {
            unlink($file->getRealPath()); // You're useless ! (:
        }
    }

    protected function generateModel($modulePath, $moduleCode)
    {
        /**
         * Generate model
         */
        $moduleGenerateModel = new ModuleGenerateModelCommand();
        $moduleGenerateModel->setApplication(new Application($this->kernel));

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
        return array(
            TheliaStudioEvents::LAUNCH_MODULE_BUILD => array("launchBuild", 128),
        );
    }
}
