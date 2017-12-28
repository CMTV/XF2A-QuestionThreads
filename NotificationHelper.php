<?php

namespace QuestionThreads;

use \XF\Entity\Thread;
use \XF\Entity\User;
use \XF\Entity\Post;
use \XF\Repository\UserAlert;

class NotificationHelper
{
    /* - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -  */
    /* Notification types */
    /* - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -  */
    const THREAD_SOLVED = 1;
    const THREAD_UNSOLVED = 2;
    const THREAD_BEST_ANSWER_REMOVED = 3;
    const POST_BEST_ANSWER = 4;

    /* - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -  */
    /* Alerts */
    /* - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -  */

    /**
     * Alerting watcher that the question was solved
     *
     * @param Thread $thread
     * @param User $actionCaller
     * @param User $watcher
     */
    public static function alertThreadSolved(Thread $thread, User $actionCaller, User $watcher)
    {
        if($watcher->user_id !== $actionCaller->user_id)
        {
            /** @var UserAlert $alertRep */
            $alertRep = \XF::app()->repository('XF:UserAlert');
            $alertRep->alert(
                $watcher,
                $actionCaller->user_id,
                $actionCaller->username,
                'thread',
                $thread->thread_id,
                'questionthreads_solved'
            );
        }
    }

    /**
     * Alerting watcher that the question was unsolved
     *
     * @param Thread $thread
     * @param User $actionCaller
     * @param User $watcher
     */
    public static function alertThreadUnsolved(Thread $thread, User $actionCaller, User $watcher)
    {
        if($watcher->user_id !== $actionCaller->user_id)
        {
            /** @var UserAlert $alertRep */
            $alertRep = \XF::app()->repository('XF:UserAlert');
            $alertRep->alert(
                $watcher,
                $actionCaller->user_id,
                $actionCaller->username,
                'thread',
                $thread->thread_id,
                'questionthreads_unsolved'
            );
        }
    }

    /**
     * Alerting watcher that best answer mark was removed
     *
     * @param Post $bestAnswer
     * @param User $actionCaller
     * @param User $watcher
     */
    public static function alertRemoveBestAnswer(Post $bestAnswer, User $actionCaller, User $watcher)
    {
        if($watcher->user_id !== $actionCaller->user_id)
        {
            /** @var User $bestAnswerPoster */
            $bestAnswerPoster = \XF::finder('XF:User')->where('user_id', $bestAnswer->user_id)->fetchOne();

            /* Sending different notifications to best answer poster and ordinary watcher */
            if($watcher->user_id === $bestAnswerPoster->user_id)
            {
                /** @var UserAlert $alertRep */
                $alertRep = \XF::app()->repository('XF:UserAlert');
                $alertRep->alert(
                    $watcher,
                    $actionCaller->user_id,
                    $actionCaller->username,
                    'post',
                    $bestAnswer->post_id,
                    'questionthreads_remove_best_a'
                );
            }
            else
            {
                /** @var UserAlert $alertRep */
                $alertRep = \XF::app()->repository('XF:UserAlert');
                $alertRep->alert(
                    $watcher,
                    $actionCaller->user_id,
                    $actionCaller->username,
                    'post',
                    $bestAnswer->post_id,
                    'questionthreads_remove_best'
                );
            }
        }
    }

    /**
     * Alerting watcher that given post was marked as best answer
     *
     * @param Post $bestAnswer
     * @param User $actionCaller
     * @param User $watcher
     */
    public static function alertMarkBestAnswer(Post $bestAnswer, User $actionCaller, User $watcher)
    {
        if($watcher->user_id !== $actionCaller->user_id)
        {
            /** @var User $bestAnswerPoster */
            $bestAnswerPoster = \XF::finder('XF:User')->where('user_id', $bestAnswer->user_id)->fetchOne();

            /* Sending different notifications to best answer poster and ordinary watcher */
            if($watcher->user_id === $bestAnswerPoster->user_id)
            {
                /** @var UserAlert $alertRep */
                $alertRep = \XF::app()->repository('XF:UserAlert');
                $alertRep->alert(
                    $watcher,
                    $actionCaller->user_id,
                    $actionCaller->username,
                    'post',
                    $bestAnswer->post_id,
                    'questionthreads_best_post_a'
                );
            }
            else
            {
                /** @var UserAlert $alertRep */
                $alertRep = \XF::app()->repository('XF:UserAlert');
                $alertRep->alert(
                    $watcher,
                    $actionCaller->user_id,
                    $actionCaller->username,
                    'post',
                    $bestAnswer->post_id,
                    'questionthreads_best_post'
                );
            }
        }
    }

