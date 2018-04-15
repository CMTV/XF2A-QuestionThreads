<?php
/**
 * Question Threads
 *
 * You CAN use/change/share this code.
 * Enjoy!
 *
 * Written by CMTV
 * Date: 06.03.2018
 * Time: 15:12
 */

namespace QuestionThreads\Repository;

use XF\Mvc\Entity\Repository;

class Forum extends Repository
{
    public function convertToThreadsOnly(\XF\Entity\Forum $forum)
    {
        \XF::app()->jobManager()->enqueueUnique('QT_convertation','QuestionThreads:ConvertForumThreads', [
            'forum_id' => $forum->node_id,
            'to' => 'threads'
        ], true);
    }

    public function convertToQuestionsOnly(\XF\Entity\Forum $forum)
    {
        \XF::app()->jobManager()->enqueueUnique('QT_convertation', 'QuestionThreads:ConvertForumThreads', [
            'forum_id' => $forum->node_id,
            'to' => 'questions'
        ], true);
    }
}