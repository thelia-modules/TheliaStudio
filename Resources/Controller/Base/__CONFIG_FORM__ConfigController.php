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

namespace {$moduleCode}\Controller\Base;

use {$moduleCode}\{$moduleCode};
use Thelia\Controller\Admin\BaseAdminController;
use Thelia\Form\Exception\FormValidationException;
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
        return $this->render("{$moduleCode|lower}-configuration");
    }

    public function saveAction()
    {
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
