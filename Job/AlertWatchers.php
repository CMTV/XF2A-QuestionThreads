<?php
/**
 * Question Threads
 *
 * You CAN use/change/share this code.
 * Enjoy!
 *
 * Written by CMTV
 * Date: 15.03.2018
 * Time: 16:24
 */

namespace QuestionThreads\Job;

use XF\Entity\Post;
use XF\Entity\Thread;
use XF\Entity\ThreadWatch;
use XF\Entity\User;
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

    protected function setupData(array $data)
    {
        $this->defaultData['thread_id'] = $data['thread_id'];
        $this->defaultData['post_id'] = $data['post_id'];
        $this->defaultData['sender'] = $data['sender'];
        $this->defaultData['contentType'] = $data['contentType'];
        $this->defaultData['contentId'] = $data['contentId'];
        $this->defaultData['action'] = $data['action'];
        $this->defaultData['email_template'] = $data['email_template'];

        return parent::setupData($data);
    }

    protected function getNextIds($start, $batch)
    {
        $db = $this->app->db();

        return $db->fetchAllColumn($db->limit("
                SELECT `user_id`
                FROM `xf_thread_watch`
                WHERE `user_id` > ? AND `thread_id` = ?
                ORDER BY `thread_id`
        ", $batch), [$start, $this->defaultData['thread_id']]);
    }

    protected function rebuildById($id)
    {
        if($id === $this->defaultData['sender'])
        {
            return;
        }

        /** @var User $sender */
        $sender = $this->app->finder('XF:User')->whereId($this->defaultData['sender'])->fetchOne();

        /** @var User $user */
        $receiver = $this->app->finder('XF:User')->whereId($id)->fetchOne();

        /** @var Thread $thread */
        $thread = $this->app->finder('XF:Thread')->whereId($this->defaultData['thread_id'])->fetchOne();

        /** @var Post $post */
        $post = $this->app->finder('XF:Post')->whereId($this->defaultData['post_id'])->fetchOne();

        /** @var UserAlert $alertRepo */
        $alertRepo = \XF::repository('XF:UserAlert');
        $alertRepo->alertFromUser(
            $receiver, $sender,
            $this->defaultData['contentType'],
            $this->defaultData['contentId'],
            $this->defaultData['action']
        );

        /** @var ThreadWatch $threadWatch */
        $threadWatch = $this->app->finder('XF:ThreadWatch')->where(['user_id' => $id, 'thread_id' => $this->defaultData['thread_id']])->fetchOne();
        if($threadWatch)
        {
            if($threadWatch->email_subscribe)
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
                    ->setTemplate($this->defaultData['email_template'], $params)
                    ->queue();
            }
        }
    }

    protected function getStatusType()
    {

    }

    public function getStatusMessage()
    {

    }
}