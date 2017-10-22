<?php

namespace QuestionThreads\XF\Entity;

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

        /* Sending an alert to best answer author */
        $visitor = \XF::visitor();
        /** @var \XF\Entity\User $bestAnswerPoster */
        $bestAnswerPoster = \XF::finder('XF:User')->where('user_id', $this->user_id)->fetchOne();
        if($visitor->user_id !== $this->user_id)
        {

            $this->alertBestAnswerPoster($bestAnswerPoster, $visitor);
        }
    }

    /**
     * Send an alert to user who wrote the best answer
     *
     * @param User $bestAnswerPoster User who wrote the best answer
     * @param User $solver User who marked the answer as best
     */
    public function alertBestAnswerPoster(User $bestAnswerPoster, User $solver)
    {
        /** @var UserAlert $alertRep */
        $alertRep = \XF::app()->repository('XF:UserAlert');
        $alertRep->alert($bestAnswerPoster, $solver->user_id, $solver->username, 'post', $this->Thread->questionthreads_best_post, 'questionthreads_best_post');
    }
}