<?php
/**
 * Question Threads
 *
 * You CAN use/change/share this code.
 * Enjoy!
 *
 * Written by CMTV
 * Date: 06.03.2018
 * Time: 12:28
 */

namespace QuestionThreads\XF\Entity;

use XF\Mvc\Entity\Structure;

/**
 * COLUMNS
 * @property string QT_type
 */
class Forum extends XFCP_Forum
{
    const QT_THREADS_ONLY = 'threads_only';
    const QT_THREADS_QUESTIONS = 'threads_questions';
    const QT_QUESTIONS_ONLY = 'questions_only';

    public static function getStructure(Structure $structure)
    {
        $structure = parent::getStructure($structure);

        $structure->columns['QT_type'] = [
            'type' => self::STR,
            'default' => self::QT_THREADS_ONLY,
            'allowedValues' => [self::QT_THREADS_ONLY, self::QT_THREADS_QUESTIONS, self::QT_QUESTIONS_ONLY]
        ];

        return $structure;
    }

    public function canCreateQuestion()
    {
        if($this->QT_type === self::QT_THREADS_ONLY)
        {
            return false;
        }

        if(!\XF::visitor()->hasNodePermission($this->node_id, 'QT_createQuestion'))
        {
            return false;
        }

        return true;
    }
}