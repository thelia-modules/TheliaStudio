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

namespace {$moduleCode}\Form\Base;

use {$moduleCode}\{$moduleCode};
use Thelia\Form\BaseForm;
use {$moduleCode}\Model\Config\{$moduleCode}ConfigValue;
{assign hasLength 0}
{assign hasRegex 0}
{assign hasRequired 0}
{foreach from=$form item=field}
{if $field->isRequired() && !$hasRequired}
{assign hasRequired 1}
use Symfony\Component\Validator\Constraints\NotBlank;
{/if}
{if $field->hasSize() && !$hasLength}
{assign hasLength 1}
use Symfony\Component\Validator\Constraints\Length;
{/if}
{if $field->hasRegex() && !$hasRegex}
{assign hasRegex 1}
use Symfony\Component\Validator\Constraints\Regex;
{/if}
{/foreach}

/**
 * Class {$moduleCode}ConfigForm
 * @package {$moduleCode}\Form\Base
 * @author TheliaStudio
 */
class {$moduleCode}ConfigForm extends BaseForm
{
    const FORM_NAME = "{$moduleCode|lower}_config_form";

    /**
     *
     * in this function you add all the fields you need for your Form.
     * Form this you have to call add method on $this->formBuilder attribute :
     *
     * $this->formBuilder->add("name", "text")
     *   ->add("email", "email", array(
     *           "attr" => array(
     *               "class" => "field"
     *           ),
     *           "label" => "email",
     *           "constraints" => array(
     *               new \Symfony\Component\Validator\Constraints\NotBlank()
     *           )
     *       )
     *   )
     *   ->add('age', 'integer');
     *
     * @return null
     */
    protected function buildForm()
    {
        $translationKeys = $this->getTranslationKeys();
        $fieldsIdKeys = $this->getFieldsIdKeys();

{foreach from=$form item=field}
        $this->add{$field->getCamelizedName()}Field($translationKeys, $fieldsIdKeys);
{/foreach}
    }

{foreach from=$form item=field}
    protected function add{$field->getCamelizedName()}Field(array $translationKeys, array $fieldsIdKeys)
    {
        $this->formBuilder
            ->add("{$field->getName()}", "{$field->getRealType()}", array(
                "label" => $this->translator->trans($this->readKey("{$field->getName()}", $translationKeys), [], {$moduleCode}::MESSAGE_DOMAIN),
                "label_attr" => ["for" => $this->readKey("{$field->getName()}", $fieldsIdKeys)],
{if $field->getRealType() != 'checkbox' && $field->isRequired()}
                "required" => true,
{else}
                "required" => false,
{/if}
                "constraints" => array(
{if $field->getRealType() != 'checkbox' && $field->isRequired()}
                    new NotBlank(),
{/if}
{if $field->hasSize()}
                    new Length([{if $field->hasMinSize()}"min" => {$field->getMinSize()}, {/if}{if $field->hasMaxSize()}"max" => {$field->getMaxSize()}{/if}]),
{/if}
{if $field->hasRegex()}
                    new Regex(["pattern" => "{$field->getFormattedRegex()}"]),
{/if}
                ),
{if $field->getRealType() == 'checkbox'}
                "value" => {$moduleCode}::getConfigValue({$moduleCode}ConfigValue::{$field->getConstantName()}, false),
{else}
                "data" => {$moduleCode}::getConfigValue({$moduleCode}ConfigValue::{$field->getConstantName()}),
{/if}
            ))
        ;
    }
{/foreach}
    public function getName()
    {
        return static::FORM_NAME;
    }

    public function readKey($key, array $keys, $default = '')
    {
        if (isset($keys[$key])) {
            return $keys[$key];
        }

        return $default;
    }

    public function getTranslationKeys()
    {
        return array();
    }

    public function getFieldsIdKeys()
    {
        return array(
{foreach from=$form item=field}
            "{$field->getName()}" => "{$field->getName()}",
{/foreach}
        );
    }
}
