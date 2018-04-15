<?php
/**
 * Question Threads
 *
 * You CAN use/change/share this code.
 * Enjoy!
 *
 * Written by CMTV
 * Date: 02.04.2018
 * Time: 12:10
 */

namespace QuestionThreads\XF\InlineMod\Thread;

use QuestionThreads\XF\Entity\Thread;
use XF\Mvc\Entity\AbstractCollection;

class Merge extends XFCP_Merge
{
    protected function canApplyInternal(AbstractCollection $entities, array $options, &$error)
    {
        $bestAnswers = 0;

        /** @var Thread $thread */
        foreach($entities as $thread)
        {
            if($thread->QT_question && $thread->QT_best_answer_id)
            {
                $bestAnswers++;
            }
        }

        if($bestAnswers >= 2)
        {
            return false;
        }

        return parent::canApplyInternal($entities, $options, $error);
    }
}