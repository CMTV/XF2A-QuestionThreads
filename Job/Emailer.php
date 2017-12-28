<?php

namespace QuestionThreads\Job;

use QuestionThreads\NotificationHelper;
use XF\Entity\Post;
use XF\Entity\Thread;
use XF\Entity\User;
use XF\Job\AbstractJob;

class Emailer extends AbstractJob
{
    protected $defaultData = [
        'thread_id' =>          0,
        'actionCaller_id' =>    0,
        'alertType' =>          0,
        'batch' =>              5,
        'offset' =>             0
    ];

    public function run($maxRunTime)
    {
        if(!$this->data['alertType'])
        {
            return $this->complete();
        }

        $start = microtime(true);

        /** @var Thread $thread */
        $thread = \XF::finder('XF:Thread')->where('thread_id', $this->data['thread_id'])->fetchOne();

        /** @var User $actionCaller */
        $actionCaller = \XF::finder('XF:User')->where('user_id', $this->data['actionCaller_id'])->fetchOne();

        $db = $this->app->db();
        $query = "SELECT user_id FROM xf_thread_watch WHERE thread_id = ? AND email_subscribe = 1 ORDER BY user_id";

        $watchers = $db->fetchAll(
            $db->limit(
                $query,
                $this->data['batch'],
                $this->data['offset']
            ),
            $thread->thread_id
        );
        if(!$watchers)
        {
            return $this->complete();
        }

        foreach($watchers as $watcher)
        {
            if(microtime(true) - $start >= $maxRunTime)
            {
                break;
            }

            $this->data['offset']++;

            /** @var User $watcher */
            $watcher = \XF::finder('XF:User')->where('user_id', $watcher['user_id'])->fetchOne();

            switch($this->data['alertType']) {
                case NotificationHelper::THREAD_SOLVED:
                    NotificationHelper::emailThreadSolved($thread,$actionCaller,$watcher);
                    break;
                case NotificationHelper::THREAD_UNSOLVED:
                    NotificationHelper::emailThreadUnsolved($thread,$actionCaller,$watcher);
                    break;
                case NotificationHelper::THREAD_BEST_ANSWER_REMOVED:
                    /** @var Post $bestAnswer */
                    $bestAnswer = \XF::finder('XF:Post')->where('post_id', $this->data['bestPost_id'])->fetchOne();
                    NotificationHelper::emailRemoveBestAnswer($bestAnswer,$actionCaller, $watcher);
                    break;
                case NotificationHelper::POST_BEST_ANSWER:
                    $bestAnswerId = $thread->questionthreads_best_post;
                    /** @var Post $bestAnswer */
                    $bestAnswer = \XF::finder('XF:Post')->where('post_id', $bestAnswerId)->fetchOne();
                    NotificationHelper::emailMarkBestAnswer($bestAnswer, $actionCaller, $watcher);
                    break;
            }
        }

        $resume = $this->resume();
        $resume->continueDate = intval((new \DateTime('@' . \XF::$time))->modify('+1 minute')->format('U'));
        return $resume;
    }

    public function getStatusMessage()
    {
        return sprintf('QuestionThreads: Sending emails... %s sent', $this->data['offset']);
    }

    public function canCancel()
    {
        return true;
    }

    public function canTriggerByChoice()
    {
        return true;
    }
}