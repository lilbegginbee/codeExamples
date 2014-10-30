<?php
/**
 * Родитель всех крон джёбов
 */
namespace App\Commands\Crons;

use App\Commands\zendLoadTrait;
use App\Commands\AppCommand;
use Model_Crons;

class CronActivity extends AppCommand
{

    use zendLoadTrait;

    protected $cronId = null;

    /**
     * @var $input InputInterface
     */
    protected $input;
    /**
     * @var $input OutputInterface
     */
    protected $output;

    protected $status;
    protected $message;

    /**
     * @var array Аргументы переданные через cli
     */
    protected $args = array();

    public function __construct()
    {
        parent::__construct();
        $this->initZend();
        $this->cronId = get_called_class();
    }

    public function __destruct()
    {
        $this->close();
    }

    /**
     * Установка статуса для кронджоба
     * @param $status
     */
    public function setStatus($status)
    {
        $mCrons = new Model_Crons();
        return $mCrons->setStatus($this->cronId, $status);
    }

    /**
     * Установка статус-сообщения для кронджоба
     * @param $message
     */
    public function setMessage($message)
    {
        $mCrons = new Model_Crons();
        return $mCrons->setMessage($this->cronId, $message);
    }

    /**
     * По завершению работы скрипта нужно рассказать всем как он отработал.
     * @return bool
     */
    public function close()
    {
        if (!$this->_status) {
            return;
        }
        if ($this->_status == Model_Crons::CRON_STATUS_OK) {
            $this->output->writeln("'<info>{$this->cronId}</info>' is done <comment>successfully</comment>");
        } else {
            $this->output->writeln("'<info>{$this->cronId}</info>' is <comment>failed</comment>");
        }

        $mCrons = new Model_Crons();
        $mCrons->initCron($this->_cronId);
        return $mCrons->update(
            array(
                'status'            => $this->status,
                'status_message'    => $this->message,
                'date_executed'     => date('Y.m.d H:i:s')
            ),
            'id_cron = "' . str_replace('\\', '\\\\', $this->cronId) . '"'
        );
    }

    public function getArg($arg_name, $default = null)
    {
        if (isset($this->args[$arg_name])) {
            return $this->args[$arg_name];
        }
        return $default;
    }
}
