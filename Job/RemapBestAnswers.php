<?php
/**
 * Question Threads xF2 addon by CMTV
 * Enjoy!
 */

namespace CMTV\QuestionThreads\Job;

use CMTV\QuestionThreads\Entity\BestAnswer;
use XF\Job\AbstractRebuildJob;

use CMTV\QuestionThreads\Constants as C;

class RemapBestAnswers extends AbstractRebuildJob
{
    protected function getNextIds($start, $batch)
    {
        $db = $this->app->db();

        return $db->fetchAllColumn(
            $db->limit("SELECT `best_answer_id` FROM `xf_cmtv_qt_best_answer` WHERE `best_answer_id` > ? ORDER BY `best_answer_id`", $batch),
            $start
        );
    }

    protected function rebuildById($id)
    {
        /** @var BestAnswer $bestAnswer */
        $bestAnswer = $this->app->finder(C::__('BestAnswer'))->whereId($id)->fetchOne();

        $postId = $bestAnswer->post_id;

        $this->app->db()->update('xf_thread', ['CMTV_QT_best_answer_id' => $id], 'CMTV_QT_best_answer_id = ?', [$postId]);
    }

    protected function getStatusType()
    {

    }
}