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
     * 8. Fix cs
     */
    public function launchBuild(ModuleGenerateEvent $event)
    {
        // Backup trim level and set it to 0
        $this->previousTrimLevel = ConfigQuery::read("html_output_trim_level");
        ConfigQuery::write("html_output_trim_level", 2);

        $this->moduleCode = $moduleCode = $event->getModuleCode();
        $modulePath = $modulePath = THELIA_MODULE_DIR . $moduleCode . DS;

        $e = null;

        try {
            // 1. Launch propel
            //$this->generateModel($modulePath, $moduleCode);
            //$this->generateSql($modulePath, $moduleCode);

            // 2. Prepare resources
            $entities = $this->buildEntities($modulePath, $moduleCode);
            $this->prepareResources();
            $this->parser->assign("moduleCode", $moduleCode);
            $this->parser->assign("tables", $entities);


            // 3. Build module classes
            $this->processModule($entities);

            // 4. Build table classes
            foreach ($entities as $entity) {
                $this->processTable($entity);
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
        } catch (\Exception $e) {}

        // Restore trim level
        ConfigQuery::write("html_output_trim_level", $this->previousTrimLevel);

        if (null !== $e) {
            throw $e;
        }
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
            $this->parser->assign("table", $table);

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
        $this->smartyTemplates = $this->findInPath($resourcesPath, "/\.html$/");
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
    protected function buildEntities($modulePath, $moduleCode)
    {
        $entities = array();
        $schemaFile = $modulePath . "Config" . DS . "schema.xml";

        if (is_file($schemaFile) && is_readable($schemaFile)) {
            $xml = new SimpleXMLElement(file_get_contents($schemaFile));
            $parser = new SchemaParser();

            $entities = $parser->parseXml($xml);
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
