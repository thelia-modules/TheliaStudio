<?php
{include "../../includes/header.php"}

namespace {$moduleCode}\Event\Base;

{foreach from=$tables item=table}
use {$moduleCode}\Event\{$table->getTableName()}Events;
{/foreach}

/**
 * Class {$moduleCode}Events
 * @package {$moduleCode}\Event\Base
 * @author TheliaStudio
 */
class {$moduleCode}Events
{
{foreach from=$tables item=table}
    const {$table->getUppercaseName()}_CREATE = {$table->getTableName()}Events::CREATE;
    const {$table->getUppercaseName()}_UPDATE = {$table->getTableName()}Events::UPDATE;
    const {$table->getUppercaseName()}_DELETE = {$table->getTableName()}Events::DELETE;
{if $table->hasPosition()}
    const {$table->getUppercaseName()}_UPDATE_POSITION = {$table->getTableName()}Events::UPDATE_POSITION;
{/if}
{if $table->hasVisible()}
    const {$table->getUppercaseName()}_TOGGLE_VISIBILITY = {$table->getTableName()}Events::TOGGLE_VISIBILITY;
{/if}
{/foreach}
}
