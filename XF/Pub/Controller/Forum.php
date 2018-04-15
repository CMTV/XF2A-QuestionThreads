<?php
/**
 * Question Threads
 *
 * You CAN use/change/share this code.
 * Enjoy!
 *
 * Written by CMTV
 * Date: 08.03.2018
 * Time: 11:37
 */

namespace QuestionThreads\XF\Pub\Controller;

use XF\Mvc\ParameterBag;

class Forum extends XFCP_Forum
{
    public function actionPostThread(ParameterBag $params)
    {
        if (!$params->node_id && !$params->node_name)
        {
            return $this->rerouteController('XF:Forum', 'postThreadChooser');
        }

        /** @var \QuestionThreads\XF\Entity\Forum $forum */
        $forum = $this->assertViewableForum($params->node_id ?: $params->node_name, ['DraftThreads|' . \XF::visitor()->user_id]);

        if ($this->isPost())
        {
            if(
                $forum->QT_type === \QuestionThreads\XF\Entity\Forum::QT_QUESTIONS_ONLY
                ||
                $this->filter('QT_question', 'bool') == true
            )
            {
                if(!$forum->canCreateQuestion())
                {
                    return $this->error(\XF::phrase('QT_create_question_no_permission'));
                }
            }
        }

        return parent::actionPostThread($params);
    }

    protected function setupThreadCreate(\XF\Entity\Forum $forum)
    {
        $creator = parent::setupThreadCreate($forum);

        $thread = $creator->getThread();

        switch($forum->QT_type)
        {
            case \QuestionThreads\XF\Entity\Forum::QT_QUESTIONS_ONLY:
                $thread->QT_question = 1;
                break;
            case \QuestionThreads\XF\Entity\Forum::QT_THREADS_QUESTIONS:
                $thread->QT_question = $this->filter('QT_question', 'bool');
                break;
            case \QuestionThreads\XF\Entity\Forum::QT_THREADS_ONLY:
            default:
                $thread->QT_question = 0;
                break;
        }

        return $creator;
    }
}