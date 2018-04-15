<?php
/**
 * Question Threads
 *
 * You CAN use/change/share this code.
 * Enjoy!
 *
 * Written by CMTV
 * Date: 10.03.2018
 * Time: 14:24
 */

namespace QuestionThreads\XF\Pub\Controller;

use QuestionThreads\Repository\BestAnswer;
use XF\Mvc\ParameterBag;
use XF\Service\Thread\Editor;

class Thread extends XFCP_Thread
{
    public function actionBestAnswer(ParameterBag $params)
    {
        /** @var \QuestionThreads\XF\Entity\Thread $thread */
        $thread = $this->assertViewableThread($params->thread_id);

        if($thread->QT_best_answer_id)
        {
            $bestAnswer = $this->finder('XF:Post')->whereId($thread->QT_best_answer_id)->fetchOne();

            return $this->redirect($this->plugin('XF:Thread')->getPostLink($bestAnswer));
        }
        else
        {
            return $this->redirect($this->buildLink('threads', $thread));
        }
    }

    public function actionMarkSolved(ParameterBag $params)
    {
        /** @var \QuestionThreads\XF\Entity\Thread $thread */
        $thread = $this->assertViewableThread($params->thread_id);

        if(!$thread->canMarkSolved())
        {
            return $this->noPermission();
        }

        $thread->fastUpdate('QT_solved', true);

        // Alerting watchers
        $data = [
            'thread_id' => $thread->thread_id,
            'post_id' => $thread->first_post_id,
            'sender' => \XF::visitor()->user_id,
            'contentType' => 'thread',
            'contentId' => $thread->thread_id,
            'action' => 'QT_solved',
            'email_template' => 'QT_question_solved'
        ];

        $this->app()->jobManager()->enqueue('QuestionThreads:AlertWatchers', $data);

        return $this->redirect($this->buildLink('threads', $thread), \XF::phrase('QT_question_marked_solved'));
    }

    public function actionMarkUnsolved(ParameterBag $params)
    {
        /** @var \QuestionThreads\XF\Entity\Thread $thread */
        $thread = $this->assertViewableThread($params->thread_id);

        $thread->fastUpdate('QT_solved', false);

        if($thread->QT_best_answer_id)
        {
            $bestAnswer = $this->finder('XF:Post')->whereId($thread->QT_best_answer_id)->fetchOne();

            /** @var BestAnswer $bestAnswersRepo */
            $bestAnswersRepo = $this->repository('QuestionThreads:BestAnswer');
            $bestAnswersRepo->unselectBestAnswer($bestAnswer);
        }

        return $this->redirect($this->buildLink('threads', $thread), \XF::phrase('QT_question_marked_unsolved'));
    }

    protected function setupThreadEdit(\XF\Entity\Thread $thread)
    {
        /** @var Editor $editor */
        $editor = parent::setupThreadEdit($thread);

        /** @var \QuestionThreads\XF\Entity\Thread $thread */
        $thread = $editor->getThread();

        if($thread->canEditType())
        {
            if($thread->QT_question && !$this->filter('QT_question', 'bool'))
            {
                /** @var \QuestionThreads\Repository\Thread $threadRepo */
                $threadRepo = $this->repository('QuestionThreads:Thread');
                $threadRepo->convertQuestionToThread($thread);
            }

            if(!$thread->QT_question && $this->filter('QT_question', 'bool'))
            {
                /** @var \QuestionThreads\Repository\Thread $threadRepo */
                $threadRepo = $this->repository('QuestionThreads:Thread');
                $threadRepo->convertThreadToQuestion($thread);
            }

            $thread->QT_question = $this->filter('QT_question', 'bool');
        }

        return $editor;
    }
}