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
    protected ${$column->getCamelizedName()|lcfirst};
{/foreach}
    protected ${$table->getTableName()|lcfirst};
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
        return $this->{$column->getCamelizedName()|lcfirst};
    }

    public function set{$column->getCamelizedName()}(${$column->getCamelizedName()|lcfirst})
    {
        $this->{$column->getCamelizedName()|lcfirst} = ${$column->getCamelizedName()|lcfirst};

        return $this;
    }

{/foreach}
    public function get{$table->getTableName()}()
    {
        return $this->{$table->getTableName()|lcfirst};
    }

    public function set{$table->getTableName()}({$table->getModelClass()} ${$table->getTableName()|lcfirst})
    {
        $this->{$table->getTableName()|lcfirst} = ${$table->getTableName()|lcfirst};

        return $this;
    }
}
