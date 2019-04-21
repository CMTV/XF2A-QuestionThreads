<?php
/**
 * Question Threads xF2 addon by CMTV
 * Enjoy!
 */

namespace CMTV\QuestionThreads\XF\Entity;

use CMTV\QuestionThreads\Constants as C;

class Post extends XFCP_Post
{
    //************************* PERMISSIONS ***************************

    public function canSelectBestAnswer()
    {
        $visitor = \XF::visitor();
        $thread = $this->Thread;

        if ($thread->user_id === $visitor->user_id && $visitor->hasPermission(C::_(), 'selectBestAnswerOwn'))
        {
            if ($this->user_id === $visitor->user_id)
            {
                if ($visitor->hasPermission(C::_(), 'selectBestAnswerOwnPost'))
                {
                    return true;
                }
            }
            else
            {
                return true;
            }
        }

        return $visitor->hasPermission(C::_(), 'selectBestAnswerAny');
    }

    public function canUnselectBestAnswer()
    {
        $visitor = \XF::visitor();
        $thread = $this->Thread;

        if ($thread->user_id === $visitor->user_id && $visitor->hasPermission(C::_(), 'unselectBestAnswerOwn'))
        {
            return true;
        }

        return $visitor->hasPermission(C::_(), 'unselectBestAnswerAny');
    }

    //************************* OTHER ***************************

    public function canDisplaySelectBestAnswer()
    {
        /** @var Thread $thread */
        $thread = $this->Thread;

        $canGeneric = $this->canDisplayGenericBestAnswer();

        return $canGeneric && !$thread->CMTV_QT_best_answer_id;
    }

    public function canDisplayUnselectBestAnswer()
    {
        /** @var Thread $thread */
        $thread = $this->Thread;

        $canGeneric = $this->canDisplayGenericBestAnswer();

        if ($bestAnswer = $thread->BestAnswer)
        {
            return $canGeneric && $thread->CMTV_QT_is_solved && ($this->post_id === $bestAnswer->post_id);
        }

        return false;
    }

    protected function canDisplayGenericBestAnswer()
    {
        /** @var Thread $thread */
        $thread = $this->Thread;

        if ($thread->isDeleted() || !$thread->isVisible())
        {
            return false;
        }

        if ($this->isDeleted() || !$this->isVisible())
        {
            return false;
        }

        if (!$thread->CMTV_QT_is_question)
        {
            return false;
        }

        if ($thread->first_post_id === $this->post_id)
        {
            return false;
        }

        return true;
    }

    public function isBestAnswer()
    {
        /** @var Thread $thread */
        $thread = $this->Thread;

        if ($bestAnswer = $thread->BestAnswer)
        {
            return $this->post_id === $bestAnswer->post_id;
        }

        return false;
    }
}