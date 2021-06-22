<?php
{include "../../includes/header.php"}

namespace {$moduleCode}\Form\Transformers;

use Symfony\Component\Form\DataTransformerInterface;

/**
 * A simple DataTransformer to set the default value of form fields to empty string ('') instead of null.
 * This data transformer is used on all text or textarea fields.
 */
class NullToEmptyTransformer implements DataTransformerInterface
{
    /**
     * Does not transform anything
     *
     * @param  string|null $value
     * @return string
     */
    public function transform($value)
    {
        return $value;
    }

    /**
     * Transforms a null to an empty string.
     *
     * @param  string $value
     * @return string
     */
    public function reverseTransform($value)
    {
        if (is_null($value)) {
            return '';
        }

        return $value;
    }
}
