<?php

namespace QuestionThreads\XF\Entity;

use QuestionThreads\NotificationHelper;
use XF\Entity\User;
use XF\Repository\UserAlert;

class Thread extends XFCP_Thread
{
    /**
     * Can current thread be solved
     *
     * @param null $error
     * @return bool
     */
    public function canSolve(&$error = null)
    {
        $visitor = \XF::visitor();

        /* If this thread is question thread */
        if(!$this->questionthreads_is_question)
        {
            $error = \XF::phrase('questionthreads_not_a_question');
            return false;
        }

        /* Question is already solved */
        if($this->questionthreads_is_solved)
        {
            $error = \XF::phrase('questionthreads_already_solved');
            return false;
        }

        /* Visitor and question author are the same */
        if ($visitor->user_id === $this->user_id)
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
     * Can current thread be unsolved
     *
     * @param null $error
     * @return bool
     */
    public function canUnsolve(&$error = null)
    {
        $visitor = \XF::visitor();

        /* If this thread is question thread */
        if(!$this->questionthreads_is_question)
        {
            $error = \XF::phrase('questionthreads_not_a_question');
            return false;
        }

        /* Question is already unsolved */
        if(!$this->questionthreads_is_solved)
        {
            $error = \XF::phrase('questionthreads_already_unsolved');
            return false;
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
     * Can remove "Best answer" mark from answer
     *
     * @param null $error
     * @return bool
     */
    public function canRemoveBest(&$error = null)
    {
        $visitor = \XF::visitor();

        /* If this thread is question thread */
        if(!$this->questionthreads_is_question)
        {
            $error = \XF::phrase('questionthreads_not_a_question');
            return false;
        }

        /* If there current question already has best answer */
        if(!$this->questionthreads_best_post)
        {
            $error = \XF::phrase('questionthreads_no_best_post');
            return false;
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
     * Marking current thread as solved
     */
    public function solve()
    {
        $this->questionthreads_is_solved = true;
        $this->save();

        $jobParams = [
            'thread_id' => $this->thread_id,
            'actionCaller_id' => \XF::visitor()->user_id,
            'alertType' => NotificationHelper::THREAD_SOLVED
        ];
        \XF::app()->jobManager()->enqueueUnique('solvedAlerting_' . time(), 'QuestionThreads:Alerter', $jobParams);
        \XF::app()->jobManager()->enqueueUnique('solvedEmailing_' . time(), 'QuestionThreads:Emailer', $jobParams);
    }

    /**
     * Marking current thread as unsolved
     */
    public function unsolve()
    {
        $this->questionthreads_is_solved = false;
        $this->questionthreads_best_post = 0;
        $this->save();

        $jobParams = [
            'thread_id' => $this->thread_id,
            'actionCaller_id' => \XF::visitor()->user_id,
            'alertType' => NotificationHelper::THREAD_UNSOLVED
        ];
        \XF::app()->jobManager()->enqueueUnique('unsolvedAlerting_' . time(), 'QuestionThreads:Alerter', $jobParams);
        \XF::app()->jobManager()->enqueueUnique('unsolvedEmailing_' . time(), 'QuestionThreads:Emailer', $jobParams);
    }

    /**
     * Removing "Best answer" mark from answer
     */
    public function removeBest()
    {
        $jobParams = [
            'thread_id' => $this->thread_id,
            'actionCaller_id' => \XF::visitor()->user_id,
            'bestPost_id' => $this->questionthreads_best_post,
            'alertType' => NotificationHelper::THREAD_BEST_ANSWER_REMOVED
        ];
        \XF::app()->jobManager()->enqueueUnique('bestRemovedAlerting_' . time(), 'QuestionThreads:Alerter', $jobParams);
        \XF::app()->jobManager()->enqueueUnique('bestRemovedEmailing_' . time(), 'QuestionThreads:Emailer', $jobParams);

        /* Removing best answer mark */
        $this->questionthreads_best_post = 0;
        $this->save();
    }
}