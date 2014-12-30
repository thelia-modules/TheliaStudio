<?php
{include "../../includes/header.php"}
namespace {$moduleCode}\Event\Base;

use Thelia\Core\Event\ActionEvent;
use {$table->getFullModelClass()};
/**
* Class {$table->getTableName()}Event
* @package {$moduleCode}\Event\Base
* @author TheliaStudio
*/
class {$table->getTableName()}Event extends ActionEvent
{
    {foreach from=$table->getColumns() item=column}
    protected ${$column->getName()};
    {/foreach}
    protected ${$table->getRawTableName()};

    {if $table->hasI18nBehavior()}
    protected $locale;

    public function getLocale()
    {
        return $this->locale;
    }

    public function setLocale($v)
    {
        $this->locale = $v;

        return $this;
    }
    {/if}

    {foreach from=$table->getColumns() item=column}
    public function get{$column->getCamelizedName()}()
    {
        return $this->{$column->getName()};
    }

    public function set{$column->getCamelizedName()}($v)
    {
        $this->{$column->getName()} = $v;

        return $this;
    }
    {/foreach}

    public function get{$table->getTableName()}()
    {
        return $this->{$table->getRawTableName()};
    }

    public function set{$table->getTableName()}({$table->getModelClass()} $v)
    {
        $this->{$table->getRawTableName()} = $v;

        return $this;
    }
}
