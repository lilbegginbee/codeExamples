<?php

trait AG_lib_Notification_Default_MeetingInvitation
{
    protected $meeting = null;

    /**
     * @param $obj
     */
    public function setObj($obj)
    {
        if (!($obj instanceof Model_MeetingInvitation)) {
            throw new AG_lib_Notification_EventException('Obj is not an instance of Model_MeetingInvitation');
        }

        $this->obj = $obj;
    }

    /**
     * Получатель уведомления
     * @return Model_User
     */
    public function getRecipient()
    {
        if (!$this->recipients) {
            $mUser = new Model_User();
            $mUser->loadById($this->obj->getUserId());
            $this->recipients = $mUser;
        }
        return $this->recipients;
    }

    /**
     * @return Model_Meeting
     */
    public function getMeeting()
    {
        if (!$this->meeting) {
            $mMeeting = new Model_Meeting();
            $mMeeting->loadById($this->obj->getMeetingId());
            $this->meeeting = $mMeeting;
        }
        return $this->meeeting;
    }
}
