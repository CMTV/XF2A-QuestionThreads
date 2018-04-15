<?php
/**
 * Question Threads
 *
 * You CAN use/change/share this code.
 * Enjoy!
 *
 * Written by CMTV
 * Date: 13.03.2018
 * Time: 12:47
 */

namespace QuestionThreads\XF\Criteria;

class User extends XFCP_User
{
    protected function _matchQTQuestionsPosted(array $data, \XF\Entity\User $user)
    {
        $query = "SELECT COUNT(*) FROM `xf_thread` WHERE `user_id` = ? AND `QT_question` = 1";
        $questions = $this->app->db()->fetchOne($query, [$user->user_id]);

        return ($questions && $questions >= $data['questions']);
    }

    protected function _matchQTQuestionsMaximum(array $data, \XF\Entity\User $user)
    {
        $query = "SELECT COUNT(*) FROM `xf_thread` WHERE `user_id` = ? AND `QT_question` = 1";
        $questions = $this->app->db()->fetchOne($query, [$user->user_id]);

        return ($questions <= $data['questions']);
    }

    protected function _matchQTBestAnswerCount(array $data, \XF\Entity\User $user)
    {
        $query = "SELECT COUNT(*) FROM `xf_QT_best_answer` WHERE `post_user_id` = ? AND `is_counted` = 1";
        $bestAnswers = $this->app->db()->fetchOne($query, [$user->user_id]);

        return ($bestAnswers && $bestAnswers >= $data['best_answers']);
    }

    protected function _matchQTBestAnswerRatio(array $data, \XF\Entity\User $user)
    {
        $queryBestAnswers = "SELECT COUNT(*) FROM `xf_QT_best_answer` WHERE `post_user_id` = ? AND `is_counted` = 1";
        $bestAnswers = $this->app->db()->fetchOne($queryBestAnswers, [$user->user_id]);

        $queryQuestions = "
            SELECT COUNT(DISTINCT `xf_post`.`thread_id`) FROM `xf_post`
            JOIN `xf_thread` ON `xf_post`.`thread_id` = `xf_thread`.`thread_id`
            WHERE `xf_post`.`user_id` = ? AND `xf_thread`.`QT_question` = 1";
        $questions = $this->app->db()->fetchOne($queryQuestions, [$user->user_id]);

        if(!$bestAnswers || !$questions)
        {
            return false;
        }

        $ratio = $bestAnswers / $questions;
        return ($ratio >= $data['ratio']);
    }
}