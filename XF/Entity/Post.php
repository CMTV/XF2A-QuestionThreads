<?php

namespace QuestionThreads\XF\Entity;

use QuestionThreads\NotificationHelper;
use XF\Entity\User;
use XF\Repository\UserAlert;

class Post extends XFCP_Post
{
    /**
     * Can mark current post as best answer
     *
     * @param null $error
     * @return bool
     */
    public function canMarkBest(&$error = null)
    {
        $visitor = \XF::visitor();
        $thread = $this->Thread;

        /* If this thread is question thread */
        if(!$thread->questionthreads_is_question)
        {
            $error = \XF::phrase('questionthreads_not_a_question');
            return false;
        }

        /* If first post in thread */
        if($thread->first_post_id === $this->post_id)
        {
            $error = \XF::phrase('questionthreads_first_post_cant_be_best');
            return false;
        }

        /* If there current question already has best answer */
        if($thread->questionthreads_best_post !== 0)
        {
            $error = \XF::phrase('questionthreads_already_has_best_post');
            return false;
        }

        /* Visitor and question author are the same */
        if($thread->user_id === $visitor->user_id)
        {
            return true;
        }

        /* Visitor has permission */
        if($visitor->hasPermission('forum', 'questionthreads_solve'))
        {
            return true;
        }
        else
        {
            $error = 403;
            return false;
        }
    }

    /**
     * Marking current post as best answer for this question
     */
    public function markBest()
    {
        $thread = $this->Thread;
        $thread->questionthreads_best_post = $this->post_id;
        $thread->save();

        $jobParams = [
            'thread_id' => $this->thread_id,
            'actionCaller_id' => \XF::visitor()->user_id,
            'alertType' => NotificationHelper::POST_BEST_ANSWER
        ];
        \XF::app()->jobManager()->enqueueUnique('bestMarkedAlerting_' . time(), 'QuestionThreads:Alerter', $jobParams);

        /*$this->app()->mailer()->newMail()
            ->setToUser($user)
            ->setTemplate($template, $params)
            ->queue();*/

        \XF::app()->jobManager()->enqueueUnique('bestMarkedEmailing_' . time(), 'QuestionThreads:Emailer', $jobParams);
    }
}