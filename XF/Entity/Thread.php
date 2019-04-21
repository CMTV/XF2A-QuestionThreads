<?php
/**
 * Question Threads xF2 addon by CMTV
 * Enjoy!
 */

namespace CMTV\QuestionThreads\XF\Entity;

use CMTV\QuestionThreads\Entity\BestAnswer;
use XF\Entity\Post;
use XF\Mvc\Entity\Structure;

use CMTV\QuestionThreads\Constants as C;

/**
 * COLUMNS
 * @property bool CMTV_QT_is_question
 * @property bool CMTV_QT_is_solved
 * @property int CMTV_QT_best_answer_id
 *
 * RELATIONS
 * @property BestAnswer BestAnswer
 */
class Thread extends XFCP_Thread
{
    public static function getStructure(Structure $structure)
    {
        $structure = parent::getStructure($structure);

        // Columns

        $structure->columns[C::_('is_question')] = [
            'type' => self::BOOL,
            'default' => false
        ];

        $structure->columns[C::_('is_solved')] = [
            'type' => self::BOOL,
            'default' => false
        ];

        $structure->columns[C::_('best_answer_id')] = [
            'type' => self::UINT,
            'default' => 0
        ];

        // Relations

        $structure->relations['BestAnswer'] = [
            'entity' => C::__('BestAnswer'),
            'type' => self::TO_ONE,
            'conditions' => [['best_answer_id', '=', '$' . C::_('best_answer_id')]],
            'primary' => true
        ];

        return $structure;
    }

    //************************* LIFE CYCLE ***************************

    protected function _postSave()
    {
        parent::_postSave();

        if ($bestAnswer = $this->BestAnswer)
        {
            $bestAnswer->is_counted = $this->CMTV_QT_is_question && $this->CMTV_QT_is_solved;
            $bestAnswer->save();
        }
    }

    protected function _postDelete()
    {
        parent::_postDelete();

        if ($bestAnswer = $this->BestAnswer)
        {
            $bestAnswer->delete();
        }
    }

    //************************* PERMISSIONS ***************************

    public function canMarkSolved()
    {
        return $this->canMarkGeneric('Solved');
    }

    public function canMarkUnsolved()
    {
        return $this->canMarkGeneric('Unsolved');
    }

    protected function canMarkGeneric(string $mark): bool
    {
        $visitor = \XF::visitor();

        if ($this->user_id === $visitor->user_id && $visitor->hasPermission(C::_(), 'markOwnQuestion' . $mark))
        {
            return true;
        }

        return $visitor->hasPermission(C::_(), 'markAnyQuestion' . $mark);
    }

    //************************* OTHER ***************************

    public function postRemoved(Post $post)
    {
        parent::postRemoved($post);

        if ($bestAnswer = $this->BestAnswer)
        {
            if ($post->post_id === $bestAnswer->post_id)
            {
                $bestAnswer->delete();
            }
        }
    }

    public function bestAnswerRemoved()
    {
        $this->CMTV_QT_best_answer_id = 0;
    }
}