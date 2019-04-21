<?php
/**
 * Question Threads xF2 addon by CMTV
 * Enjoy!
 */

namespace CMTV\QuestionThreads;

use XF\Entity\User;

use CMTV\QuestionThreads\Constants as C;

class EventListener
{
    public static function criteriaUser($rule, array $data, User $user, &$returnValue)
    {
        switch ($rule)
        {
            // Questions

            case C::_('questions_asked'):
                $db = \XF::db();
                $questions = $db->fetchOne(
                    "SELECT COUNT(*) FROM `xf_thread` WHERE `CMTV_QT_is_question` = 1 AND `user_id` = ?",
                    $user->user_id
                );

                $returnValue = $questions >= $data['questions'];
                break;
            case C::_('questions_maximum'):
                $db = \XF::db();
                $questions = $db->fetchOne(
                    "SELECT COUNT(*) FROM `xf_thread` WHERE `CMTV_QT_is_question` = 1 AND `user_id` = ?",
                    $user->user_id
                );

                $returnValue = $questions <= $data['questions'];
                break;

            // Best answers

            case C::_('best_answers_posted'):
                $returnValue = $user->get(C::_('best_answer_count')) >= $data['best_answers'];
                break;
            case C::_('best_answers_maximum'):
                $returnValue = $user->get(C::_('best_answer_count')) <= $data['best_answers'];
                break;
        }
    }
}