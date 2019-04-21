<?php
/**
 * Question Threads xF2 addon by CMTV
 * Enjoy!
 */

namespace CMTV\QuestionThreads\Job;

use CMTV\QuestionThreads\XF\Entity\Thread;
use XF\Job\AbstractRebuildJob;

use CMTV\QuestionThreads\Constants as C;

class ConvertForumThreads extends AbstractRebuildJob
{
    protected $defaultData = [
        'forum_id' => null,
        'type' => null
    ];

    protected function getNextIds($start, $batch)
    {
        $db = $this->app->db();

        $result = $db->fetchAllColumn(
            $db->limit("SELECT `thread_id` FROM `xf_thread` WHERE `thread_id` > ? AND `node_id` = ? ORDER BY `thread_id`", $batch),
            [$start, $this->data['forum_id']]
        );

        return $result;
    }

    protected function rebuildById($id)
    {
        /** @var Thread $thread */
        $thread = $this->app->finder('XF:Thread')->whereId($id)->fetchOne();
        $thread->CMTV_QT_is_question = $this->data['type'] == 'questions_only';
        $thread->save();
    }

    protected function getStatusType()
    {
        $phrase = $this->data['type'] == 'threads_only' ? 'questions_to_threads' : 'threads_to_questions';

        return \XF::phrase(C::_($phrase));
    }
}