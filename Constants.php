<?php
/**
 * Question Threads xF2 addon by CMTV
 * Enjoy!
 */

namespace CMTV\QuestionThreads;

use CMTV\QuestionThreads\Constants as C;

class Constants
{
    const ADDON_ID = [
        'CMTV',
        'QuestionThreads'
    ];

    const ADDON_ID_SHORT = [
        'CMTV',
        'QT'
    ];

    public static function _(string $content = ''): string
    {
        $shortId = implode('_', self::ADDON_ID_SHORT);

        return $shortId . (empty($content) ? '' : '_' . $content);
    }

    public static function __(string $content): string
    {
        return implode('\\', self::ADDON_ID) . ':' . $content;
    }
}