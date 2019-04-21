<?php
/**
 * Question Threads xF2 addon by CMTV
 * Enjoy!
 */

namespace CMTV\QuestionThreads\XF\Pub\Controller;

use CMTV\QuestionThreads\XF\Entity\Thread;

use CMTV\QuestionThreads\Constants as C;

class Forum extends XFCP_Forum
{
    protected function setupThreadCreate(\XF\Entity\Forum $forum)
    {
        $creator = parent::setupThreadCreate($forum);

        /** @var Thread $thread */
        $thread = $creator->getThread();

        switch ($forum->get(C::_('type')))
        {
            case 'questions_only':
                $thread->CMTV_QT_is_question = true;
                break;
            case 'both':
                $thread->CMTV_QT_is_question = $this->filter(C::_('is_question'), 'bool');
                break;
            case 'threads_only':
                $thread->CMTV_QT_is_question = false;
                break;
        }

        return $creator;
    }
}