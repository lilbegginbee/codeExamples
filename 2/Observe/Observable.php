<?php
/**
 * Интерфейс наблюдателя.
 */

interface AG_lib_Observe_Observable
{
    static function notify(AG_lib_Observe_Event $event, $obj);
}
