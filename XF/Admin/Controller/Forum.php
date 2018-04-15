<?php
/**
 * Question Threads
 *
 * You CAN use/change/share this code.
 * Enjoy!
 *
 * Written by CMTV
 * Date: 06.03.2018
 * Time: 14:47
 */

namespace QuestionThreads\XF\Admin\Controller;

use XF\Mvc\FormAction;

class Forum extends XFCP_Forum
{
    protected function saveTypeData(FormAction $form, \XF\Entity\Node $node, \XF\Entity\AbstractNode $data)
    {
        parent::saveTypeData($form, $node, $data);

        $form->setup(function() use ($data)
        {
            $data->QT_type = $this->filter('QT_type', 'str');
        });

        if($this->filter('QT_convert_threads', 'bool'))
        {
            /** @var \QuestionThreads\Repository\Forum $forumRepo */
            $forumRepo = $this->repository('QuestionThreads:Forum');

            switch($this->filter('QT_type', 'str'))
            {
                case \QuestionThreads\XF\Entity\Forum::QT_THREADS_ONLY:
                    $forumRepo->convertToThreadsOnly($data);
                    break;
                case \QuestionThreads\XF\Entity\Forum::QT_QUESTIONS_ONLY:
                    $forumRepo->convertToQuestionsOnly($data);
                    break;
            }
        }
    }
}