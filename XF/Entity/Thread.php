<?php

namespace QuestionThreads\XF\Entity;

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

        /* Sending an alert to question author if his question was marked as solved by someone else */
        $visitor = \XF::visitor();
        if($visitor->user_id !== $this->user_id)
        {
            $this->alertSolved($visitor);
        }
    }

    /**
     * Marking current thread as unsolved
     */
    public function unsolve()
    {
        $this->questionthreads_is_solved = false;
        $this->questionthreads_best_post = 0;
        $this->save();

        /* Sending an alert to question author if his question was marked as unsolved by someone else */
        $visitor = \XF::visitor();
        if($visitor->user_id !== $this->user_id)
        {
            $this->alertUnsolved($visitor);
        }
    }

    /**
     * Removing "Best answer" mark from answer
     */
    public function removeBest()
    {
        /* Sending an alert to best answer author saying that his answer is no longer the best one */
        $visitor = \XF::visitor();
        /** @var \XF\Entity\Post $bestAnswer */
        $bestAnswer = \XF::finder('XF:Post')->where('post_id', $this->questionthreads_best_post)->fetchOne();
        if($visitor->user_id !== $bestAnswer->user_id)
        {
            /** @var \XF\Entity\User $bestAnswerPoster */
            $bestAnswerPoster = \XF::finder('XF:User')->where('user_id', $bestAnswer->user_id)->fetchOne();
            $this->alertRemoveBestAnswerPoster($bestAnswerPoster, $visitor);
        }

        /* Sending an alert to question author saying that best answer mark was removed */
        if($visitor->user_id !== $this->user_id)
        {
            $this->alertQuestionAuthorRemoveBestAnswer();
        }

        /* Removing best answer mark */
        $this->questionthreads_best_post = 0;
        $this->save();
    }

    /**
     * Send an alert to question author if his question was marked as solved by someone else
     *
     * @param User $solver User who marked question as solved
     */
    public function alertSolved(User $solver)
    {
        /** @var User $questionAuthor */
        $questionAuthor = \XF::finder('XF:User')->where('user_id', $this->user_id)->fetchOne();

        /** @var UserAlert $alertRep */
        $alertRep = \XF::app()->repository('XF:UserAlert');
        $alertRep->alert($questionAuthor, $solver->user_id, $solver->username, 'thread', $this->thread_id, 'questionthreads_solved');
    }

    /**
     * Send an alert to question author if his question was marked as unsolved by someone else
     *
     * @param User $unsolver
     */
    public function alertUnsolved(User $unsolver)
    {
        /** @var User $questionAuthor */
        $questionAuthor = \XF::finder('XF:User')->where('user_id', $this->user_id)->fetchOne();

        /** @var UserAlert $alertRep */
        $alertRep = \XF::app()->repository('XF:UserAlert');
        $alertRep->alert($questionAuthor, $unsolver->user_id, $unsolver->username, 'thread', $this->thread_id, 'questionthreads_unsolved');

    }

    /**
     * Alerting user that his answer is no longer the best one
     *
     * @param User $bestAnswerPoster
     * @param User $actionCaller
     */
    public function alertRemoveBestAnswerPoster(User $bestAnswerPoster, User $actionCaller)
    {
        /** @var UserAlert $alertRep */
        $alertRep = \XF::app()->repository('XF:UserAlert');
        $alertRep->alert($bestAnswerPoster, $actionCaller->user_id, $actionCaller->username, 'post', $this->questionthreads_best_post, 'questionthreads_remove_best');
    }

    /**
     * Alerting question author that best answer mark was removed by someone else
     */
    public function alertQuestionAuthorRemoveBestAnswer()
    {
        $actionCaller = \XF::visitor();
        /** @var \XF\Entity\User $questionAuthor */
        $questionAuthor = \XF::finder('XF:User')->where('user_id', $this->user_id)->fetchOne();

        /** @var UserAlert $alertRep */
        $alertRep = \XF::app()->repository('XF:UserAlert');
        $alertRep->alert($questionAuthor, $actionCaller->user_id, $actionCaller->username, 'post', $this->questionthreads_best_post, 'questionthreads_remove_best_a');
    }
}