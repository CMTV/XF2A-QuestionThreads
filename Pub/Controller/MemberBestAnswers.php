<?php
/**
 * Question Threads xF2 addon by CMTV
 * Enjoy!
 */

namespace CMTV\QuestionThreads\Pub\Controller;

use CMTV\QuestionThreads\Repository\BestAnswer;
use XF\Entity\User;
use XF\Mvc\ParameterBag;
use XF\Pub\Controller\AbstractController;

use CMTV\QuestionThreads\Constants as C;

class MemberBestAnswers extends AbstractController
{
    public function actionIndex(ParameterBag $params)
    {
        $user = $this->assertViewableUser($params->user_id);

        $page = $this->filterPage($params->page);
        $perPage = $this->options()->messagesPerPage;

        /** @var BestAnswer $bestAnswerRepo */
        $bestAnswerRepo = $this->repository(C::__('BestAnswer'));
        $bestAnswerFinder = $bestAnswerRepo->findMemberBestAnswers($user)
            ->order('BestAnswerPoster.username')
            ->limitByPage($page, $perPage, 1);

        $bestAnswers = $bestAnswerFinder->fetch()->pluckNamed('BestAnswerPost');
        $bestAnswers = $bestAnswers->slice(0, $perPage);
        $bestAnswersCount = $bestAnswerFinder->total();

        $viewParams = [
            'user' => $user,
            'bestAnswers' => $bestAnswers,
            'page' => $page,
            'perPage' => $perPage,
            'total' => $bestAnswersCount
        ];

        return $this->view(
            C::__('MemberBestAnswers'),
            C::_('member_best_answers'),
            $viewParams
        );
    }

    public function assertViewableUser($userId, array $extraWidth = [], $basicProfileOnly = false)
    {
        $extraWidth[] = 'Option';
        $extraWidth[] = 'Privacy';
        $extraWidth[] = 'Profile';
        array_unique($extraWidth);

        /** @var User $user */
        $user = $this->em()->find('XF:User', $userId, $extraWidth);

        if (!$user)
        {
            throw $this->exception($this->notFound(\XF::phrase('requested_user_not_found')));
        }

        $canView = $basicProfileOnly ? $user->canViewBasicProfile($error) : $user->canViewFullProfile($error);

        if (!$canView)
        {
            throw $this->exception($this->noPermission($error));
        }

        return $user;
    }
}