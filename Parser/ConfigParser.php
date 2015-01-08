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
use TheliaStudio\Parser\Entity\Config;
use TheliaStudio\Parser\Entity\Form;
use TheliaStudio\Parser\Entity\Loop;
use TheliaStudio\Parser\Entity\Service;
use TheliaStudio\Parser\Entity\Tag;

/**
 * Class ConfigParser
 * @package TheliaStudio\Parser
 * @author Benjamin Perche <bperche9@gmail.com>
 */
class ConfigParser
{
    public function parseXml(SimpleXMLElement $xml)
    {
        static::registerNamespace($xml);

        $config = new Config();

        $this->parseLoops($xml, $config);
        $this->parseForms($xml, $config);
        $this->parseServices($xml, $config);

        return $config;
    }

    protected function parseLoops(SimpleXMLElement $xml, Config $config)
    {
        /** @var SimpleXmlElement $loopXml */
        foreach ($xml->xpath("//config:loops/config:loop") as $loopXml) {
            $config->addLoop(new Loop($loopXml->getAttributeAsPhp("name"), $loopXml->getAttributeAsPhp("class")));
        }
    }

    protected function parseForms(SimpleXMLElement $xml, Config $config)
    {
        /** @var SimpleXmlElement $formXml */
        foreach ($xml->xpath("//config:forms/config:form")  as $formXml) {
            $config->addForm(new Form($formXml->getAttributeAsPhp("name"), $formXml->getAttributeAsPhp("class")));
        }
    }

    protected function parseServices(SimpleXMLElement $xml, Config $config)
    {
        /** @var SimpleXmlElement $serviceXml */
        foreach ($xml->xpath("//config:services/config:service")  as $serviceXml) {
            $this->parseService($serviceXml, $config);
        }
    }

    protected function parseService(SimpleXMLElement $serviceXml, Config $config)
    {
        $service = new Service(
            $serviceXml->getAttributeAsPhp("id"),
            $serviceXml->getAttributeAsPhp("class"),
            $serviceXml->getAttributeAsPhp("scope")
        );

        static::registerNamespace($serviceXml);

        foreach ($serviceXml->xpath(".//config:tag") as $tagXml) {
            $parameters = array();
            foreach ($tagXml->attributes() as $k => $v) {
                $parameters[$k] = $v;
            }

            $service->addTag(new Tag($parameters["name"], $parameters));
        }

        $config->addService($service);
    }

    public static function registerNamespace(SimpleXMLElement $xml)
    {
        $xml->registerXPathNamespace('config', 'http://thelia.net/schema/dic/config');
    }
}
