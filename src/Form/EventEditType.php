<?php

/**
 * EventEdit type.
 */

namespace App\Form;

/**
 * Class EventEditType.
 */
class EventEditType extends EventType
{
    /**
     * Returns the prefix of the template block name for this type.
     *
     * The block prefix defaults to the underscored short class name with
     * the "Type" suffix removed (e.g. "UserProfileType" => "user_profile").
     *
     * @return string The prefix of the template block name
     */
    public function getBlockPrefix(): string
    {
        return 'event_edit';
    }
}
