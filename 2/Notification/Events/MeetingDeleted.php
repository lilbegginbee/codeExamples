<?php
/**
 *  Уведомления о удалении встречи
 */

class AG_lib_Notification_Events_MeetingDeleted extends AG_lib_Notification_EventDefault
{
    use AG_lib_Notification_Default_Meeting;

    /**
     * Отправляется всем затронутым пользователям
     * @param $Meeting Model_Meeting
     * @param bool $isBubble
     * @return bool
     */
    public function push($isBubble = false)
    {
        foreach ($this->getRecipients() as $mUser) {
            AG_lib_Notification::sendPush($mUser, array(
                'text' => AG_lib_Lang::getString(
                    'Event.event_' . $this->getObserverEvent()->getEventName() . '_full',
                    AG_lib_Lang::getLang(),
                    array(
                        'name' => $this->getCreator()->getFirstName(),
                        'surname' => $this->getCreator()->getLastName(),
                        'time' => $this->obj->getMeetingTime($mUser->getTimezone(), 'H:i'),
                        'date' => $this->obj->getMeetingTime($mUser->getTimezone(), AG_lib_Locale::getDateFormat($mUser->getLang())),
                    )
                )
            ));
        }
        return true;
    }

    /**
     * Отправляется всем затронутым пользователям, даже если они не в сервисе
     * @param $obj Model_Meeting
     * @return bool
     */
    public function email()
    {
        $mUserSettings = new Model_user_Settings();
        foreach ($this->getRecipients() as $mUser) {
            // проверяем настройки пользователя

            $mUserSettings->loadByUser($mUser);
            if (!$mUserSettings->getEmailNotifyCopy()) {
                continue;
            }

            AG_lib_Notification::sendEmail(
                array(
                    'tpl' => 'email/build/' . $mUser->getLang() . '/meetings/deleted.phtml',
                    'destination' => $mUser->getPrimaryEmail()->getEmail(),
                    'from'=> array(
                        'email' => Zend_Registry::get('config')->email->noreply,
                        'name'  => Zend_Registry::get('config')->email->noreply_name
                    ),
                    'subject' => AG_lib_Lang::getString(
                        'Event.event_' . $this->getObserverEvent()->getEventName(),
                        $mUser->getLang(),
                        array(
                            'title' => $this->obj->getTitle(),
                            'time'  => $this->obj->getMeetingTimeFriendly($mUser),
                        )
                    ),
                    'lang' => $mUser->getLang(),
                    'data' => array(
                        'title'     => $this->obj->getTitle(),
                        'time'      => $this->obj->getMeetingTimeFriendly($mUser),
                        'name'      => $this->getCreator()->getFirstName(),
                        'surname'   => $this->getCreator()->getLastName(),
                    )
                )
            );
        }

        return true;
    }

    /**
     * Отправляется всем затронутым пользователям
     * @param $Meeting
     * @param bool $isBubble
     * @return bool
     */
    public function feed($isBubble = false)
    {
        $res = array();

        foreach ($this->getRecipients() as $mUser) {
            $res[] = array(
                'type'              => $this->getObserverEvent()->getEventName(),
                'create_dt'         => $this->obj->getMeetingDt(),
                'target_meeting_id' => $this->obj->getId(),
                'user_id'           => $mUser->getId(),
                'is_bubble'         => $isBubble?1:0,
                'text'              => AG_lib_Lang::getString(
                    'Event.event_' . $this->getObserverEvent()->getEventName() . '_full',
                    $mUser->getLang(),
                    array(
                        'name' => $this->getCreator()->getFirstName(),
                        'surname' => $this->getCreator()->getLastName(),
                        'title' => $this->obj->getTitle(),
                        'time' =>  $this->obj->getMeetingTimeFriendly($mUser)
                    )
                )
            );
        }

        $Event = new Model_Event();
        foreach ($res as $insert) {
            $Event->insert($insert);
        }

        return true;
    }
}
