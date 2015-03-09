<?php
{include "../../includes/header.php"}

namespace {$moduleCode}\Event\Base;

use {$moduleCode}\Event\{$moduleCode}Events;

/*
 * Class {$table->getTableName()}Events
 * @package {$moduleCode}\Event\Base
 * @author TheliaStudio
 */
class {$table->getTableName()}Events
{
    const CREATE = {$moduleCode}Events::{$table->getUppercaseName()}_CREATE;
    const UPDATE = {$moduleCode}Events::{$table->getUppercaseName()}_UPDATE;
    const DELETE = {$moduleCode}Events::{$table->getUppercaseName()}_DELETE;
{if $table->hasPosition()}
    const UPDATE_POSITION = {$moduleCode}Events::{$table->getUppercaseName()}_UPDATE_POSITION;
{/if}
{if $table->hasVisible()}
    const TOGGLE_VISIBILITY = {$moduleCode}Events::{$table->getUppercaseName()}_TOGGLE_VISIBILITY;
{/if}
}
