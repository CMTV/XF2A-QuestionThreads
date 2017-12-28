<?php

namespace QuestionThreads\XF\Pub\Controller;

class Forum extends XFCP_Forum
{
    /**
     * Automatically create question thread if it was created in questions forum
     * or checkbox "Is question" is selected
     *
     * @param \XF\Entity\Forum $forum
     * @return \QuestionThreads\XF\Service\Thread\Creator
     */
    public function setupThreadCreate(\XF\Entity\Forum $forum)
    {
        /** @var \QuestionThreads\XF\Service\Thread\Creator $creator */
        $creator = parent::setupThreadCreate($forum);

        if ($forum->questionthreads_forum || $this->filter('questionthreads_is_question', 'bool'))
        {
            $creator->isQuestionThread = true;
        }

        return $creator;
    }
}