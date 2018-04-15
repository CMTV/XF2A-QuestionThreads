<?php
/**
 * Question Threads
 *
 * You CAN use/change/share this code.
 * Enjoy!
 *
 * Written by CMTV
 * Date: 14.03.2018
 * Time: 18:42
 */

namespace QuestionThreads\Repository;

use XF\Mvc\Entity\Repository;

class Thread extends Repository
{
    /**
     * @param \QuestionThreads\XF\Entity\Thread $question
     */
    public function convertQuestionToThread(\XF\Entity\Thread $question)
    {
        if($question->QT_best_answer_id)
        {
            /** @var \QuestionThreads\Entity\BestAnswer $bestAnswer */
            $bestAnswer = $this->finder('QuestionThreads:BestAnswer')->where(['post_id' => $question->QT_best_answer_id])->fetchOne();

            if($bestAnswer)
            {
                $bestAnswer->fastUpdate('is_counted', false);

                /** @var BestAnswer $bestAnswerRepo */
                $bestAnswerRepo = $this->repository('QuestionThreads:BestAnswer');
                $bestAnswerRepo->adjustBestAnswers($bestAnswer->BestAnswerPoster);
            }
        }

        $question->fastUpdate('QT_question', 0);
    }

    /**
     * @param \QuestionThreads\XF\Entity\Thread $thread
     */
    public function convertThreadToQuestion(\XF\Entity\Thread $thread)
    {
        if($thread->QT_best_answer_id)
        {
            /** @var \QuestionThreads\Entity\BestAnswer $bestAnswer */
            $bestAnswer = $this->finder('QuestionThreads:BestAnswer')->where(['post_id' => $thread->QT_best_answer_id])->fetchOne();

            if($bestAnswer)
            {
                $bestAnswer->fastUpdate('is_counted', true);

                /** @var BestAnswer $bestAnswerRepo */
                $bestAnswerRepo = $this->repository('QuestionThreads:BestAnswer');
                $bestAnswerRepo->adjustBestAnswers($bestAnswer->BestAnswerPoster);
            }
        }

        $thread->fastUpdate('QT_question', 1);
    }
}