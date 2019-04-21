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
 * @property int CMTV_QT_best_answer_count
 */
class User extends XFCP_User
{
    public static function getStructure(Structure $structure)
    {
        $structure = parent::getStructure($structure);

        // Columns

        $structure->columns[C::_('best_answer_count')] = [
            'type' => self::UINT,
            'default' => 0,
            'changeLog' => false,
            'api' => true
        ];

        return $structure;
    }

    //************************* OTHER ***************************

    public function bestAnswersDecrease()
    {
        if ($this->CMTV_QT_best_answer_count > 0)
        {
            $this->CMTV_QT_best_answer_count--;
            $this->save();
        }
    }

    public function bestAnswersIncrease()
    {
        $this->CMTV_QT_best_answer_count++;
        $this->save();
    }
}