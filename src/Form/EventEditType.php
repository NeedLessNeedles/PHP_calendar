<?php

namespace App\Form;

class EventEditType extends EventType
{
    public function getBlockPrefix(): string
    {
        return 'event_edit';
    }
}
