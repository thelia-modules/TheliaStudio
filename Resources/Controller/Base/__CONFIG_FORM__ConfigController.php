<?php
{include "../../includes/header.php"}

namespace {$moduleCode}\Controller\Base;

use {$moduleCode}\{$moduleCode};
use Thelia\Controller\Admin\BaseAdminController;
use Thelia\Form\Exception\FormValidationException;
use Thelia\Core\Security\Resource\AdminResources;
use Thelia\Core\Security\AccessManager;
use {$moduleCode}\Model\Config\{$moduleCode}ConfigValue;

/**
 * Class {$moduleCode}ConfigController
 * @package {$moduleCode}\Controller\Base
 * @author TheliaStudio
 */
class {$moduleCode}ConfigController extends BaseAdminController
{
    public function defaultAction()
    {
        if (null !== $response = $this->checkAuth([AdminResources::MODULE], ["{$moduleCode|lower}"], AccessManager::VIEW)) {
            return $response;
        }

        return $this->render("{$moduleCode|lower}-configuration");
    }

    public function saveAction()
    {
        if (null !== $response = $this->checkAuth([AdminResources::MODULE], ["{$moduleCode|lower}"], AccessManager::UPDATE)) {
            return $response;
        }

        $baseForm = $this->createForm("{$moduleCode|lower}.configuration");

        $errorMessage = null;

        try {
            $form = $this->validateForm($baseForm);
            $data = $form->getData();

{foreach from=$form item=field}
            {$moduleCode}::setConfigValue({$moduleCode}ConfigValue::{$field->getConstantName()}, is_bool($data["{$field->getName()}"]) ? (int) ($data["{$field->getName()}"]) : $data["{$field->getName()}"]);
{/foreach}
        } catch (FormValidationException $ex) {
            // Invalid data entered
            $errorMessage = $this->createStandardFormValidationErrorMessage($ex);
        } catch (\Exception $ex) {
            // Any other error
            $errorMessage = $this->getTranslator()->trans('Sorry, an error occurred: %err', ['%err' => $ex->getMessage()], [], {$moduleCode}::MESSAGE_DOMAIN);
        }

        if (null !== $errorMessage) {
            // Mark the form as with error
            $baseForm->setErrorMessage($errorMessage);

            // Send the form and the error to the parser
            $this->getParserContext()
                ->addForm($baseForm)
                ->setGeneralError($errorMessage)
            ;
        } else {
            $this->getParserContext()
                ->set("success", true)
            ;
        }

        return $this->defaultAction();
    }
}
