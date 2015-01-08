<?php
/*************************************************************************************/
/*      This file is part of the Thelia package.                                     */
/*                                                                                   */
/*      Copyright (c) OpenStudio                                                     */
/*      email : dev@thelia.net                                                       */
/*      web : http://www.thelia.net                                                  */
/*                                                                                   */
/*      For the full copyright and license information, please view the LICENSE.txt  */
/*      file that was distributed with this source code.                             */
/*************************************************************************************/

namespace TheliaStudio;

use Propel\Runtime\Connection\ConnectionInterface;
use Thelia\Model\Config;
use Thelia\Module\BaseModule;
use Thelia\Model\ConfigQuery;

class TheliaStudio extends BaseModule
{
    const RESOURCE_PATH_CONFIG_NAME = "thelia_studio.config.resource_path";

    public function postActivation(ConnectionInterface $con = null)
    {
        if (null === ConfigQuery::read(static::RESOURCE_PATH_CONFIG_NAME)) {
            $config = new Config();

            $config
                ->setHidden(0)
                ->setSecured(0)
                ->setName(static::RESOURCE_PATH_CONFIG_NAME)
                ->setValue(__DIR__.DS."Resources")

                ->getTranslation("fr_FR")
                    ->setTitle("Chemin de templates pour Thelia studio")
                    ->getConfig()
                ->getTranslation("en_US")
                    ->setTitle("Templates path for Thelia studio")
            ;

            $config->save();
        }
    }
}
