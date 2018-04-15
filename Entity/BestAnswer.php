<?php
/**
 * Question Threads
 *
 * You CAN use/change/share this code.
 * Enjoy!
 *
 * Written by CMTV
 * Date: 12.03.2018
 * Time: 11:40
 */

namespace QuestionThreads\Entity;

use XF\Entity\Post;
use XF\Entity\Thread;
use XF\Entity\User;
use XF\Mvc\Entity\Entity;
use XF\Mvc\Entity\Structure;

/**
 * COLUMNS
 * @property int best_answer_id
 * @property int post_id
 * @property int post_user_id
 * @property int thread_id
 * @property int thread_user_id
 * @property bool is_counted
 *
 * RELATIONS
 * @property Post BestAnswerPost
 * @property User BestAnswerPoster
 * @property Thread Thread
 * @property User ThreadAuthor
 */
class BestAnswer extends Entity
{
    public static function getStructure(Structure $structure)
    {
        $structure->table = 'xf_QT_best_answer';
        $structure->shortName = 'QuestionThreads:BestAnswer';
        $structure->primaryKey = 'best_answer_id';

        $structure->columns = [
            'best_answer_id' => ['type' => self::UINT, 'autoIncrement' => true],
            'post_id' => ['type' => self::UINT, 'required' => true],
            'post_user_id' => ['type' => self::UINT, 'required' => true],
            'thread_id' => ['type' => self::UINT, 'required' => true],
            'thread_user_id' => ['type' => self::UINT, 'required' => true],
            'is_counted' => ['type' => self::BOOL, 'default' => true]
        ];

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
                'conditions' => [['user_id', '=', '$post_user_id']],
                'primary' => true
            ],
            'Thread' => [
                'entity' => 'XF:Thread',
                'type' => self::TO_ONE,
                'conditions' => [['thread_id', '=', '$thread_id']],
                'primary' => true
            ],
            'ThreadAuthor' => [
                'entity' => 'XF:User',
                'type' => self::TO_ONE,
                'conditions' => [['user_id', '=', '$thread_user_id']],
                'primary' => true
            ]
        ];

        $structure->defaultWith = ['BestAnswerPost', 'BestAnswerPoster', 'Thread', 'ThreadAuthor'];

        return $structure;
    }

    protected function _postDelete()
    {
        /** @var \QuestionThreads\Repository\BestAnswer $bestAnswerRepo */
        $bestAnswerRepo = $this->repository('QuestionThreads:BestAnswer');

        $bestAnswerThread = $this->Thread;
        if($bestAnswerThread)
        {
            $bestAnswerThread->fastUpdate('QT_best_answer_id', 0);
        }

        $user = $this->BestAnswerPoster;
        $bestAnswerRepo->adjustBestAnswers($user);
    }

    public function canView()
    {
        return ($this->BestAnswerPost->canView() && $this->Thread->canView());
    }
}