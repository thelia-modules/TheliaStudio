<?php
/*************************************************************************************/
/* This file is part of the Thelia package.                                          */
/*                                                                                   */
/* Copyright (c) OpenStudio                                                          */
/* email : dev@thelia.net                                                            */
/* web : http://www.thelia.net                                                       */
/*                                                                                   */
/* For the full copyright and license information, please view the LICENSE.txt       */
/* file that was distributed with this source code.                                  */
/*************************************************************************************/

namespace TheliaStudio\Parser;

use Symfony\Component\DependencyInjection\SimpleXMLElement;


/**
 * Class SchemaParser
 * @package TheliaStudio\Parser
 * @author Benjamin Perche <bperche9@gmail.com>
 */
class SchemaParser
{
    /**
     * @param SimpleXMLElement $xml
     * @return Table[]
     */
    public function parseXml(SimpleXMLElement $xml, array $whiteList = array())
    {
        $entities = array();
        $defaultNamespace = '';

        foreach ($xml->xpath("//database") as $database) {
            $currentNs = $database->getAttributeAsPhp("namespace");

            if (!empty($currentNs)) {
                $defaultNamespace = $currentNs . "\\";
            }
        }

        /** @var SimpleXmlElement $table */
        foreach ($xml->xpath("//database/table") as $table) {
            $tableName = $table->getAttributeAsPhp('name');

            if (!$whiteList || in_array($tableName, $whiteList)) {
                $entities[] = $tableInstance = new Table($tableName, $defaultNamespace . $table->getAttributeAsPhp("namespace"));

                $this->readColumns($table, $tableInstance);
                $this->readBehaviors($table, $tableInstance);
            }
        }

        return $entities;
    }

    protected function readColumns(SimpleXMLElement $xml, Table $table)
    {
        /**
         * @var SimpleXmlElement $column
         *
         * report columns
         */
        foreach ($xml->xpath(".//column") as $column) {
            $table->addColumn(new Column(
                $column->getAttributeAsPhp("name"),
                $column->getAttributeAsPhp("type"),
                $column->getAttributeAsPhp("required")
            ));
        }
    }

    protected function readBehaviors(SimpleXMLElement $xml, Table $table)
    {
        /**
         * @var SimpleXmlElement $behavior
         *
         * report behaviors
         */
        foreach ($xml->xpath(".//behavior") as $behavior) {
            $table->addBehavior($name = $behavior->getAttributeAsPhp('name'));

            if ($name === 'i18n') {
                foreach ($behavior->xpath(".//parameter") as $parameter) {
                    if ($parameter->getAttributeAsPhp('name') === 'i18n_columns') {
                        $i18nColumns = array_map("trim", explode(",", $parameter->getAttributeAsPhp("value")));

                        foreach ($i18nColumns as $i18nColumn) {
                            $table->getColumn($i18nColumn)->setI18n(true);
                        }
                    }
                }
            }
        }
    }
}
