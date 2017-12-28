<?php

namespace QuestionThreads\Job;

use XF\Entity\Thread;
use XF\Job\AbstractJob;

/**
 * Converting non-question threads to questions when marking existing forum as questions only
 *
 * @package QuestionThreads\Job
 */
class QuestionsForumConverter extends AbstractJob
{
    protected $defaultData = [
        'forum_id' =>   0,
        'batch' =>      10,
        'offset' =>     0
    ];

    public function run($maxRunTime)
    {
        if(!$this->data['forum_id'])
        {
            return $this->complete();
        }

        $start = microtime(true);

        $db = $this->app->db();
        $query = "SELECT thread_id FROM xf_thread WHERE node_id = ? ORDER BY thread_id";

        $threads = $db->fetchAll(
            $db->limit(
                $query,
                $this->data['batch'],
                $this->data['offset']
            ),
            $this->data['forum_id']
        );
        if(!$threads)
        {
            return $this->complete();
        }

        foreach($threads as $thread)
        {
            if(microtime(true) - $start >= $maxRunTime)
            {
                break;
            }

            $this->data['offset']++;

            /** @var Thread $thread */
            $thread = \XF::finder('XF:Thread')->where('thread_id', $thread['thread_id'])->fetchOne();

            if(!$thread->questionthreads_is_question)
            {
                $thread->questionthreads_is_question = true;
                $thread->save();
            }
        }

        return $this->resume();
    }

    public function getStatusMessage()
    {
        return sprintf(\XF::phrase('questionthreads_converting_to_questions', ['convertedNumber' => $this->data['offset']]));
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