<?php
/**
 * Question Threads xF2 addon by CMTV
 * Enjoy!
 */

namespace CMTV\QuestionThreads\XF\Entity;

use XF\Mvc\Entity\Structure;

use CMTV\QuestionThreads\Constants as C;

/**
 * COLUMNS
 * @property string CMTV_QT_type
 */
class Forum extends XFCP_Forum
{
    public static function getStructure(Structure $structure)
    {
        $structure = parent::getStructure($structure);

        // Columns

        $structure->columns[C::_('type')] = [
            'type' => self::STR,
            'default' => 'threads_only',
            'allowedValues' => ['threads_only', 'questions_only', 'both']
        ];

        // Getters

        $structure->getters['type_phrase'] = true;

        return $structure;
    }

    //************************* OTHER ***************************

    public function getTypePhrase()
    {
        switch ($this->CMTV_QT_type)
        {
            case 'threads_only':
                return \XF::phrase(C::_('threads_only'));
            case 'both':
                return \XF::phrase(C::_('threads_and_questions'));
            case 'questions_only':
                return \XF::phrase(C::_('questions_only'));
        }
    }
}