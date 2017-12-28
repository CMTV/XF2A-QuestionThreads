<?php

namespace QuestionThreads\XF\Pub\Controller;

use QuestionThreads\XF\Service\Thread\Editor;
use XF\Mvc\Entity\Entity;
use XF\Mvc\ParameterBag;

class Thread extends XFCP_Thread
{
    /**
     * Mark current thread as solved
     *
     * @param ParameterBag $pb
     *
     * @return \XF\Mvc\Reply\Redirect
     *
     * @throws \XF\Mvc\Reply\Exception
     */
    public function actionSolve(ParameterBag $pb)
    {
        /** @var \QuestionThreads\XF\Entity\Thread $thread */
        $thread = $this->assertViewableThread($pb->thread_id);

        if($thread->canSolve($error))
        {
            $thread->solve();
            return $this->redirectPermanently($this->buildLink('threads', $thread), ['page' => $pb->page]);
        }
        else
        {
            if($error === 403)
            {
                throw $this->exception($this->noPermission());
            }
            else if($error)
            {
                throw $this->exception($this->error($error));
            }
        }
    }

    /**
     * Mark current thread as unsolved
     *
     * @param ParameterBag $pb
     *
     * @return \XF\Mvc\Reply\Redirect
     *
     * @throws \XF\Mvc\Reply\Exception
     */
    public function actionUnsolve(ParameterBag $pb)
    {
        /** @var \QuestionThreads\XF\Entity\Thread $thread */
        $thread = $this->assertViewableThread($pb->thread_id);

        if($thread->canUnsolve($error))
        {
            $thread->unsolve();
            return $this->redirectPermanently($this->buildLink('threads', $thread), ['page' => $pb->page]);
        }
        else
        {
            if($error === 403)
            {
                throw $this->exception($this->noPermission());
            }
            else if($error)
            {
                throw $this->exception($this->error($error));
            }
        }
    }

    /**
     * Remove best answer mark
     *
     * @param ParameterBag $bg
     * @return \XF\Mvc\Reply\Redirect
     *
     * @throws \XF\Mvc\Reply\Exception
     */
    public function actionRemoveBest(ParameterBag $bg)
    {
        /** @var \QuestionThreads\XF\Entity\Thread $thread */
        $thread = $this->assertViewableThread($bg->thread_id);

        if($thread->canRemoveBest($error))
        {
            $bestAnswer = \XF::finder('XF:Post')->where('post_id', $thread->questionthreads_best_post)->fetchOne();

            $thread->removeBest();

            return $this->redirect($this->buildLink('posts', $bestAnswer));
        }
        else
        {
            if($error === 403)
            {
                throw $this->exception($this->noPermission());
            }
            else if($error)
            {
                throw $this->exception($this->error($error));
            }
        }
    }

    /**
     * Convert thread to question is checkbox "Is question" is selected
     *
     * @param \XF\Entity\Thread $thread
     * @return Editor
     */
    public function setupThreadEdit(\XF\Entity\Thread $thread)
    {
        /** @var Editor $editor */
        $editor = parent::setupThreadEdit($thread);

        if($this->filter('questionthreads_is_question', 'bool') || $thread->Forum->questionthreads_forum)
        {
            $editor->isQuestionThread = true;
        }

        return $editor;
    }
}