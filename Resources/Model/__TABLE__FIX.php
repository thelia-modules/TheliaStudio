<?php
{include "../includes/header.php"}

namespace {$moduleCode}\Model;

use {$moduleCode}\Model\Base\{$table->getTableName()} as Base{$table->getTableName()};
use Thelia\Model\Tools\ModelEventDispatcherTrait;
{if $table->hasPosition()}
use Thelia\Model\Tools\PositionManagementTrait;
use Propel\Runtime\Connection\ConnectionInterface;
{/if}
{if $table->hasSeo()}
use Thelia\Model\Tools\UrlRewritingTrait;
{/if}

/**
 * Class {$table->getTableName()}
 * @package {$moduleCode}\Model
 */
class {$table->getTableName()} extends Base{$table->getTableName()}
{
    use ModelEventDispatcherTrait;
{if $table->hasSeo()}
    use UrlRewritingTrait;
{/if}
{if $table->hasPosition()}
    use PositionManagementTrait;

    public function preInsert(ConnectionInterface $con = null)
    {
        $this->setPosition($this->getNextPosition());

        return true;
    }
{/if}

{if $table->hasSeo()}
    public function getRewrittenUrlViewName()
    {
        return '{$table->getLowercaseName()}';
    }
{/if}
}
