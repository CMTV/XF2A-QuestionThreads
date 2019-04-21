<?php
/**
 * Question Threads xF2 addon by CMTV
 * Enjoy!
 */

namespace CMTV\QuestionThreads\MemberStat;

use XF\Entity\MemberStat;
use XF\Finder\User;

use CMTV\QuestionThreads\Constants as C;

class BestAnswer
{
    public static function getBestAnswerUsers(MemberStat $memberStat, User $finder)
    {
        $finder->order(C::_('best_answer_count'), 'DESC');

        $users = $finder->where(C::_('best_answer_count'), '>', 0)->limit($memberStat->user_limit)->fetch();

        $results = $users->pluck(function (\XF\Entity\User $user)
        {
            return [$user->user_id, \XF::language()->numberFormat($user->get(C::_('best_answer_count')))];
        });

        return $results;
    }
}