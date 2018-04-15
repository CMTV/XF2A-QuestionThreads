<?php
/**
 * Question Threads
 *
 * You CAN use/change/share this code.
 * Enjoy!
 *
 * Written by CMTV
 * Date: 14.03.2018
 * Time: 16:12
 */

namespace QuestionThreads\Job;

use QuestionThreads\Repository\BestAnswer;
use QuestionThreads\XF\Entity\Post;
use QuestionThreads\XF\Entity\Thread;
use QuestionThreads\XF\Entity\User;
use XF\Job\AbstractRebuildJob;

class RebuildBestAnswerCounts extends AbstractRebuildJob
{
    protected function getNextIds($start, $batch)
    {
        $db = $this->app->db();

        return $db->fetchAllColumn($db->limit("
                SELECT `thread_id`
                FROM `xf_thread`
                WHERE `thread_id` > ? AND `QT_best_answer_id` > 0
                ORDER BY `thread_id`
        ", $batch), $start);
    }

    protected function rebuildById($id)
    {
        /** @var Thread $thread */
        $thread = $this->app->finder('XF:Thread')->whereId($id)->fetchOne();

        /** @var Post $bestAnswer */
        $bestAnswer = $this->app->finder('XF:Post')->whereId($thread->QT_best_answer_id)->fetchOne();

        $bestAnswerEntity = $this->app->finder('QuestionThreads:BestAnswer')->where(['post_id' => $thread->QT_best_answer_id])->fetchOne();
        if(!$bestAnswerEntity)
        {
            /** @var BestAnswer $bestAnswer */
            $newBestAnswer = $this->app->em()->create('QuestionThreads:BestAnswer');
            $newBestAnswer->post_id = $bestAnswer->post_id;
            $newBestAnswer->post_user_id = $bestAnswer->user_id;
            $newBestAnswer->thread_id = $thread->thread_id;
            $newBestAnswer->thread_user_id = $thread->user_id;
            $newBestAnswer->is_counted = true;
            $newBestAnswer->save();
        }

        if($bestAnswer->User)
        {
            /** @var BestAnswer $bestAnswerRepo */
            $bestAnswerRepo = $this->app->repository('QuestionThreads:BestAnswer');
            $bestAnswerRepo->adjustBestAnswers($bestAnswer->User);
        }
    }

    protected function getStatusType()
    {
        return \XF::phrase('threads');
    }

    public function getStatusMessage()
    {
        $actionPhrase = \XF::phrase('QT_rebuilding_best_answer_counts');
        $typePhrase = $this->getStatusType();
        return sprintf('%s... %s (%s)', $actionPhrase, $typePhrase, $this->data['start']);
    }
}