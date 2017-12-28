<?php

namespace QuestionThreads\XF\Service\Thread;

class Creator extends XFCP_Creator
{
    public $isQuestionThread = false;

    protected function _save()
    {
        $thread = parent::_save();

        $thread->questionthreads_is_question = $this->isQuestionThread;
        $thread->save();

        return $thread;
    }
}