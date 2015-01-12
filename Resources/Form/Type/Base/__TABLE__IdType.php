<?php
{include "../../../includes/header.php"}

namespace {$moduleCode}\Form\Type\Base;

use Thelia\Core\Form\Type\Field\AbstractIdType;
use {$table->getFullQueryClass()};

/**
 * Class {$table->getTableName()}
 * @package {$moduleCode}\Form\Base
 * @author TheliaStudio
 */
class {$table->getTableName()}IdType extends AbstractIdType
{
    const TYPE_NAME = "{$table->getRawTableName()}_id";

    protected function getQuery()
    {
        return new {$table->getQueryClass()}();
    }

    public function getName()
    {
        return static::TYPE_NAME;
    }
}
