<?php
/**
 * Question Threads xF2 addon by CMTV
 * Enjoy!
 */

namespace CMTV\QuestionThreads\Job;

use CMTV\QuestionThreads\XF\Entity\Post;
use CMTV\QuestionThreads\XF\Entity\Thread;
use CMTV\QuestionThreads\XF\Entity\User;
use XF\Entity\ThreadWatch;
use XF\Job\AbstractRebuildJob;
use XF\Repository\UserAlert;

class AlertWatchers extends AbstractRebuildJob
{
    protected $defaultData = [
        'thread_id' => null,
        'sender' => null,
        'contentType' => null,
        'contentId' => null,
        'action' => null,
        'email_template' => null,
        'extra_data' => null
    ];

    protected function getNextIds($start, $batch)
    {
        $db = $this->app->db();

        $result = $db->fetchAllColumn($db->limit(
            "SELECT `user_id` FROM `xf_thread_watch` WHERE `user_id` > ? AND `thread_id` = ? ORDER BY `thread_id`", $batch),
            [$start, $this->data['thread_id']]
        );

        return $result;
    }

    protected function rebuildById($id)
    {
        if ($id === $this->data['sender'])
        {
            return;
        }

        /** @var User $sender */
        $sender = $this->app->finder('XF:User')->whereId($this->data['sender'])->fetchOne();

        /** @var User $receiver */
        $receiver = $this->app->finder('XF:User')->whereId($id)->fetchOne();

        /** @var Thread $thread */
        $thread = $this->app->finder('XF:Thread')->whereId($this->data['thread_id'])->fetchOne();

        /** @var Post $post */
        $post = $this->app->finder('XF:Post')->whereId($this->data['post_id'])->fetchOne();

        /** @var UserAlert $alertRepo */
        $alertRepo = \XF::repository('XF:UserAlert');
        $alertRepo->alertFromUser(
            $receiver, $sender,
            $this->data['contentType'],
            $this->data['contentId'],
            $this->data['action']
        );

        /** @var ThreadWatch $threadWatch */
        $threadWatch = $this->app->finder('XF:ThreadWatch')->where(['user_id' => $id, 'thread_id' => $this->data['thread_id']])->fetchOne();

        if ($threadWatch && $threadWatch->email_subscribe)
        {
            $params = [
                'sender' => $sender,
                'receiver' => $receiver,
                'thread' => $thread,
                'post' => $post,
                'forum' => $thread->Forum
            ];

            $this->app->mailer()->newMail()
                ->setToUser($receiver)
                ->setTemplate($this->data['email_template'], $params)
                ->queue();
        }
    }

    protected function getStatusType() {}
}