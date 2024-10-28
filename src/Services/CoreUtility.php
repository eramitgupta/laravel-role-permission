<?php

namespace EragPermission\Services;

class CoreUtility
{
    public static function stringArray(string|array $inputString): array
    {
        if (is_array($inputString)) {
            return $inputString;
        }

        return array_map('trim', preg_split('/[,|]/', $inputString));
    }
}
