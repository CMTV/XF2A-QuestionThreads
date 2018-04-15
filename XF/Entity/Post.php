<?php
/**
 * Question Threads
 *
 * You CAN use/change/share this code.
 * Enjoy!
 *
 * Written by CMTV
 * Date: 14.03.2018
 * Time: 9:46
 */

namespace QuestionThreads\XF\Entity;

use QuestionThreads\Entity\BestAnswer;

class Post extends XFCP_Post
{
    protected function _postDelete()
    {
        /** @var Thread $thread */
        $thread = $this->Thread;

        if($thread->QT_best_answer_id === $this->post_id)
        {
            $bestAnswer = $this->finder('QuestionThreads:BestAnswer')->where(['post_id' => $thread->QT_best_answer_id])->fetchOne();

            if($bestAnswer)
            {
                $bestAnswer->delete();
            }
        }

        parent::_postDelete();
    }

    protected function postHidden($hardDelete = false)
    {
        /** @var Thread $thread */
        $thread = $this->Thread;

        if($thread->QT_best_answer_id === $this->post_id)
        {
            /** @var BestAnswer $bestAnswer */
            $bestAnswer = $this->finder('QuestionThreads:BestAnswer')->where(['post_id' => $thread->QT_best_answer_id])->fetchOne();

            if($bestAnswer)
            {
                $bestAnswer->fastUpdate('is_counted', 0);

                /** @var \QuestionThreads\Repository\BestAnswer $bestAnswerRepo */
                $bestAnswerRepo = $this->repository('QuestionThreads:BestAnswer');
                $bestAnswerRepo->adjustBestAnswers($bestAnswer->BestAnswerPoster);
            }
        }

        parent::postHidden($hardDelete);
    }

    protected function postMadeVisible()
    {
        /** @var Thread $thread */
        $thread = $this->Thread;

        if($thread->QT_question && ($thread->QT_best_answer_id === $this->post_id))
        {
            /** @var BestAnswer $bestAnswer */
            $bestAnswer = $this->finder('QuestionThreads:BestAnswer')->where(['post_id' => $thread->QT_best_answer_id])->fetchOne();

            if($bestAnswer)
            {
                $bestAnswer->fastUpdate('is_counted', 1);

                /** @var \QuestionThreads\Repository\BestAnswer $bestAnswerRepo */
                $bestAnswerRepo = $this->repository('QuestionThreads:BestAnswer');
                $bestAnswerRepo->adjustBestAnswers($bestAnswer->BestAnswerPoster);
            }
        }

        parent::postMadeVisible();
    }
}