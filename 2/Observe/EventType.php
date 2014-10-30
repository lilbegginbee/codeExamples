<?php
/**
 * Все типы событий в приложении
 */

class AG_lib_Observe_EventType
{
    /**
     * События по встречам
     */
    const MEETING_START                 = 1;
    // ...
    const MEETING_DELETED               = 102; // +

    /**
     * Приглашения
     */
    // ...

    /**
     * Собственные события пользователя
     */
    const USER_PASSWORD_RECOVER_SEND    = 20;
    const USER_PASSWORD_RECOVERED       = 21;
    const USER_EMAIL_CONFIRMATION       = 22; // Инициирование подтверждения email
    const USER_EMAIL_CONFIRMED          = 23; // Пользователь подтвердил свой основной email
    // ...

    /**
     * События друзей
     */
    // ...

    /**
     * События по контактам
     */
    // ...


    private static $eventsMap = array(
        // ...
        self::MEETING_DELETED => array(
            'name' => 'MeetingDeleted',
            'notify' => array(
                AG_lib_Notification_EventNotifications::TYPE_PUSH,
                AG_lib_Notification_EventNotifications::TYPE_EMAIL,
                AG_lib_Notification_EventNotifications::TYPE_FEED_BUBBLE,
            )
        ),
        // ...
    );

    public static function isValid($eventName)
    {
        if (!isset(self::$eventsMap[$eventName])) {
            return false;
        }
        return true;
    }

    public static function getParams($eventName)
    {
        if (self::isValid($eventName)) {
            return self::$eventsMap[$eventName];
        }
        return null;
    }
}
