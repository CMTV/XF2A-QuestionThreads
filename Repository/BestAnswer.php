<?php
/**
 * Question Threads
 *
 * You CAN use/change/share this code.
 * Enjoy!
 *
 * Written by CMTV
 * Date: 12.03.2018
 * Time: 9:41
 */

namespace QuestionThreads\Repository;

use XF\Entity\User;
use XF\Entity\Post;
use XF\Mvc\Entity\Finder;
use XF\Mvc\Entity\Repository;

class BestAnswer extends Repository
{
    public function getBestAnswer($post_id)
    {
        return $this->finder('QuestionThreads:BestAnswer')->where(['post_id' => $post_id])->fetchOne();
    }

    public function toggleBestAnswer(Post $post)
    {
        $bestAnswer = $this->getBestAnswer($post->post_id);

        if(!$bestAnswer)
        {
            $this->selectBestAnswer($post);
            return true;
        }
        else
        {
            $this->unselectBestAnswer($post);
            return false;
        }
    }

    public function selectBestAnswer(Post $post)
    {
        $thread = $post->Thread;
        $thread->fastUpdate('QT_solved', true);
        $thread->fastUpdate('QT_best_answer_id', $post->post_id);

        /** @var BestAnswer $bestAnswer */
        $bestAnswer = $this->em->create('QuestionThreads:BestAnswer');
        $bestAnswer->post_id = $post->post_id;
        $bestAnswer->post_user_id = $post->user_id;
        $bestAnswer->thread_id = $post->Thread->thread_id;
        $bestAnswer->thread_user_id = $post->Thread->user_id;
        $bestAnswer->is_counted = true;
        $bestAnswer->save();

        $post->User->fastUpdate('QT_best_answer_count', $this->countBestAnswers($post->User));
    }

    public function unselectBestAnswer(Post $post)
    {
        $thread = $post->Thread;
        $thread->fastUpdate('QT_best_answer_id', 0);

        $bestAnswer = $this->getBestAnswer($post->post_id);

        if($bestAnswer)
        {
            $bestAnswer->delete();
        }

        $post->User->fastUpdate('QT_best_answer_count', $this->countBestAnswers($post->User));
    }

    public function removeBestAnswerFromThread(\QuestionThreads\XF\Entity\Thread $thread)
    {
        if($thread->QT_best_answer_id)
        {
            /** @var Post $post */
            $post = $this->finder('XF:Post')->whereId($thread->QT_best_answer_id)->fetchOne();

            $this->unselectBestAnswer($post);
        }
    }

    public function countBestAnswers(User $user)
    {
        $query = "SELECT COUNT(*) FROM `xf_QT_best_answer` WHERE `post_user_id` = ? AND `is_counted` = 1";

        $bestAnswers = $this->db()->fetchOne($query, $user->user_id);

        return $bestAnswers;
    }

    public function adjustBestAnswers(User $user)
    {
        $user->fastUpdate('QT_best_answer_count', $this->countBestAnswers($user));
    }

    public function findUserBestAnswers($user_id)
    {
        if($user_id instanceof \XF\Entity\User)
        {
            $user_id = $user_id->user_id;
        }

        /** @var Finder $finder */
        $finder = $this->finder('QuestionThreads:BestAnswer')
            ->where(['post_user_id' => $user_id, 'is_counted' => 1])
            ->setDefaultOrder('best_answer_id', 'DESC');

        return $finder;
    }
}