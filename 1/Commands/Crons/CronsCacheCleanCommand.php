<?php

namespace Meetwire\Commands\Crons;

use Model_Crons;

class CronsCacheCleanCommand extends CronActivity
{
    protected function configure()
    {
        $this->setName("crons:cache:clean")
            ->setDescription("Clean ZF caches (db and translate)")
            ->setHelp("The <info>crons:cache:clean</info> command runs without any arguments.");
    }

    /**
     *	@todo
     */
    public function doExecute()
    {
        $this->status = Model_Crons::CRON_STATUS_OK;
        $this->message = 'Done';
    }
}
