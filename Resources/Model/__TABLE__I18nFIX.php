<?php
{include "../includes/header.php"}

namespace {$moduleCode}\Model;

use {$moduleCode}\Model\Base\{$table->getTableName()}I18n as Base{$table->getTableName()}I18n;
{if $table->hasI18nBehavior() && $table->hasTimestampableBehavior()}
use Thelia\Model\Tools\I18nTimestampableTrait;
{/if}

/**
 * Class {$table->getTableName()}I18n
 * @package {$moduleCode}\Model
 */
class {$table->getTableName()}I18n extends Base{$table->getTableName()}I18n
{
{if $table->hasI18nBehavior() && $table->hasTimestampableBehavior()}
    use I18nTimestampableTrait;
{/if}
}
