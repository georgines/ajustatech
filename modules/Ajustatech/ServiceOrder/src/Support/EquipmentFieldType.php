<?php

namespace Ajustatech\ServiceOrder\Support;

final class EquipmentFieldType
{
    public const PHOTO = 'photo';
    public const TEXT = 'text';
    public const SELECT = 'select';
    public const RADIO = 'radio';
    public const FILE = 'file';
    public const DOCUMENT = 'document';

    public static function values(): array
    {
        return [
            self::PHOTO,
            self::TEXT,
            self::SELECT,
            self::RADIO,
            self::FILE,
            self::DOCUMENT,
        ];
    }

    public static function acceptsOptions(string $fieldType): bool
    {
        return in_array($fieldType, [self::SELECT, self::RADIO], true);
    }

    public static function isAttachment(string $fieldType): bool
    {
        return in_array($fieldType, [self::PHOTO, self::FILE], true);
    }
}

