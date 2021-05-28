<?php
{include "../../includes/header.php"}

namespace {$moduleCode}\Event\Base;

use {$moduleCode}\Event\Module\{$moduleCode}Events as Child{$moduleCode}Events;

/*
 * Class {$table->getTableName()}Events
 * @package {$moduleCode}\Event\Base
 * @author TheliaStudio
 */
class {$table->getTableName()}Events
{
    const CREATE = Child{$moduleCode}Events::{$table->getUppercaseName()}_CREATE;
    const UPDATE = Child{$moduleCode}Events::{$table->getUppercaseName()}_UPDATE;
    const DELETE = Child{$moduleCode}Events::{$table->getUppercaseName()}_DELETE;
{if $table->hasPosition()}
    const UPDATE_POSITION = Child{$moduleCode}Events::{$table->getUppercaseName()}_UPDATE_POSITION;
{/if}
{if $table->hasVisible()}
    const TOGGLE_VISIBILITY = Child{$moduleCode}Events::{$table->getUppercaseName()}_TOGGLE_VISIBILITY;
{/if}
{if $table->hasSeo()}
    const UPDATE_SEO = Child{$moduleCode}Events::{$table->getUppercaseName()}_UPDATE_SEO;
{/if}
}
