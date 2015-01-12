<?php
{include "../includes/header.php"}

namespace {$moduleCode}\Form;

use {$moduleCode}\Form\Base\{$table->getTableName()}CreateForm as Base{$table->getTableName()}CreateForm;

/**
 * Class {$table->getTableName()}CreateForm
 * @package {$moduleCode}\Form
 */
class {$table->getTableName()}CreateForm extends Base{$table->getTableName()}CreateForm
{
    public function getTranslationKeys()
    {
        return array(
{foreach from=$table->getColumns() item=column}
{if $column->getName() != 'id' && $column->getName() != 'position'}
            "{$column->getName()}" => "{$column->getName()|ucfirst|replace:'_':' '}",
{/if}
{/foreach}
        );
    }
}
