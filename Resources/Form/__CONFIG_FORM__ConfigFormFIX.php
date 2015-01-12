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

namespace {$moduleCode}\Form;

use {$moduleCode}\Form\Base\{$moduleCode}ConfigForm as Base{$moduleCode}ConfigForm;

/**
 * Class {$moduleCode}ConfigForm
 * @package {$moduleCode}\Form\Base
 */
class {$moduleCode}ConfigForm extends Base{$moduleCode}ConfigForm
{
    public function getTranslationKeys()
    {
        return array(
{foreach from=$form item=field}
            "{$field->getName()}" => "{$field->getName()|ucfirst|replace:'_':' '}",
{/foreach}
        );
    }
}
