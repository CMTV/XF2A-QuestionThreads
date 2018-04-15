<?php
/**
 * Question Threads
 *
 * You CAN use/change/share this code.
 * Enjoy!
 *
 * Written by CMTV
 * Date: 11.03.2018
 * Time: 15:36
 */

namespace QuestionThreads\XF\Pub\Controller;

use QuestionThreads\Repository\BestAnswer;
use XF\Mvc\ParameterBag;
use XF\Service\Thread\Editor;

class Post extends XFCP_Post
{
    protected function setupFirstPostThreadEdit(\XF\Entity\Thread $thread, &$threadChanges)
    {
        /** @var Editor $threadEditor */
        $threadEditor = parent::setupFirstPostThreadEdit($thread, $threadChanges);

        /** @var \QuestionThreads\XF\Entity\Thread $thread */
        $thread = $threadEditor->getThread();

        if($thread->canEditType())
        {
            $thread->QT_question = $this->filter('QT_question', 'bool');

            $threadChanges['QT_question'] = $thread->isChanged('QT_question');
        }

        return $threadEditor;
    }

    public function actionSelectBestAnswer(ParameterBag $params)
    {
        $post = $this->assertViewablePost($params->post_id);
        /** @var \QuestionThreads\XF\Entity\Thread $thread */
        $thread = $post->Thread;

        if(!$thread->QT_question)
        {
            return $this->error(\XF::phrase('QT_this_thread_is_not_a_question'));
        }

        if($post->isFirstPost())
        {
            return $this->error(\XF::phrase('QT_first_post_cant_be_best_answer'));
        }

        if($thread->QT_best_answer_id)
        {
            return $this->error(\XF::phrase('QT_there_is_a_best_answer_already'));
        }

        if(!$thread->canSelectBestAnswer($post))
        {
            return $this->noPermission();
        }

        /** @var BestAnswer $bestAnswerRepo */
        $bestAnswerRepo = $this->repository('QuestionThreads:BestAnswer');
        $bestAnswerRepo->selectBestAnswer($post);

        // Alerting watchers
        $data = [
            'thread_id' => $thread->thread_id,
            'post_id' => $thread->QT_best_answer_id,
            'sender' => \XF::visitor()->user_id,
            'contentType' => 'post',
            'contentId' => $post->post_id,
            'action' => 'QT_best_answer_selected',
            'email_template' => 'QT_best_answer_selected'
        ];

        $this->app()->jobManager()->enqueue('QuestionThreads:AlertWatchers', $data);

        return $this->redirect($this->plugin('XF:Thread')->getPostLink($post), \XF::phrase('QT_best_answer_successfully_selected'));
    }

    public function actionUnselectBestAnswer(ParameterBag $params)
    {
        $post = $this->assertViewablePost($params->post_id);
        /** @var \QuestionThreads\XF\Entity\Thread $thread */
        $thread = $post->Thread;

        if(!$thread->QT_question)
        {
            return $this->error(\XF::phrase('QT_this_thread_is_not_a_question'));
        }

        if($post->post_id !== $thread->QT_best_answer_id)
        {
            return $this->error(\XF::phrase('QT_this_post_is_not_the_best_answer'));
        }

        if(!$thread->canUnselectBestAnswer())
        {
            return $this->noPermission();
        }

        /** @var BestAnswer $bestAnswerRepo */
        $bestAnswerRepo = $this->repository('QuestionThreads:BestAnswer');
        $bestAnswerRepo->unselectBestAnswer($post);

        return $this->redirect($this->plugin('XF:Thread')->getPostLink($post), \XF::phrase('QT_best_answer_successfully_unselected'));
    }
}