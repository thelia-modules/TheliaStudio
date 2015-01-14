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

namespace TheliaStudio\Generator;

use TheliaStudio\Events\ModuleGenerateEvent;
use Symfony\Component\Finder\Finder;

/**
 * Class RawCopyGenerator
 * @package TheliaStudio\Generator
 * @author Benjamin Perche <bperche9@gmail.com>
 */
class RawCopyGenerator extends BaseGenerator
{
    public function generate(ModuleGenerateEvent $event)
    {
        /** @var \SplFileInfo $file */
        foreach (Finder::create()->files()->in($event->getResourcesPath()."raw-copy") as $file) {
            $relativePath = $relativePath = str_replace($event->getResourcesPath()."raw-copy", "", $file->getRealPath());
            $completePath = $event->getModulePath().$relativePath;

            @copy($file->getRealPath(), $completePath);
        }
    }

    public function getName()
    {
        return "copy";
    }
}
