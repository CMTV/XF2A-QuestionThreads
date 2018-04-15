<?php
/**
 * Question Threads
 *
 * You CAN use/change/share this code.
 * Enjoy!
 *
 * Written by CMTV
 * Date: 12.03.2018
 * Time: 9:57
 */

namespace QuestionThreads\XF\Entity;

use XF\Mvc\Entity\Structure;

/**
 * COLUMNS
 * @property int QT_best_answer_count
 */
class User extends XFCP_User
{
    public static function getStructure(Structure $structure)
    {
        $structure = parent::getStructure($structure);

        $structure->columns['QT_best_answer_count'] = [
            'type' => self::UINT,
            'default' => 0,
        ];

        return $structure;
    }
}