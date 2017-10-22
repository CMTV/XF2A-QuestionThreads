<?php

namespace QuestionThreads\XF\Admin\Controller;

use XF\Mvc\FormAction;

class Forum extends XFCP_Forum
{
    /**
     * Extending forum save process allowing marking forum as "questions forum"
     *
     * @param FormAction $form
     * @param \XF\Entity\Node $node
     * @param \XF\Entity\AbstractNode $data
     */
    protected function saveTypeData(FormAction $form, \XF\Entity\Node $node, \XF\Entity\AbstractNode $data)
    {
        parent::saveTypeData($form, $node, $data);

        $form->setup(function() use ($data)
        {
            $data->questionthreads_forum = $this->filter('questionthreads_forum', 'bool');
        });
    }
}