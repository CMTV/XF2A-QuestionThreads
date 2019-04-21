<?php
/**
 * Question Threads xF2 addon by CMTV
 * Enjoy!
 */

namespace CMTV\QuestionThreads;

use XF\AddOn\AbstractSetup;
use XF\AddOn\StepRunnerInstallTrait;
use XF\AddOn\StepRunnerUninstallTrait;
use XF\AddOn\StepRunnerUpgradeTrait;
use XF\Db\Schema\Alter;
use XF\Db\Schema\Create;

use CMTV\QuestionThreads\Constants as C;

class Setup extends AbstractSetup
{
    use StepRunnerInstallTrait;
    use StepRunnerUpgradeTrait;
    use StepRunnerUninstallTrait;

    //************************* INSTALL STEPS ***************************

    public function installStep1()
    {
        if ($this->app()->config('CMTV_QT')['upgrade_install'])
        {
            $this->_installStep1();
            return;
        }

        $this->schemaManager()->createTable('xf_' . C::_('best_answer'), function (Create $table)
        {
            $table->addColumn('best_answer_id', 'int')->autoIncrement();
            $table->addColumn('post_id', 'int');
            $table->addColumn('post_user_id', 'int');
            $table->addColumn('thread_id', 'int');
            $table->addColumn('thread_user_id', 'int');
            $table->addColumn('is_counted', 'tinyint', 3)->setDefault(1);
        });
    }

    public function installStep2()
    {
        if ($this->app()->config('CMTV_QT')['upgrade_install'])
        {
            $this->_installStep2();
            return;
        }

        $this->schemaManager()->alterTable('xf_thread', function (Alter $table)
        {
            $table->addColumn(C::_('is_question'), 'tinyint', 3)->setDefault(0);
            $table->addColumn(C::_('is_solved'), 'tinyint', 3)->setDefault(0);
            $table->addColumn(C::_('best_answer_id'), 'int')->setDefault(0);
        });
    }

    public function installStep3()
    {
        if ($this->app()->config('CMTV_QT')['upgrade_install'])
        {
            $this->_installStep3();
            return;
        }

        $this->schemaManager()->alterTable('xf_user', function (Alter $table)
        {
            $table->addColumn(C::_('best_answer_count'), 'int')->setDefault(0);
        });
    }

    public function installStep4()
    {
        if ($this->app()->config('CMTV_QT')['upgrade_install'])
        {
            $this->_installStep4();
            return;
        }

        $this->schemaManager()->alterTable('xf_forum', function (Alter $table)
        {
            $table->addColumn(C::_('type'), 'enum')->values(['threads_only', 'questions_only', 'both'])->setDefault('threads_only');
        });
    }

    public function installStep5()
    {
        $registeredPermissions = [
            'markOwnQuestionSolved',
            'selectBestAnswerOwn'
        ];

        $moderatorPermissions = [
            'editAnyThreadType',
            'markAnyQuestionSolved',
            'markAnyQuestionUnsolved',
            'selectBestAnswerAny',
            'unselectBestAnswerAny'
        ];

        foreach ($registeredPermissions as $permission)
        {
            $this->applyGlobalPermission(
                C::_(),
                $permission,
                'forum',
                'editOwnPost'
            );
        }

        foreach ($moderatorPermissions as $permission)
        {
            $this->applyGlobalPermission(
                C::_(),
                $permission,
                'forum',
                'deleteAnyThread'
            );
        }
    }

    public function postInstall(array &$stateChanges)
    {
        $this->app()->jobManager()->enqueue(C::__('RemapBestAnswers'), [], true);
    }

    //************************* UNINSTALL STEPS ***************************

    public function uninstallStep1()
    {
        $this->schemaManager()->dropTable('xf_' . C::_('best_answer'));
    }

    public function uninstallStep2()
    {
        $this->schemaManager()->alterTable('xf_thread', function (Alter $table)
        {
            $table->dropColumns([
                C::_('is_question'),
                C::_('is_solved'),
                C::_('best_answer_id')
            ]);
        });
    }

    public function uninstallStep3()
    {
        $this->schemaManager()->alterTable('xf_user', function (Alter $table)
        {
            $table->dropColumns(C::_('best_answer_count'));
        });
    }

    public function uninstallStep4()
    {
        $this->schemaManager()->alterTable('xf_forum', function (Alter $table)
        {
            $table->dropColumns(C::_('type'));
        });
    }

    //************************* UPGRADE STEPS ***************************

    //************************* 2.0.2 -> 2.1.0 ***************************

    public function _installStep1()
    {
        $this->schemaManager()->renameTable('xf_qt_best_answer', 'xf_' . C::_('best_answer'));

    }

    public function _installStep2()
    {
        $this->schemaManager()->alterTable('xf_thread', function (Alter $table)
        {
            $table->renameColumn('QT_question', C::_('is_question'));
            $table->renameColumn('QT_solved', C::_('is_solved'));
            $table->renameColumn('QT_best_answer_id', C::_('best_answer_id'));
        });
    }

    public function _installStep3()
    {
        $this->schemaManager()->alterTable('xf_user', function (Alter $table)
        {
            $table->renameColumn('QT_best_answer_count', C::_('best_answer_count'));
        });
    }

    public function _installStep4()
    {
        $this->schemaManager()->alterTable('xf_forum', function (Alter $table)
        {
            $table->renameColumn('QT_type', C::_('type'));
        });

        $this->query("ALTER TABLE `xf_forum` MODIFY `CMTV_QT_type` ENUM('questions_only', 'threads_only', 'both', 'threads_questions')");
        $this->query("UPDATE `xf_forum` SET `CMTV_QT_type` = 'both' WHERE `CMTV_QT_type` = 'threads_questions'");
        $this->query("ALTER TABLE `xf_forum` MODIFY `CMTV_QT_type` ENUM('questions_only', 'threads_only', 'both')");
    }
}