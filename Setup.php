<?php

namespace QuestionThreads;

use QuestionThreads\XF\Entity\Forum;
use XF\AddOn\AbstractSetup;
use XF\AddOn\StepRunnerInstallTrait;
use XF\AddOn\StepRunnerUninstallTrait;
use XF\AddOn\StepRunnerUpgradeTrait;
use XF\Db\Schema\Alter;
use XF\Db\Schema\Create;

class Setup extends AbstractSetup
{
	use StepRunnerInstallTrait;
	use StepRunnerUpgradeTrait;
	use StepRunnerUninstallTrait;

	public function installStep1($is1Upgrading)
    {
        $this->schemaManager()->alterTable('xf_forum', function(Alter $table) use ($is1Upgrading) {

            $default = ($is1Upgrading) ? 'threads_questions' : 'threads_only';

            $table->addColumn('QT_type', 'varchar', 25)->setDefault($default);
        });

        $this->schemaManager()->alterTable('xf_user', function(Alter $table)
        {
            $table->addColumn('QT_best_answer_count', 'int')->setDefault(0);
        });
    }

    public function installStep2()
    {
        $this->schemaManager()->alterTable('xf_thread', function(Alter $table) {
            $table->addColumn('QT_question', 'tinyint')->setDefault(0);
            $table->addColumn('QT_solved', 'tinyint')->setDefault(0);
            $table->addColumn('QT_best_answer_id', 'int')->setDefault(0);
        });
    }

    public function installStep3()
    {
        $this->schemaManager()->createTable('xf_QT_best_answer', function(Create $table)
        {
            $table->addColumn('best_answer_id', 'int')->autoIncrement();
            $table->addColumn('post_id', 'int');
            $table->addColumn('post_user_id', 'int');
            $table->addColumn('thread_id', 'int');
            $table->addColumn('thread_user_id', 'int');
            $table->addColumn('is_counted', 'tinyint')->setDefault(1);

            $table->addPrimaryKey('best_answer_id');
        });
    }

    public function installStep4()
    {
        $registeredPermissions = [
            'QT_createQuestion',
            'QT_editOwnThreadType',
            'QT_markOwnSolved',
            //'QT_markOwnUnsolved',
            'QT_selectBestAnswerOwn',
            //'QT_selectBestAnswerOwn_'
            //'QT_unselectBestAnswerOwn'
        ];

        $moderatorPermissions = [
            'QT_editAnyThreadType',
            'QT_markAnySolved',
            'QT_markAnyUnsolved',
            'QT_selectBestAnswerAny',
            'QT_unselectBestAnswerAny'
        ];

        foreach($registeredPermissions as $registeredPermission)
        {
            $this->applyGlobalPermission('forum', $registeredPermission, 'forum', 'editOwnPost');
        }

        foreach($moderatorPermissions as $moderatorPermission)
        {
            $this->applyGlobalPermission('forum', $moderatorPermission, 'forum', 'manageAnyThread');
        }
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    /// UPDATING FROM 1.x.x
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    public function upgrade(array $stepParams = [])
    {
        if($this->addOn->version_id < 2000070)
        {
            $this->schemaManager()->alterTable('xf_forum', function(Alter $table)
            {
                $table->dropColumns('questionthreads_forum');
            });

            $this->schemaManager()->alterTable('xf_thread', function(Alter $table)
            {
                $table->renameColumn('questionthreads_is_question', 'QT_question');
                $table->renameColumn('questionthreads_is_solved', 'QT_solved');
                $table->renameColumn('questionthreads_best_post', 'QT_best_answer_id');
            });

            $this->installStep1(true);
            $this->installStep3();
            $this->installStep4();

            $this->uninstallStep1();
            $this->db()->query("DELETE FROM `xf_user_alert` WHERE `action` LIKE 'questionthreads_%'");

            $this->app->jobManager()->enqueueUnique('QT_upgrade', 'QuestionThreads:RebuildBestAnswerCounts');
        }
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    /// UNINSTALLING
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    // Removing possible running addon-related jobs
    public function uninstallStep1()
    {
        $db = $this->app->db();
        $query = "DELETE FROM `xf_job` WHERE `execute_class` LIKE 'QuestionThreads:%'";
        $db->query($query);
    }

    // Removing all addon-related user alerts
    public function uninstallStep2()
    {
        $db = $this->app->db();
        $query = "DELETE FROM `xf_user_alert` WHERE `action` LIKE 'QT_%'";
        $db->query($query);
    }

    public function uninstallStep3()
    {
        $this->schemaManager()->dropTable('xf_QT_best_answer');
    }

    public function uninstallStep4()
    {
        $this->schemaManager()->alterTable('xf_forum', function(Alter $table)
        {
            $table->dropColumns('QT_type');
        });

        $this->schemaManager()->alterTable('xf_thread', function(Alter $table)
        {
            $table->dropColumns(['QT_question', 'QT_solved', 'QT_best_answer_id']);
        });

        $this->schemaManager()->alterTable('xf_user', function(Alter $table)
        {
            $table->dropColumns('QT_best_answer_count');
        });
    }
}