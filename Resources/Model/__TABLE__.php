<?php
{include "../includes/header.php"}

namespace {$moduleCode}\Model;

use {$moduleCode}\Model\Base\{$table->getTableName()} as Base{$table->getTableName()};
{if $table->hasPosition()}
use Thelia\Model\Tools\ModelEventDispatcherTrait;
use Thelia\Model\Tools\PositionManagementTrait;
use Propel\Runtime\Connection\ConnectionInterface;
{/if}

/**
 * Class {$table->getTableName()}
 * @package {$moduleCode}\Model
 */
class {$table->getTableName()} extends Base{$table->getTableName()}
{
{if $table->hasPosition()}
    use ModelEventDispatcherTrait;
    use PositionManagementTrait;

    public function preInsert(ConnectionInterface $con = null)
    {
        $this->setPosition($this->getNextPosition());

        return true;
    }
{/if}
}