    /* - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -  */
    /* Emails */
    /* - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -  */

    /**
     * Emailing watcher that the question was solved
     *
     * @param Thread $thread
     * @param User $actionCaller
     * @param User $watcher
     */
    public static function emailThreadSolved(Thread $thread, User $actionCaller, User $watcher)
    {
        if($watcher->user_id !== $actionCaller->user_id)
        {
            $mailParams = [
                'actionCaller' => $actionCaller,
                'thread' => $thread,
                'forum' => $thread->Forum
            ];

            \XF::app()->mailer()->newMail()
                ->setToUser($watcher)
                ->setTemplate('questionthreads_solved', $mailParams)
                ->send();
        }
    }

    /**
     * Emailing watcher that the question was unsolved
     *
     * @param Thread $thread
     * @param User $actionCaller
     * @param User $watcher
     */
    public static function emailThreadUnsolved(Thread $thread, User $actionCaller, User $watcher)
    {
        if($watcher->user_id !== $actionCaller->user_id)
        {
            $mailParams = [
                'actionCaller' => $actionCaller,
                'thread' => $thread,
                'forum' => $thread->Forum
            ];

            \XF::app()->mailer()->newMail()
                ->setToUser($watcher)
                ->setTemplate('questionthreads_unsolved', $mailParams)
                ->send();
        }
    }

    /**
     * Emailing watcher that best answer mark was removed
     *
     * @param Post $bestAnswer
     * @param User $actionCaller
     * @param User $watcher
     */
    public static function emailRemoveBestAnswer(Post $bestAnswer, User $actionCaller, User $watcher)
    {
        if($watcher->user_id !== $actionCaller->user_id)
        {
            /** @var User $bestAnswerPoster */
            $bestAnswerPoster = \XF::finder('XF:User')->where('user_id', $bestAnswer->user_id)->fetchOne();

            /* Sending different notifications to best answer poster and ordinary watcher */
            if($watcher->user_id === $bestAnswerPoster->user_id)
            {
                $mailParams = [
                    'actionCaller' => $actionCaller,
                    'post' => $bestAnswer,
                    'thread' => $bestAnswer->Thread,
                    'forum' => $bestAnswer->Thread->Forum
                ];

                \XF::app()->mailer()->newMail()
                    ->setToUser($watcher)
                    ->setTemplate('questionthreads_best_mark_removed_a', $mailParams)
                    ->send();
            }
            else
            {
                $mailParams = [
                    'actionCaller' => $actionCaller,
                    'post' => $bestAnswer,
                    'thread' => $bestAnswer->Thread,
                    'forum' => $bestAnswer->Thread->Forum
                ];

                \XF::app()->mailer()->newMail()
                    ->setToUser($watcher)
                    ->setTemplate('questionthreads_best_mark_removed', $mailParams)
                    ->send();
            }
        }
    }

    /**
     * Emailing watcher that given post was marked as best answer
     *
     * @param Post $bestAnswer
     * @param User $actionCaller
     * @param User $watcher
     */
    public static function emailMarkBestAnswer(Post $bestAnswer, User $actionCaller, User $watcher)
    {
        if($watcher->user_id !== $actionCaller->user_id)
        {
            /** @var User $bestAnswerPoster */
            $bestAnswerPoster = \XF::finder('XF:User')->where('user_id', $bestAnswer->user_id)->fetchOne();

            /* Sending different notifications to best answer poster and ordinary watcher */
            if($watcher->user_id === $bestAnswerPoster->user_id)
            {
                $mailParams = [
                    'actionCaller' => $actionCaller,
                    'post' => $bestAnswer,
                    'thread' => $bestAnswer->Thread,
                    'forum' => $bestAnswer->Thread->Forum
                ];

                \XF::app()->mailer()->newMail()
                    ->setToUser($watcher)
                    ->setTemplate('questionthreads_best_answer_a', $mailParams)
                    ->send();
            }
            else
            {
                $mailParams = [
                    'actionCaller' => $actionCaller,
                    'post' => $bestAnswer,
                    'thread' => $bestAnswer->Thread,
                    'forum' => $bestAnswer->Thread->Forum
                ];

                \XF::app()->mailer()->newMail()
                    ->setToUser($watcher)
                    ->setTemplate('questionthreads_best_answer', $mailParams)
                    ->send();
            }
        }
    }
}