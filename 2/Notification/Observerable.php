<?php
/**
 * Наблюдатель для уведомлений
 */

class AG_lib_Notification_Observerable implements AG_lib_Observe_Observable
{
    private static $_instance = null;

    private function __construct()
    {

    }

    public static function getInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new AG_lib_Notification_Observerable();
        }
        return self::$_instance;
    }

    public static function notify(AG_lib_Observe_Event $event, $obj)
    {
        return AG_lib_Notification_EventNotifications::run($event, $obj);
    }
}
