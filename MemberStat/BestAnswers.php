<?php
/**
 * Question Threads
 *
 * You CAN use/change/share this code.
 * Enjoy!
 *
 * Written by CMTV
 * Date: 15.03.2018
 * Time: 12:59
 */

namespace QuestionThreads\MemberStat;

class BestAnswers
{
    public static function getBestAnswerUsers(\XF\Entity\MemberStat $memberStat, \XF\Finder\User $finder)
    {
        $finder->order('QT_best_answer_count', 'DESC');

        $users = $finder->where('QT_best_answer_count', '>', 0)->limit($memberStat->user_limit)->fetch();

        $results = $users->pluck(function(\XF\Entity\User $user)
        {
            return [$user->user_id, \XF::language()->numberFormat($user->QT_best_answer_count)];
        });

        return $results;
    }
}