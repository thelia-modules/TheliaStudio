<?php
{include "../includes/header.php"}

namespace {$moduleCode}\Form;

use {$moduleCode}\{$moduleCode};
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
            "{$field->getName()}" => $this->translator->trans("{$field->getLabel()}", [], {$moduleCode}::MESSAGE_DOMAIN),
{/foreach}
{foreach from=$form item=field}
{if {$field->getHelp()}}
            "help.{$field->getName()}" => $this->translator->trans("{$field->getHelp()}", [], {$moduleCode}::MESSAGE_DOMAIN),
{/if}
{/foreach}
        );
    }
}
