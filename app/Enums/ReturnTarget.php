<?php

namespace App\Enums;

enum ReturnTarget: string
{
    case CreatorEntity = 'creator_entity';
    case PreviousStep = 'previous_step';

    public function label(): string
    {
        return match ($this) {
            self::CreatorEntity => 'إرجاع للجهة المنشئة (المسودة)',
            self::PreviousStep => 'إرجاع للمرحلة السابقة مباشرة',
        };
    }
}
