<?php
/**
 * Question Threads xF2 addon by CMTV
 * Enjoy!
 */

namespace CMTV\QuestionThreads\XF\Pub\Controller;

use CMTV\QuestionThreads\Entity\BestAnswer;
use XF\Mvc\ParameterBag;

use CMTV\QuestionThreads\Constants as C;

class Post extends XFCP_Post
{
    public function actionSelectBestAnswer(ParameterBag $params)
    {
        /** @var \CMTV\QuestionThreads\XF\Entity\Post $post */
        $post = $this->assertViewablePost($params->post_id);

        /** @var \CMTV\QuestionThreads\XF\Entity\Thread $thread */
        $thread = $post->Thread;

        if (!$post->canSelectBestAnswer())
        {
            return $this->noPermission();
        }

        if ($thread->isDeleted() || !$thread->isVisible() || $post->isDeleted() || !$post->isVisible())
        {
            return $this->error(\XF::phrase(C::_('thread_post_deleted_not_visible')));
        }

        if (!$thread->CMTV_QT_is_question)
        {
            return $this->error(\XF::phrase(C::_('only_questions_can_have_a_best_answer')));
        }

        if ($thread->first_post_id === $post->post_id)
        {
            return $this->error(\XF::phrase(C::_('first_question_post_cant_be_the_best_answer')));
        }

        if ($thread->BestAnswer)
        {
            return $this->error(\XF::phrase(C::_('this_question_already_has_the_best_answer')));
        }

        //
        // Creating and saving new best answer entity
        //

        /** @var BestAnswer $bestAnswer */
        $bestAnswer = $this->em()->create(C::__('BestAnswer'));

        $bestAnswer->bulkSet([
            'post_id' => $post->post_id,
            'post_user_id' => $post->user_id,
            'thread_id' => $thread->thread_id,
            'thread_user_id' => $thread->user_id
        ]);

        if (!$bestAnswer->preSave())
        {
            return $this->error($bestAnswer->getErrors());
        }

        $bestAnswer->save();

        //
        // Updating thread entity
        //

        $thread->bulkSet([
            C::_('is_solved') => true,
            C::_('best_answer_id') => $bestAnswer->best_answer_id
        ]);

        if (!$thread->preSave())
        {
            return $this->error($thread->getErrors());
        }

        $thread->save();

        //
        // Alerting watchers and creating news feed entry
        //

        $bestAnswerRepo = $this->getBestAnswerRepo();

        $bestAnswerRepo->publishBestAnswerNewsFeed(\XF::visitor(), $post);
        $bestAnswerRepo->alertWatchers(\XF::visitor(), $post);

        return $this->redirect($this->buildLink('posts', $post));
    }

    public function actionUnselectBestAnswer(ParameterBag $params)
    {
        /** @var \CMTV\QuestionThreads\XF\Entity\Post $post */
        $post = $this->assertViewablePost($params->post_id);

        /** @var \CMTV\QuestionThreads\XF\Entity\Thread $thread */
        $thread = $post->Thread;

        if (!$post->canUnselectBestAnswer())
        {
            return $this->noPermission();
        }

        if (!($bestAnswer = $thread->BestAnswer))
        {
            return $this->error(\XF::phrase(C::_('cant_unselect_this_post_is_not_the_best_answer')));
        }

        $bestAnswer->delete();

        $thread->CMTV_QT_best_answer_id = 0;

        if (!$thread->preSave())
        {
            return $this->error($thread->getErrors());
        }

        $thread->save();

        $bestAnswerRepo = $this->getBestAnswerRepo();
        $bestAnswerRepo->unpublishBestAnswerNewsFeed($post);

        return $this->redirect($this->buildLink('posts', $post));
    }

    /**
     * @return \CMTV\QuestionThreads\Repository\BestAnswer
     */
    protected function getBestAnswerRepo()
    {
        return $this->repository(C::__('BestAnswer'));
    }
}