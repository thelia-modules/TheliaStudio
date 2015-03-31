<?php
{include "../../includes/header.php"}

namespace {$moduleCode}\Event\Module\Base;

/**
 * Class {$moduleCode}Events
 * @package {$moduleCode}\Event\Module\Base
 * @author TheliaStudio
 */
class {$moduleCode}Events
{
{foreach from=$tables item=table}
    const {$table->getUppercaseName()}_CREATE = "action.{$table->getRawTableName()}.create";
    const {$table->getUppercaseName()}_UPDATE = "action.{$table->getRawTableName()}.update";
    const {$table->getUppercaseName()}_DELETE = "action.{$table->getRawTableName()}.delete";
{if $table->hasPosition()}
    const {$table->getUppercaseName()}_UPDATE_POSITION = "action.{$table->getRawTableName()}.update_position";
{/if}
{if $table->hasVisible()}
    const {$table->getUppercaseName()}_TOGGLE_VISIBILITY = "action.{$table->getRawTableName()}.toggle_visilibity";
{/if}
{/foreach}
}
