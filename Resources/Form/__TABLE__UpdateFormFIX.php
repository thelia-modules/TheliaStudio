<?php
{include "../includes/header.php"}

namespace {$moduleCode}\Form;

use {$moduleCode}\Form\Base\{$table->getTableName()}UpdateForm as Base{$table->getTableName()}UpdateForm;

/**
 * Class {$table->getTableName()}UpdateForm
 * @package {$moduleCode}\Form
 */
class {$table->getTableName()}UpdateForm extends Base{$table->getTableName()}UpdateForm
{
    public function getTranslationKeys()
    {
        return array(
{foreach from=$table->getColumns() item=column}
            "{$column->getName()}" => "{$column->getName()}",
{/foreach}
        );
    }
}
