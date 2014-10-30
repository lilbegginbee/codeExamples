<?php
/**
 * Пользователь должен подтвердить свой основной Email.
 * @frequency daily
 */
namespace App\Commands\Crons;

use Model_Core;
use Model_Crons;
use Model_User;
use Model_user_Email;
use AG_lib_Notification;
use AG_lib_Timezone;
use AG_lib_Lang;

use  Zend_Db_Select;
use  Zend_Registry;

class RemoveInactiveUsersCommand extends CronActivity
{
    const TYPE_PRIMARY = 'primary';
    const TYPE_ADDITIONAL = 'add';

    const PERIOD_WARNING_FIRST = 604800;
    const PERIOD_WARNING_SECOND = 1209600;
    const PERIOD_DELETE = 1814400;

    const PERIOD_ADDITIONAL_FIRST = 86400;
    const PERIOD_ADDITIONAL_DELETE = 172800;

    const PERIOD_AROUND = 86400;

    protected function configure()
    {
        $this->setName("crons:remove:users:inactive")
            ->setDescription("Clean Inactive Users")
            ->setHelp("The <info>crons:remove:users:inactive</info> command runs without any arguments.");
    }

    public function doExecute()
    {
        $this->checkPrimaryEmails();
        $this->checkAdditionalEmails();

        $this->status = Model_Crons::CRON_STATUS_OK;
        $this->message = '';
    }

    /**
     * Проверяет активированность основной почты
     */
    protected function checkPrimaryEmails()
    {
        $mCore = new Model_Core();
        $mUser = new Model_User();

        $select = $mCore->getAdapter()->select()
            ->from(array('u' => 'user'), array('uid' => 'id'))
            ->join(
                array('e' => 'user_email'),
                'e.user_id = u.id AND e.is_primary = 1 AND e.is_confirmed = 0 AND u.is_deleted = 0'
            );

        // 1 wave
        $leftBound = time()-self::PERIOD_WARNING_FIRST - self::PERIOD_AROUND;
        $rightBound = time()-self::PERIOD_WARNING_FIRST;

        $rows = $select
            ->where('registration_dt > ?', $leftBound)
            ->where('registration_dt <= ?', $rightBound)
            ->query()
            ->fetchAll();
        foreach ($rows as $row) {
            $mUser->loadById($row['id']);
            $this->sendConfirmationEmail($mUser);
        }

        // 2d wave
        $leftBound = time()-self::PERIOD_WARNING_SECOND - self::PERIOD_AROUND;
        $rightBound = time()-self::PERIOD_WARNING_SECOND;
        $select->reset(Zend_Db_Select::WHERE);
        $rows = $select
            ->where('registration_dt > ?', $leftBound)
            ->where('registration_dt <= ?', $rightBound)
            ->query()
            ->fetchAll();
        foreach ($rows as $row) {
            $mUser->loadById($row['id']);
            $this->sendConfirmationEmail($mUser);
        }

        // final wave
        $rightBound = time()-self::PERIOD_DELETE - self::PERIOD_AROUND;
        $select->reset(Zend_Db_Select::WHERE);
        $rows = $select
            ->where('registration_dt <= ?', $rightBound)
            ->query()
            ->fetchAll();

        foreach ($rows as $row) {
            $mUser->loadById($row['uid']);
            $mUser->deleteTemp();
            $this->output->writeln('<info>User [id='.$row['uid'].']</info> is marked as deleted');
        }

    }

    /**
     * Проверяет активированность дополнительных почт
     */
    protected function checkAdditionalEmails()
    {
        $mCore = new Model_Core();
        $mUser = new Model_User();

        $select = $mCore->getAdapter()->select()
            ->from(array('u' => 'user'))
            ->join(array('e' => 'user_email'), 'e.user_id = u.id AND e.is_primary = 0 AND e.is_confirmed = 0');

        // 1 wave
        $leftBound = time()-self::PERIOD_ADDITIONAL_FIRST - self::PERIOD_AROUND;
        $rightBound = time()-self::PERIOD_ADDITIONAL_FIRST;
        $rows = $select
            ->where('add_dt > ?', $leftBound)
            ->where('add_dt <= ?', $rightBound)
            ->query()
            ->fetchAll();
        foreach ($rows as $row) {
            $mUser->loadById($row['id']);
            $this->sendConfirmationEmail($mUser, array(), self::TYPE_ADDITIONAL);
        }

        //final wave
        $rightBound = time()-self::PERIOD_ADDITIONAL_DELETE - self::PERIOD_AROUND;
        $select->reset(Zend_Db_Select::WHERE);
        $rows = $select
            ->where('add_dt <= ?', $rightBound)
            ->query()
            ->fetchAll();

        foreach ($rows as $row) {
            $mUser->loadById($row['id']);
            $mUser->delete();
            $this->output->writeln('<info>User\'s [id='.$row['id'].'] mail "'.$row['mail'].'"</info> is deleted');
        }
    }

    /**
     * @param $user Model_User
     */
    private function sendConfirmationEmail(Model_User $mUser, $data = array(), $type = self::TYPE_PRIMARY)
    {
        $mEmails = new Model_user_Email();
        $mEmails->loadPrimaryEmailByUserId($mUser->getId());
        $tpl = 'primaryConfirmAgain.phtml';
        if ($type == self::TYPE_ADDITIONAL) {
            $tpl = 'additionalConfirmAgain.phtml';
        }

        // Variables fot specific email template
        $defaultData = array(
            'confirm_url'       => Zend_Registry::get('config')->site->url . '/confirmation/' . $mEmails->getConfirmationToken() ,
            'email_to_confirm'  => $mUser->getPrimaryEmail()->getEmail(),
            'service_title'     => AG_lib_Lang::getString('Default.service_title', $mUser->getLang()),
        );

        // About deferred email delivering:
        // Let's assume that the optimal mail time for messages of current cron job is 10:00 AM
        if (date('H', time()) < 10) {
            $localDeliveryTime = strtotime(date('Y-m-d') . ' 10:00');
        } else {
            $localDeliveryTime = strtotime(date('Y-m-d', strtotime("+1 day")) . ' 10:00');
        }
        $deliveryTime = AG_lib_Timezone::convertToUserTime($localDeliveryTime, null, $mUser->getTimezone(), "Y-m-d H:i");

        AG_lib_Notification::sendEmail(
            array(
                'tpl'               => 'email/'.$mUser->getLang().'/registration/' . $tpl,
                'destination'       => $mEmails->getEmail(),
                'from'              => array(
                    'email' => Zend_Registry::get('config')->email->noreply,
                    'name'  => Zend_Registry::get('config')->email->noreply_name
                ),
                'subject'           => AG_lib_Lang::getString('Registration.confirm_email'),
                'lang'              => $mUser->getLang(),
                'deliveryTime'          => $deliveryTime,
                'data'              => array_merge($defaultData, $data)
            )
        );
        $this->output->writeln('<info>Message for User [id='.$mUser->getId().']</info> was sent');
    }
}
