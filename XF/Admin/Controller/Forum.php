<?php
/**
 * Question Threads xF2 addon by CMTV
 * Enjoy!
 */

namespace CMTV\QuestionThreads\XF\Admin\Controller;

use XF\Mvc\FormAction;

use CMTV\QuestionThreads\Constants as C;

class Forum extends XFCP_Forum
{
    protected function saveTypeData(FormAction $form, \XF\Entity\Node $node, \XF\Entity\AbstractNode $data)
    {
        parent::saveTypeData($form, $node, $data);

        $forumType = $this->filter(C::_('type'), 'str');

        $data->bulkSet([
            'CMTV_QT_type' => $forumType
        ]);

        if (
            in_array($forumType, ['threads_only', 'questions_only'])
            &&
            $this->filter(C::_('convert'), 'bool')
        )
        {
            $this->app->jobManager()->enqueueUnique(
                C::_('convertation'),
                C::__('ConvertForumThreads'),
                ['forum_id' => $data->node_id, 'type' => $forumType],
                true
            );
        }
    }
}