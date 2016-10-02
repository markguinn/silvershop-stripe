<?php

/**
 * Encapsulates a stripe custom field with a data-stripe attribute and name and value suppressed.
 *
 * @author  Mark Guinn <markguinn@gmail.com>
 * @date    9.24.2016
 * @package silvershop-stripe
 */
class StripeField extends TextField
{
    protected $extraClasses = ['text'];

    /**
     * @param string      $name
     * @param null|string $title
     * @param string      $value
     * @param int|null    $maxLength
     * @param Form|null   $form
     */
    public function __construct($name, $title = null, $value = '', $maxLength = null, $form = null)
    {
        parent::__construct($name, $title, $value, $maxLength, $form);
    }

    /**
     * @return array
     */
    public function getAttributes()
    {
        $attributes = parent::getAttributes();
        $attributes['data-stripe'] = $attributes['name'];
        unset($attributes['name']);
        unset($attributes['value']);
        return $attributes;
    }
}
