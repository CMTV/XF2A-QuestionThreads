<?php
/**
 * Question Threads xF2 addon by CMTV
 * Enjoy!
 */

namespace CMTV\QuestionThreads\XF\Service\Post;

use CMTV\QuestionThreads\XF\Entity\Thread;

use CMTV\QuestionThreads\Constants as C;

class Mover extends XFCP_Mover
{
    protected function updateSourceData()
    {
        parent::updateSourceData();

        /** @var Thread $sourceThread */
        foreach ($this->sourceThreads as $sourceThread)
        {
            if ($bestAnswer = $sourceThread->BestAnswer)
            {
                if (array_key_exists($bestAnswer->post_id, $this->sourcePosts))
                {
                    $bestAnswer->delete();
                }
            }
        }
    }
}