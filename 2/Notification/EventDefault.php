<?php
/**
 * Карта соответствий для уведомлений
 * User: timur
 * Date: 9/29/14
 * Time: 2:47 PM
 */

class AG_lib_Notification_EventDefault extends AG_lib_Notification_EventAbstract
{
    public function push()
    {
        return false;
    }

    public function email()
    {
        return false;
    }

    public function feed($isBubble = false)
    {
        return false;
    }

    public function alert()
    {

    }

    public function postToFB()
    {
        return false;
    }

    public function postToVK()
    {
        return false;
    }

    public function run(AG_lib_Observe_Event $event, $params, $obj, $recipients = null)
    {
        $this->setObj($obj);
        $this->setObserverEvent($event);
        if (!is_null($recipients)) {
            $this->setRecipients($recipients);
        }


        foreach ($params['notify'] as $type) {
            switch($type) {
                case AG_lib_Notification_EventNotifications::TYPE_PUSH:
                    $this->push();
                    break;
                case AG_lib_Notification_EventNotifications::TYPE_EMAIL:
                    $this->email();
                    break;
                case AG_lib_Notification_EventNotifications::TYPE_FEED:
                    $this->feed();
                    break;
                case AG_lib_Notification_EventNotifications::TYPE_FEED_BUBBLE:
                    $this->feed(true);
                    break;
                case AG_lib_Notification_EventNotifications::TYPE_ALERT:
                    $this->alert();
                    break;
            }
        }
    }

    /**
     * Получатели события
     * @return Model_User[]
     */
    public function getRecipients()
    {
        return $this->recipients;
    }
}
