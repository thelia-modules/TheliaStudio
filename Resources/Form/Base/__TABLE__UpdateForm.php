<?php
{include "../../includes/header.php"}

namespace {$moduleCode}\Form\Base;

use Thelia\Form\BaseForm;

/**
 * Class {$table->getTableName()}Form
 * @package {$moduleCode}\Form
 * @author TheliaStudio
 */
class {$table->getTableName()}UpdateForm extends {$table->getTableName()}CreateForm
{
    const FORM_NAME = "{$table->getRawTableName()}_update";

    public function buildForm()
    {
        parent::buildForm();

        $this->formBuilder
            ->add("id", {$table->getTableName()}IdType::TYPE_NAME)
        ;
    }
}
