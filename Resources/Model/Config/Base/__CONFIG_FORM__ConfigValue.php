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

namespace {$moduleCode}\Model\Config\Base;

/**
 * Class {$moduleCode}ConfigValue
 * @package {$moduleCode}\Model\Config\Base
 */
class {$moduleCode}ConfigValue
{
{foreach from=$form item=field}
    const {$field->getConstantName()} = "{$field->getName()}";
{/foreach}
}

