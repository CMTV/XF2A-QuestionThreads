<?php
/**
 * Question Threads xF2 addon by CMTV
 * Enjoy!
 */

namespace CMTV\QuestionThreads\Entity;

use XF\Entity\Post;
use XF\Entity\Thread;
use XF\Entity\User;
use XF\Mvc\Entity\Entity;
use XF\Mvc\Entity\Structure;

use CMTV\QuestionThreads\Constants as C;

/**
 * COLUMNS
 * @property int|null best_answer_id
 * @property int post_id
 * @property int post_user_id
 * @property int thread_id
 * @property int thread_user_is
 * @property bool is_counted
 *
 * RELATIONS
 * @property Post BestAnswerPost
 * @property User BestAnswerPoster
 * @property Thread Thread
 * @property User ThreadPoster
 */
class BestAnswer extends Entity
{
    public static function getStructure(Structure $structure)
    {
        $structure->table = 'xf_' . C::_('best_answer');
        $structure->primaryKey = 'best_answer_id';
        $structure->shortName = C::_('BestAnswer');

        // Columns

        $structure->columns = [
            'best_answer_id' => [
                'type' => self::UINT,
                'autoIncrement' => true,
                'nullable' => true
            ],
            'post_id' => [
                'type' => self::UINT,
                'required' => true
            ],
            'post_user_id' => [
                'type' => self::UINT,
                'required' => true
            ],
            'thread_id' => [
                'type' => self::UINT,
                'required' => true
            ],
            'thread_user_id' => [
                'type' => self::UINT,
                'required' => true
            ],
            'is_counted' => [
                'type' => self::BOOL,
                'default' => true
            ]
        ];

        // Relations

        $structure->relations = [
            'BestAnswerPost' => [
                'entity' => 'XF:Post',
                'type' => self::TO_ONE,
                'conditions' => [['post_id', '=', '$post_id']],
                'primary' => true
            ],
            'BestAnswerPoster' => [
                'entity' => 'XF:User',
                'type' => self::TO_ONE,
                'conditions' => [['user_id', '=', '$post_user_id']]
            ],
            'Thread' => [
                'entity' => 'XF:Thread',
                'type' => self::TO_ONE,
                'conditions' => [['thread_id', '=', '$thread_id']]
            ],
            'ThreadPoster' => [
                'entity' => 'XF:User',
                'type' => self::TO_ONE,
                'conditions' => [['user_id', '=', '$thread_user_id']]
            ]
        ];

        $structure->defaultWith = ['BestAnswerPost', 'BestAnswerPoster'];

        return $structure;
    }

    //************************* LIFE CYCLE ***************************

    protected function _postSave()
    {
        /** @var \CMTV\QuestionThreads\XF\Entity\User $baPoster */
        $baPoster = $this->BestAnswerPoster;

        if ($this->isInsert())
        {
            $baPoster->bestAnswersIncrease();
        }

        $changed = $this->isStateChanged('is_counted', false);

        switch ($changed)
        {
            case 'enter':
                $baPoster->bestAnswersDecrease();
                break;
            case 'leave':
                $baPoster->bestAnswersIncrease();
                break;
        }
    }

    protected function _postDelete()
    {
        /** @var \CMTV\QuestionThreads\XF\Entity\Thread $thread */
        if ($thread = $this->Thread)
        {
            $thread->bestAnswerRemoved();
            $thread->save();
        }

        /** @var \CMTV\QuestionThreads\XF\Entity\User $baPoster */
        if (($baPoster = $this->BestAnswerPoster) && ($baPost = $this->BestAnswerPost))
        {
            $baPoster->bestAnswersDecrease();

            $bestAnswerRepo = $this->getBestAnswerRepo();
            $bestAnswerRepo->unpublishBestAnswerNewsFeed($baPost);
        }
    }

    /**
     * @return \CMTV\QuestionThreads\Repository\BestAnswer
     */
    protected function getBestAnswerRepo()
    {
        return $this->repository(C::__('BestAnswer'));
    }
}