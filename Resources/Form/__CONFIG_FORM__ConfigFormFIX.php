<?php
{include "../includes/header.php"}

namespace {$moduleCode}\Form;

use {$moduleCode}\Form\Base\{$moduleCode}ConfigForm as Base{$moduleCode}ConfigForm;

/**
 * Class {$moduleCode}ConfigForm
 * @package {$moduleCode}\Form\Base
 */
class {$moduleCode}ConfigForm extends Base{$moduleCode}ConfigForm
{
    public function getTranslationKeys()
    {
        return array(
{foreach from=$form item=field}
            "{$field->getName()}" => "{$field->getName()|ucfirst|replace:'_':' '}",
{/foreach}
        );
    }
}
