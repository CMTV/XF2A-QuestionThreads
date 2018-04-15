<?php
/**
 * Question Threads
 *
 * You CAN use/change/share this code.
 * Enjoy!
 *
 * Written by CMTV
 * Date: 14.03.2018
 * Time: 19:15
 */

namespace QuestionThreads\Job;

use QuestionThreads\Repository\Thread;
use XF\Job\AbstractRebuildJob;

class ConvertForumThreads extends AbstractRebuildJob
{
    protected $defaultData = [
        'forum_id' => null,
        'to' => null
    ];

    protected function setupData(array $data)
    {
        $this->defaultData['forum_id'] = $data['forum_id'];
        $this->defaultData['to'] = $data['to'];

        return parent::setupData($data);
    }

    protected function getNextIds($start, $batch)
    {
        $db = $this->app->db();

        return $db->fetchAllColumn($db->limit("
                SELECT `thread_id`
                FROM `xf_thread`
                WHERE `thread_id` > ? AND `node_id` = ?
                ORDER BY `thread_id`
        ", $batch), [$start, $this->defaultData['forum_id']]);
    }

    protected function rebuildById($id)
    {
        /** @var \XF\Entity\Thread $thread */
        $thread = $this->app->finder('XF:Thread')->whereId($id)->fetchOne();

        /** @var Thread $threadRepo */
        $threadRepo = $this->app->repository('QuestionThreads:Thread');

        switch($this->defaultData['to'])
        {
            case 'threads':
                $threadRepo->convertQuestionToThread($thread);
                break;
            case 'questions':
                $threadRepo->convertThreadToQuestion($thread);
                break;
        }
    }

    protected function getStatusType()
    {
        return \XF::phrase('threads');
    }

    public function getStatusMessage()
    {
        if($this->defaultData['to'] === 'thread')
        {
            $actionPhrase = \XF::phrase('QT_converting_to_threads');
        }
        else
        {
            $actionPhrase = \XF::phrase('QT_converting_to_questions');
        }

        $typePhrase = $this->getStatusType();
        return sprintf('%s... %s (%s)', $actionPhrase, $typePhrase, $this->data['start']);
    }
}