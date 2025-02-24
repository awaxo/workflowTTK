<?php

namespace App\Enums;

enum LegalRelationship: string
{
    case EMPLOYEE = 'munkavállaló';
    case VOLUNTEER = 'önkéntes';
    case CONTRACTOR = 'megbízás';

    /**
     * Get all values as an array
     *
     * @return array<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get all cases as an associative array
     *
     * @return array<string, string>
     */
    public static function toArray(): array
    {
        return array_combine(
            array_column(self::cases(), 'name'),
            array_column(self::cases(), 'value')
        );
    }
}