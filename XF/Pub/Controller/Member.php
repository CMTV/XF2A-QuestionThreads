<?php
/**
 * Question Threads
 *
 * You CAN use/change/share this code.
 * Enjoy!
 *
 * Written by CMTV
 * Date: 15.03.2018
 * Time: 10:21
 */

namespace QuestionThreads\XF\Pub\Controller;

use QuestionThreads\Repository\BestAnswer;
use XF\Mvc\ParameterBag;

class Member extends XFCP_Member
{
    public function actionBestAnswers(ParameterBag $params)
    {
        $user = $this->assertViewableUser($params->user_id);

        $page = $params->page;
        $perPage = 20;

        /** @var BestAnswer $bestAnswersRepo */
        $bestAnswersRepo = $this->repository('QuestionThreads:BestAnswer');

        $bestAnswerFinder = $bestAnswersRepo->findUserBestAnswers($user);

        $total = $bestAnswerFinder->total();
        $this->assertValidPage($page, $perPage, $total, 'members/bestAnswers', $user);

        $bestAnswers = $bestAnswerFinder->limitByPage($page, $perPage)->fetch();
        $bestAnswers = $bestAnswers->filterViewable();

        $viewParams = [
            'user' => $user,
            'bestAnswers' => $bestAnswers,
            'page' => $page,
            'perPage' => $perPage,
            'total' => $total
        ];

        $view = $this->view('QuestionThreads:Member\BestAnswers', 'QT_member_best_answers', $viewParams);
        return $view;
    }
}