<?php
/**
 * Question Threads xF2 addon by CMTV
 * Enjoy!
 */

namespace CMTV\QuestionThreads\Repository;

use CMTV\QuestionThreads\XF\Entity\Thread;
use XF\Entity\Post;
use XF\Entity\User;
use XF\Mvc\Entity\Finder;
use XF\Mvc\Entity\Repository;
use XF\Repository\NewsFeed;

use CMTV\QuestionThreads\Constants as C;

class BestAnswer extends Repository
{
    public function findMemberBestAnswers(User $user): Finder
    {
        return $this->finder(C::__('BestAnswer'))
            ->where('post_user_id', $user->user_id)
            ->where('is_counted', true);
    }

    public function publishBestAnswerNewsFeed(User $user, Post $post)
    {
        $newsFeedRepo = $this->getNewsFeedRepo();
        $newsFeedRepo->publish(
            'post',
            $post->post_id,
            'best_answer',
            $user->user_id,
            $user->username
        );
    }

    public function unpublishBestAnswerNewsFeed(Post $post)
    {
        $newsFeedRepo = $this->getNewsFeedRepo();
        $newsFeedRepo->unpublish(
            'post',
            $post->post_id,
            null,
            'best_answer'
        );
    }

    public function alertWatchers(User $sender, Post $post)
    {
        $data = [
            'thread_id' => $post->Thread->thread_id,
            'post_id' => $post->post_id,
            'sender' => $sender->user_id,
            'contentType' => 'post',
            'contentId' => $post->post_id,
            'action' => 'best_answer_selected',
            'email_template' => C::_('best_answer_selected')
        ];

        $this->app()->jobManager()->enqueue(C::__('AlertWatchers'), $data);
    }

    /**
     * @return NewsFeed
     */
    protected function getNewsFeedRepo()
    {
        return $this->repository('XF:NewsFeed');
    }
}