<?php
/**
 * Question Threads xF2 addon by CMTV
 * Enjoy!
 */

namespace CMTV\QuestionThreads\Job;

use CMTV\QuestionThreads\XF\Entity\User;
use XF\Job\AbstractRebuildJob;

use CMTV\QuestionThreads\Constants as C;

class BestAnswerCount extends AbstractRebuildJob
{
    protected function getNextIds($start, $batch)
    {
        $db = $this->app->db();

        return $db->fetchAllColumn(
            $db->limit("SELECT `user_id` FROM `xf_user` WHERE `user_id` > ? ORDER BY `user_id`", $batch),
            $start
        );
    }

    protected function rebuildById($id)
    {
        $db = $this->app->db();

        /** @var User $user */
        $user = $this->app->finder('XF:User')->whereId($id)->fetchOne();

        $bestAnswerCount = $db->fetchOne(
            "SELECT COUNT(*) FROM `xf_" . C::_('best_answer') . "` WHERE `post_user_id` = ? AND `is_counted` = 1",
            $id
        );

        $user->set(C::_('best_answer_count'), $bestAnswerCount);
        $user->save();
    }

    protected function getStatusType()
    {
        return \XF::phrase('users');
    }
}