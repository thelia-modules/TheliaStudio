<?php
{include "../../includes/header.php"}

namespace {$moduleCode}\Form\Base;

use {$moduleCode}\Form\{$table->getTableName()}CreateForm as Child{$table->getTableName()}CreateForm;
use {$moduleCode}\Form\Type\{$table->getTableName()}IdType;

/**
 * Class {$table->getTableName()}Form
 * @package {$moduleCode}\Form
 * @author TheliaStudio
 */
class {$table->getTableName()}UpdateForm extends Child{$table->getTableName()}CreateForm
{
    const FORM_NAME = "{$table->getRawTableName()}_update";

    public function buildForm()
    {
        parent::buildForm();

        $this->formBuilder
            ->add("id", {$table->getTableName()}IdType::TYPE_NAME)
{if $table->hasVisible()}
            ->remove("visible")
{/if}
        ;
    }
}
