<?php

namespace App\Helpers;

class CompanyAssetHelper
{
    /**
     * Map PT names in database to their respective file slugs and extensions.
     */
    protected static $logoMapping = [
        'BAHARI'   => 'bahari-logo.jpg',
        'ELI'      => 'eli-logo.jpg',
        'KHARISMA' => 'kharisma-logo.jpeg',
        'PESONA'   => 'pesona-logo.png',
        'SIGAP'    => 'sigap-logo.jpg',
        'TRIGUNA'  => 'triguna-logo.png',
        'TES'      => 'eli-logo.jpg', // Map test to eli for now
        // Fallback or missing ones will return null
    ];

    protected static $stampMapping = [
        'ELI'      => 'eli-stamp.png',
        'KHARISMA' => 'kharisma-stamp.png',
        'PESONA'   => 'pesona-stamp.png',
        'SIGAP'    => 'sigap-stamp.png',
        'TRIGUNA'  => 'triguna-stamp.png',
        'TES'      => 'eli-stamp.png', // Map test to eli for now
    ];

    /**
     * Get logo filename based on PT Name.
     */
    public static function getLogo(string $ptName): ?string
    {
        $ptName = strtoupper(trim($ptName));
        return self::$logoMapping[$ptName] ?? null;
    }

    /**
     * Get stamp filename based on PT Name.
     */
    public static function getStamp(string $ptName): ?string
    {
        $ptName = strtoupper(trim($ptName));
        return self::$stampMapping[$ptName] ?? null;
    }

    public static function getLogoMapping(): array
    {
        return self::$logoMapping;
    }

    public static function getStampMapping(): array
    {
        return self::$stampMapping;
    }
}
