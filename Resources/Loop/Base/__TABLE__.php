<?php
{include "../../includes/header.php"}

namespace {$moduleCode}\Loop\Base;

use Propel\Runtime\ActiveQuery\Criteria;
use Thelia\Core\Template\Element\Base{if $table->hasI18nBehavior()}I18n{/if}Loop;
use Thelia\Core\Template\Element\LoopResult;
use Thelia\Core\Template\Element\LoopResultRow;
use Thelia\Core\Template\Element\PropelSearchLoopInterface;
use Thelia\Core\Template\Loop\Argument\Argument;
use Thelia\Core\Template\Loop\Argument\ArgumentCollection;
use Thelia\Type\BooleanOrBothType;
use {$table->getFullQueryClass()};


/**
 * Class {$table->getTableName()}
 * @package {$moduleCode}\Loop\Base
 * @author TheliaStudio
 */
class {$table->getTableName()} extends Base{if $table->hasI18nBehavior()}I18n{/if}Loop implements PropelSearchLoopInterface
{
    /**
     * @param LoopResult $loopResult
     *
     * @return LoopResult
     */
    public function parseResults(LoopResult $loopResult)
    {
        /** @var \{$table->getFullModelClass()} $entry */
        foreach ($loopResult->getResultDataCollection() as $entry) {
            $row = new LoopResultRow($entry);

            $row
            {foreach from=$table->getColumns() item=column}
            ->set("{$column->getNameAsSQL()}", $entry->get{if $column->isI18n()}VirtualColumn("i18n_{$column->getNameAsSQL()}"){else}{$column->getCamelizedName()}(){/if})
            {/foreach}
            ;

            $loopResult->addRow($row);
        }

        return $loopResult;
    }

    /**
     * Definition of loop arguments
     *
     * example :
     *
     * public function getArgDefinitions()
     * {
     *  return new ArgumentCollection(
     *
     *       Argument::createIntListTypeArgument('id'),
     *           new Argument(
     *           'ref',
     *           new TypeCollection(
     *               new Type\AlphaNumStringListType()
     *           )
     *       ),
     *       Argument::createIntListTypeArgument('category'),
     *       Argument::createBooleanTypeArgument('new'),
     *       ...
     *   );
     * }
     *
     * @return \Thelia\Core\Template\Loop\Argument\ArgumentCollection
     */
    protected function getArgDefinitions()
    {
        return new ArgumentCollection(
            {foreach from=$table->getColumns() item=column}
            {if $column->getPhpType() == "int"}
            Argument::createIntListTypeArgument("{$column->getName()}"),
            {elseif $column->getPhpType() == "bool"}
            Argument::createBooleanOrBothTypeArgument("{$column->getName()}", BooleanOrBothType::ANY),
            {elseif $column->getPhpType() == "text" || $column->getPhpType() == "double"}
            Argument::createAnyTypeArgument("{$column->getName()}"),
            {/if}

            {/foreach}
            Argument::createEnumListTypeArgument(
                "order",
                [
                    {foreach from=$table->getColumns() item=column}
                    "{if $column->getName() == 'position'}manual{else}{$column->getName()}{/if}",
                    "{if $column->getName() == 'position'}manual{else}{$column->getName()}{/if}-reverse",
                    {/foreach}
                ],
                "{if $table->has('position')}manual{else}id{/if}"
            )
        );
    }

    /**
     * this method returns a Propel ModelCriteria
     *
     * @return \Propel\Runtime\ActiveQuery\ModelCriteria
     */
    public function buildModelCriteria()
    {
        $query = new {$table->getQueryClass()}();
        {if $table->hasI18nBehavior()}
        $this->configureI18nProcessing($query, [{foreach from=$table->getI18nColumns() item=column}"{$column->getNameAsSQL()}", {/foreach}]);
        {/if}

        {foreach from=$table->getColumns() item=column}
        {if $column->getPhpType() == "int"}
        if (null !== ${$column->getName()} = $this->get{$column->getCamelizedName()}()) {
            $query->filterBy{$column->getCamelizedName()}(${$column->getName()});
        }
        {elseif $column->getPhpType() == "bool"}
        if (BooleanOrBothType::ANY !== ${$column->getName()} = $this->get{$column->getCamelizedName()}()) {
            $query->filterBy{$column->getCamelizedName()}(${$column->getName()});
        }
        {elseif $column->getPhpType() == "text" || $column->getPhpType() == "double"}
        if (null !== ${$column->getName()} = $this->get{$column->getCamelizedName()}()) {
            ${$column->getName()} = array_map("trim", explode(",", ${$column->getName()}));
            $query->filterBy{$column->getCamelizedName()}(${$column->getName()});
        }
        {/if}
        {/foreach}

        foreach ($this->getOrder() as $order) {
            switch ($order) {
                {foreach from=$table->getColumns() item=column}
                case "{if $column->getName() == 'position'}manual{else}{$column->getName()}{/if}":
                    {if $column->isI18n()}
                    $query->addAscendingOrderBy("i18n_{$column->getNameAsSQL()}");
                    {else}
                    $query->orderBy{$column->getCamelizedName()}();
                    {/if}
                    break;
                case "{if $column->getName() == 'position'}manual{else}{$column->getName()}{/if}-reverse":
                    {if $column->isI18n()}
                    $query->addDescendingOrderBy("i18n_{$column->getNameAsSQL()}");
                    {else}
                    $query->orderBy{$column->getCamelizedName()}(Criteria::DESC);
                    {/if}
                    break;
                {/foreach}
            }
        }

        return $query;
    }
}
