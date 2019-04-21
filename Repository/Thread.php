<?php
/**
 * Question Threads xF2 addon by CMTV
 * Enjoy!
 */

namespace CMTV\QuestionThreads\Repository;

use XF\Entity\User;
use XF\Mvc\Entity\Repository;
use XF\Repository\NewsFeed;

use CMTV\QuestionThreads\Constants as C;

class Thread extends Repository
{
    public function publishMarkedSolved(User $user, \XF\Entity\Thread $thread)
    {
        $newsFeedRepo = $this->getNewsFeedRepo();
        $newsFeedRepo->publish(
            'thread',
            $thread->thread_id,
            'marked_solved',
            $user->user_id,
            $user->username
        );
    }

    public function unpublishMarkedSolved(\XF\Entity\Thread $thread)
    {
        $newsFeedRepo = $this->getNewsFeedRepo();
        $newsFeedRepo->unpublish(
            'thread',
            $thread->thread_id,
            null,
            'marked_solved'
        );
    }

    public function alertWatchers(User $user, \XF\Entity\Thread $thread)
    {
        $data = [
            'thread_id' => $thread->thread_id,
            'post_id' => $thread->first_post_id,
            'sender' => $user->user_id,
            'contentType' => 'thread',
            'contentId' => $thread->thread_id,
            'action' => 'marked_solved',
            'email_template' => C::_('question_marked_solved')
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