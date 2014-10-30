<?php
/**
 * Карта соответствий для уведомлений
 */

class AG_lib_Notification_EventNotifications
{

    const PREFIX_EVENT = 'AG_lib_Notification_Events_';

    const TYPE_PUSH = 0;
    const TYPE_EMAIL = 1;
    const TYPE_FEED = 2;
    const TYPE_FEED_BUBBLE = 3; // the same as TYPE_FEED, only additional property is_bubble

    /**
     * Создаёт и запускает обработчик события
     * @param AG_lib_Observe_Event $event
     * @param $obj объект (может быть в виде массива), в рамках которого произошло событие (чаще Model_Meeting)
     * @param null $recipients Опциональный массив пользователей Model_User, либо Id одного пользователя
     * @return bool
     */
    public static function run(AG_lib_Observe_Event $event, $obj, $recipients = null)
    {
        $params = AG_lib_Observe_EventType::getParams($event->getEventName());
        if (!$params) {
            return false;
        }

        $eventClass = self::PREFIX_EVENT . $params['name'];
        try {
            $Notify = new $eventClass();
            $Notify->run($event, $params, $obj, $recipients);
        } catch (Exception $e) {
            // programmers have forgotten to add notify handler for event or template,
            // or they didn't properly test their work.
            // @todo
        }
    }
}
