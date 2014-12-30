<?php
{include "../../includes/header.php"}
namespace {$moduleCode}\Event\Base;


/**
 * Class {$table->getTableName()}Events
 * @package {$moduleCode}\Event\Base
 * @author TheliaStudio
 */
class {$table->getTableName()}Events
{
    const CREATE = "action.{$table->getRawTableName()}.create";
    const UPDATE = "action.{$table->getRawTableName()}.update";
    const DELETE = "action.{$table->getRawTableName()}.delete";
    {if $table->hasPosition()}
const UPDATE_POSITION = "action.{$table->getRawTableName()}.update_position";
    {/if}
    {if $table->hasVisible()}
const TOGGLE_VISIBILITY = "action.{$table->getRawTableName()}.toggle_visilibity";
    {/if}
}
