<?php

trait AG_lib_Notification_Default_Meeting
{
    /**
     * @param $obj
     */
    public function setObj($obj)
    {
        if (!($obj instanceof Model_Meeting)) {
            throw new AG_lib_Notification_EventException('Obj is not an instance of Model_Meeting');
        }

        $this->obj = $obj;
    }

    /**
     * Возвращает инициатора встречи.
     * @return Model_User
     */
    public function getCreator()
    {
        $mCreator = new Model_User();
        $mCreator->loadById($this->obj->getCreatorId());
        return $mCreator;
    }

    /**
     * Получатели уведомления
     * @return mixed
     * @throws Exception
     */
    public function getRecipients()
    {
        if ( !($this->obj instanceof Model_Meeting)) {
            throw new Exception('Invalid obj in ' . AG_lib_Observe_EventType::getParams($this->observerEvent)['name']);
        }

        if (!$this->hasRecipients()) {
            $ids = $this->obj->getUsersActualStatusInfo($this->obj->getId());
            foreach ($ids as $User) {
                if ($this->obj->getCreatorId() == $User['user_id']) {
                    continue;
                }
                $mUser = new Model_User();
                $mUser->loadById($User['user_id']);
                $this->addRecipient($mUser);
            }
        }

        return $this->recipients;
    }
}
