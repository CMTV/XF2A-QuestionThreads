<?php

namespace QuestionThreads;

use XF\Mvc\Entity\Entity;
use XF\Mvc\Entity\Manager;
use XF\Mvc\Entity\Structure;

class ExtendEntities
{
    /**
     * Extending forum entity structure
     *
     * @param Manager $em
     * @param Structure $structure
     */
    public static function extendForumEntityStructure(Manager $em, Structure &$structure)
    {
        $structure->columns['questionthreads_forum'] = ['type' => Entity::BOOL, 'default' => false];
    }

    /**
     * Extending thread entity structure
     *
     * @param Manager $em
     * @param Structure $structure
     */
    public static function extendThreadEntityStructure(Manager $em, Structure &$structure)
    {
        $structure->columns['questionthreads_is_question'] =    ['type' => Entity::BOOL, 'default' => false];
        $structure->columns['questionthreads_is_solved'] =      ['type' => Entity::BOOL, 'default' => false];
        $structure->columns['questionthreads_best_post'] =      ['type' => Entity::UINT, 'default' => 0];
    }
}