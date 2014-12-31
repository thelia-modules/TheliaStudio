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
use TheliaStudio\Parser\Entity\Loop;

/**
 * Class ConfigParser
 * @package TheliaStudio\Parser
 * @author Benjamin Perche <bperche9@gmail.com>
 */
class ConfigParser
{
    public function parseXml(SimpleXMLElement $xml)
    {

    }

    protected function parseLoops(SimpleXMLElement $xml)
    {
        $loops = array();

        /** @var SimpleXmlElement $loopXml */
        foreach ($xml->xpath("//config/loops") as $loopXml) {
            $loops[] = new Loop($loopXml->getAttributeAsPhp("name"), $loopXml->getAttributeAsPhp("class"));
        }

        return $loops;
    }

    protected function parseForms(SimpleXMLElement $xml)
}
