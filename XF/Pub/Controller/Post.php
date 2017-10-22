<?php

namespace QuestionThreads\XF\Pub\Controller;

use XF\Mvc\ParameterBag;

class Post extends XFCP_Post
{
    /**
     * Mark thread as solved and current post as best answer
     *
     * @param ParameterBag $bg
     *
     * @throws \XF\Mvc\Reply\Exception
     */
    public function actionMarkBest(ParameterBag $bg)
    {
        /** @var \QuestionThreads\XF\Entity\Post $post */
        $post = $this->assertViewablePost($bg->post_id);

        /** @var \QuestionThreads\XF\Entity\Thread $thread */
        $thread = $post->Thread;

        if($post->canMarkBest($error))
        {
            if(!$thread->questionthreads_is_solved)
            {
                $thread->solve();
            }
            $post->markBest();

            return $this->redirect($this->buildLink('posts', $post));
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
}