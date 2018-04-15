<?php
/**
 * Question Threads
 *
 * You CAN use/change/share this code.
 * Enjoy!
 *
 * Written by CMTV
 * Date: 07.03.2018
 * Time: 15:44
 */

namespace QuestionThreads\XF\Entity;

use QuestionThreads\Repository\BestAnswer;
use XF\Mvc\Entity\Structure;

/**
 * COLUMNS
 * @property bool QT_question
 * @property bool QT_solved
 * @property int QT_best_answer_id
 */
class Thread extends XFCP_Thread
{
    public static function getStructure(Structure $structure)
    {
        $structure = parent::getStructure($structure);

        $structure->columns['QT_question'] = [
            'type' => self::BOOL,
            'default' => false
        ];
        $structure->columns['QT_solved'] = [
            'type' => self::BOOL,
            'default' => false
        ];
        $structure->columns['QT_best_answer_id'] = [
            'type' => self::UINT,
            'default' => 0
        ];

        return $structure;
    }

    protected function _postDelete()
    {
        if($this->QT_best_answer_id)
        {
            $bestAnswer = $this->finder('QuestionThreads:BestAnswer')->where(['post_id' => $this->QT_best_answer_id])->fetchOne();

            if($bestAnswer)
            {
                $bestAnswer->delete();
            }
        }

        parent::_postDelete();
    }

    protected function threadHidden($hardDelete = false)
    {
        if($this->QT_best_answer_id)
        {
            /** @var \QuestionThreads\Entity\BestAnswer $bestAnswer */
            $bestAnswer = $this->finder('QuestionThreads:BestAnswer')->where(['post_id' => $this->QT_best_answer_id])->fetchOne();

            if($bestAnswer)
            {
                $bestAnswer->fastUpdate('is_counted', 0);

                /** @var BestAnswer $bestAnswerRepo */
                $bestAnswerRepo = $this->repository('QuestionThreads:BestAnswer');
                $bestAnswerRepo->adjustBestAnswers($bestAnswer->BestAnswerPoster);
            }
        }

        parent::threadHidden($hardDelete);
    }

    protected function threadMadeVisible()
    {
        if($this->QT_question && $this->QT_best_answer_id)
        {
            /** @var \QuestionThreads\Entity\BestAnswer $bestAnswer */
            $bestAnswer = $this->finder('QuestionThreads:BestAnswer')->where(['post_id' => $this->QT_best_answer_id])->fetchOne();

            if($bestAnswer)
            {
                $bestAnswer->fastUpdate('is_counted', 1);

                /** @var BestAnswer $bestAnswerRepo */
                $bestAnswerRepo = $this->repository('QuestionThreads:BestAnswer');
                $bestAnswerRepo->adjustBestAnswers($bestAnswer->BestAnswerPoster);
            }
        }

        parent::threadMadeVisible();
    }

    public function canEditType()
    {
        $visitor = \XF::visitor();

        if($visitor->hasPermission('forum', 'QT_editAnyThreadType'))
        {
            return true;
        }

        if(
            $visitor->hasPermission('forum', 'QT_editOwnThreadType')
            && $this->user_id === $visitor->user_id
            && $this->Forum->QT_type === Forum::QT_THREADS_QUESTIONS
        )
        {
            return true;
        }

        return false;
    }

    public function canMarkSolved()
    {
        $visitor = \XF::visitor();

        if($visitor->hasPermission('forum', 'QT_markAnySolved'))
        {
            return true;
        }

        if(
            $visitor->hasPermission('forum', 'QT_markOwnSolved')
            && $this->user_id === $visitor->user_id
        )
        {
            return true;
        }

        return false;
    }

    public function canMarkUnsolved()
    {
        $visitor = \XF::visitor();

        if($visitor->hasPermission('forum', 'QT_markAnyUnsolved'))
        {
            return true;
        }

        if(
            $visitor->hasPermission('forum', 'QT_markOwnUnsolved')
            && $this->user_id === $visitor->user_id
        )
        {
            return true;
        }

        return false;
    }

    public function canSelectBestAnswer(Post $post = null)
    {
        $visitor = \XF::visitor();

        if($visitor->hasPermission('forum', 'QT_selectBestAnswerAny'))
        {
            return true;
        }

        if(
            $visitor->hasPermission('forum', 'QT_selectBestAnswerOwn')
            && $this->user_id === $visitor->user_id
        )
        {
            if ($post && $post->user_id === $visitor->user_id)
            {
                return $visitor->hasPermission('forum', 'QT_selectBestAnswerOwn_');
            }

            return true;
        }

        return false;
    }

    public function canUnselectBestAnswer()
    {
        $visitor = \XF::visitor();

        if($visitor->hasPermission('forum', 'QT_unselectBestAnswerAny'))
        {
            return true;
        }

        if(
            $visitor->hasPermission('forum', 'QT_unselectBestAnswerOwn')
            && $this->user_id === $visitor->user_id
        )
        {
            return true;
        }

        return false;
    }
}