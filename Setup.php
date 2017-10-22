<?php

namespace QuestionThreads;

use XF\AddOn\AbstractSetup;
use XF\AddOn\StepRunnerInstallTrait;
use XF\AddOn\StepRunnerUninstallTrait;
use XF\AddOn\StepRunnerUpgradeTrait;
use XF\Db\Schema\Alter;

class Setup extends AbstractSetup
{
	use StepRunnerInstallTrait;
	use StepRunnerUpgradeTrait;
	use StepRunnerUninstallTrait;

    /**
     * Adding 'questionthreads_forum' column to 'xf_forum' table
     */
	public function installStep1()
    {
        $this->schemaManager()->alterTable('xf_forum', function(Alter $table)
        {
            $table->addColumn('questionthreads_forum', 'tinyInt')->setDefault(0);
        });
    }

    /**
     * Adding 'questionthreads_is_question', 'questionthreads_is_solved' and 'questionthreads_best_post'
     * columns to 'xf_thread' table
     */
    public function installStep2()
    {
        $this->schemaManager()->alterTable('xf_thread', function(Alter $table)
        {
            $table->addColumn('questionthreads_is_question', 'tinyInt')->setDefault(0);
            $table->addColumn('questionthreads_is_solved', 'tinyInt')->setDefault(0);
            $table->addColumn('questionthreads_best_post', 'int');
        });
    }

    /**
     * Dropping 'questionthreads_forum' column in 'xf_forum' table
     */
    public function uninstallStep1()
    {
        $this->schemaManager()->alterTable('xf_forum', function(Alter $table)
        {
            $table->dropColumns('questionthreads_forum');
        });
    }

    /**
     * Dropping 'questionthreads_is_question', 'questionthreads_is_solved' and 'questionthreads_best_post'
     * columns in 'xf_thread' table
     */
    public function uninstallStep2()
    {
        $this->schemaManager()->alterTable('xf_thread', function(Alter $table)
        {
            $table->dropColumns(['questionthreads_is_question', 'questionthreads_is_solved', 'questionthreads_best_post']);
        });
    }
}